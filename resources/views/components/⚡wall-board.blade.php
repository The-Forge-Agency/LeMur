<?php

use App\Models\Note;
use App\Models\Wall;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Wall $wall;

    public string $content = '';

    public string $author = '';

    public string $color = 'jaune';

    public string $search = '';

    public string $activeTag = '';

    public ?int $editingId = null;

    public string $editingContent = '';

    public string $pin = '';

    #[Computed]
    public function isAdmin(): bool
    {
        return (bool) session()->get("wall_admin_{$this->wall->id}", false);
    }

    #[Computed]
    public function authorToken(): string
    {
        return (string) request()->cookie('lemur_token', '');
    }

    #[Computed]
    public function isUnlocked(): bool
    {
        return ! $this->wall->hasPin()
            || $this->isAdmin
            || (bool) session()->get("wall_unlocked_{$this->wall->id}", false);
    }

    #[Computed]
    public function canWrite(): bool
    {
        return ($this->isAdmin || ! $this->wall->read_only) && $this->isUnlocked;
    }

    #[Computed]
    public function hasFilters(): bool
    {
        return trim($this->search) !== '' || $this->activeTag !== '';
    }

    /**
     * @return Collection<int, Note>
     */
    #[Computed]
    public function notes(): Collection
    {
        $search = trim($this->search);

        return $this->wall->notes()
            ->when($this->activeTag !== '', fn ($query) => $query->whereJsonContains('tags', $this->activeTag))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('content', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('pinned')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function allTags(): array
    {
        return $this->wall->notes()
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function unlock(): void
    {
        if (! $this->wall->hasPin() || ! Hash::check($this->pin, (string) $this->wall->pin_hash)) {
            $this->addError('pin', 'Mauvais code, essaie encore 🙈');

            return;
        }

        session()->put("wall_unlocked_{$this->wall->id}", true);
        $this->reset('pin');
    }

    public function addNote(): void
    {
        abort_unless($this->canWrite, 403);

        $this->validate(
            [
                'content' => ['required', 'string', 'max:500'],
                'author' => ['nullable', 'string', 'max:40'],
                'color' => ['required', 'in:'.implode(',', Note::COLORS)],
            ],
            [
                'content.required' => 'Une note vide, ça ne colle pas 😄',
                'content.max' => '500 caractères max — un post-it, pas un roman !',
                'author.max' => '40 caractères max pour le pseudo.',
            ],
        );

        $this->wall->notes()->create([
            'content' => trim($this->content),
            'author' => trim($this->author) !== '' ? trim($this->author) : null,
            'author_token' => $this->authorToken !== '' ? $this->authorToken : Str::random(40),
            'color' => $this->color,
            'tags' => Note::extractTags($this->content),
            'reactions' => [],
        ]);

        $this->reset('content');
        $this->refreshBoard();
    }

    public function startEdit(int $noteId): void
    {
        $note = $this->authorizeNote($noteId);

        $this->editingId = $note->id;
        $this->editingContent = $note->content;
        $this->resetErrorBag('editingContent');
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editingContent');
    }

    public function updateNote(): void
    {
        if ($this->editingId === null) {
            return;
        }

        $note = $this->authorizeNote($this->editingId);

        $this->validate(
            ['editingContent' => ['required', 'string', 'max:500']],
            [
                'editingContent.required' => 'Une note vide, ça ne colle pas 😄',
                'editingContent.max' => '500 caractères max — un post-it, pas un roman !',
            ],
        );

        $note->update([
            'content' => trim($this->editingContent),
            'tags' => Note::extractTags($this->editingContent),
        ]);

        $this->cancelEdit();
        $this->refreshBoard();
    }

    public function deleteNote(int $noteId): void
    {
        $this->authorizeNote($noteId)->delete();

        if ($this->editingId === $noteId) {
            $this->cancelEdit();
        }

        $this->refreshBoard();
    }

    public function togglePinned(int $noteId): void
    {
        $note = $this->authorizeNote($noteId);

        $note->update(['pinned' => ! $note->pinned]);
        $this->refreshBoard();
    }

    public function react(int $noteId, string $emoji, bool $adding): void
    {
        abort_unless(in_array($emoji, Note::REACTION_EMOJIS, true), 422);

        /** @var Note $note */
        $note = $this->wall->notes()->findOrFail($noteId);

        $reactions = $note->reactions ?? [];
        $count = max(0, ($reactions[$emoji] ?? 0) + ($adding ? 1 : -1));

        if ($count === 0) {
            unset($reactions[$emoji]);
        } else {
            $reactions[$emoji] = $count;
        }

        $note->update(['reactions' => $reactions]);
        $this->refreshBoard();
    }

    public function filterByTag(string $tag): void
    {
        $this->activeTag = $this->activeTag === $tag ? '' : $tag;
        $this->refreshBoard();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'activeTag');
        $this->refreshBoard();
    }

    private function authorizeNote(int $noteId): Note
    {
        /** @var Note $note */
        $note = $this->wall->notes()->findOrFail($noteId);

        abort_unless($this->isAdmin || $note->isOwnedBy($this->authorToken), 403);

        return $note;
    }

    private function refreshBoard(): void
    {
        unset($this->notes, $this->allTags);
    }
};
?>

@php
    $noteColorClasses = [
        'jaune' => 'bg-note-jaune',
        'rose' => 'bg-note-rose',
        'bleu' => 'bg-note-bleu',
        'vert' => 'bg-note-vert',
        'violet' => 'bg-note-violet',
        'orange' => 'bg-note-orange',
    ];
    $rotations = ['rotate-1', '-rotate-1', 'rotate-2', '-rotate-2'];
@endphp

<div
    class="mx-auto max-w-6xl px-4 pt-5"
    x-data="{ shareOpen: false }"
    x-init="$wire.color = LeMur.color(); if (! $wire.author) { $wire.author = LeMur.pseudo() }"
>
    {{-- En-tête du mur --}}
    <header class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2" title="Créer mon propre mur">
            <img src="{{ asset('images/logo-icon.svg') }}" alt="LeMur" class="h-9 w-9">
            <span class="font-display text-lg font-bold text-accent">LeMur</span>
        </a>
        <div class="flex items-center gap-2">
            @if ($this->isAdmin)
                <a href="{{ $wall->adminUrl() }}"
                    class="rounded-xl border border-cork bg-paper px-3 py-2 text-sm font-medium text-ink-alt transition duration-200 ease-out hover:border-accent hover:text-accent">
                    ⚙️ Gérer
                </a>
            @endif
            <button
                type="button"
                @click="shareOpen = true; $nextTick(() => LeMur.qr($refs.qrcode, @js($wall->shareUrl())))"
                class="rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-paper transition duration-200 ease-out hover:opacity-90">
                Partager 🔗
            </button>
        </div>
    </header>

    <div class="mt-6">
        <h1 class="font-display text-3xl font-bold sm:text-4xl">{{ $wall->name }}</h1>
        <p class="mt-1 text-sm text-ink-alt">
            {{ $this->notes->count() }} {{ Str::plural('note', $this->notes->count()) }}
            @if ($wall->read_only) · 👀 lecture seule @endif
            @if ($wall->hasPin()) · 🔒 protégé par PIN @endif
            @if ($this->isAdmin) · <span class="font-medium text-accent">tu es admin</span> @endif
        </p>
    </div>

    {{-- Zone d'écriture --}}
    <section class="mt-6">
        @if (! $this->canWrite && $wall->read_only && ! $this->isAdmin)
            <div class="rounded-2xl bg-cork p-4 text-sm text-ink-alt shadow-soft">
                👀 Ce mur est en lecture seule : tu peux tout regarder, mais seul son créateur peut le modifier.
            </div>
        @elseif (! $this->isUnlocked)
            <form wire:submit="unlock" class="rounded-2xl bg-cork p-5 shadow-soft">
                <p class="font-display font-semibold">🔒 Ce mur est verrouillé par un code PIN</p>
                <p class="mt-1 text-sm text-ink-alt">Demande le code au créateur du mur pour pouvoir coller tes notes.</p>
                <div class="mt-3 flex gap-2">
                    <label for="pin" class="sr-only">Code PIN</label>
                    <input
                        id="pin"
                        type="password"
                        inputmode="numeric"
                        wire:model="pin"
                        placeholder="Code PIN"
                        class="w-40 rounded-lg border border-cork bg-paper px-4 py-2 shadow-soft transition duration-200 ease-out focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30"
                    >
                    <button type="submit" class="rounded-xl bg-accent px-4 py-2 font-semibold text-paper transition duration-200 ease-out hover:opacity-90">
                        Déverrouiller
                    </button>
                </div>
                @error('pin')
                    <p class="mt-2 text-sm font-medium text-accent">{{ $message }}</p>
                @enderror
            </form>
        @else
            <form wire:submit="addNote" class="rounded-2xl bg-cork p-4 shadow-soft sm:p-5">
                <label for="note-content" class="sr-only">Ta note</label>
                <textarea
                    id="note-content"
                    wire:model="content"
                    rows="3"
                    maxlength="500"
                    placeholder="Colle ta note ici… (les #hashtags sont bienvenus)"
                    class="w-full resize-none rounded-lg border border-transparent bg-paper px-4 py-3 font-hand text-2xl leading-snug shadow-soft transition duration-200 ease-out placeholder:font-sans placeholder:text-base placeholder:text-ink-alt/70 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30"
                ></textarea>
                @error('content')
                    <p class="mt-1 text-sm font-medium text-accent">{{ $message }}</p>
                @enderror

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <label for="note-author" class="sr-only">Ton pseudo (optionnel)</label>
                    <input
                        id="note-author"
                        type="text"
                        wire:model="author"
                        maxlength="40"
                        @change="LeMur.savePseudo($event.target.value)"
                        placeholder="Ton pseudo (optionnel)"
                        class="w-44 rounded-lg border border-transparent bg-paper px-3 py-2 text-sm shadow-soft transition duration-200 ease-out focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30"
                    >

                    <div class="flex items-center gap-1.5" role="radiogroup" aria-label="Couleur du post-it">
                        @foreach ($noteColorClasses as $colorName => $colorClass)
                            <button
                                type="button"
                                @click="$wire.color = @js($colorName); LeMur.saveColor(@js($colorName))"
                                :class="$wire.color === @js($colorName) ? 'ring-2 ring-accent ring-offset-2 ring-offset-cork' : ''"
                                class="{{ $colorClass }} h-7 w-7 rounded-full shadow-soft transition duration-200 ease-out hover:scale-110"
                                title="Post-it {{ $colorName }}"
                                aria-label="Post-it {{ $colorName }}"
                            ></button>
                        @endforeach
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="ml-auto rounded-xl bg-accent px-5 py-2.5 font-semibold text-paper transition duration-200 ease-out hover:opacity-90 disabled:opacity-60">
                        <span wire:loading.remove wire:target="addNote">Coller 📌</span>
                        <span wire:loading wire:target="addNote">On colle…</span>
                    </button>
                </div>
                @error('author')
                    <p class="mt-1 text-sm font-medium text-accent">{{ $message }}</p>
                @enderror
            </form>
        @endif
    </section>

    {{-- Recherche & filtres --}}
    @if ($this->notes->isNotEmpty() || $this->hasFilters)
        <section class="mt-6 flex flex-wrap items-center gap-2">
            <label for="search" class="sr-only">Rechercher dans le mur</label>
            <input
                id="search"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="🔍 Rechercher…"
                class="w-full rounded-lg border border-cork bg-paper px-4 py-2 text-sm shadow-soft transition duration-200 ease-out focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 sm:w-56"
            >
            @foreach ($this->allTags as $tag)
                <button
                    type="button"
                    wire:key="tag-{{ $tag }}"
                    wire:click="filterByTag(@js($tag))"
                    @class([
                        'rounded-full px-3 py-1.5 text-sm font-medium transition duration-200 ease-out',
                        'bg-accent text-paper' => $activeTag === $tag,
                        'border border-cork bg-paper text-ink-alt hover:border-accent hover:text-accent' => $activeTag !== $tag,
                    ])>
                    #{{ $tag }}
                </button>
            @endforeach
            @if ($this->hasFilters)
                <button type="button" wire:click="clearFilters" class="text-sm font-medium text-accent underline-offset-2 hover:underline">
                    Tout effacer
                </button>
            @endif
        </section>
    @endif

    {{-- Le mur --}}
    <section wire:poll.visible.15s class="mt-6 rounded-[20px] bg-cork p-4 shadow-soft sm:p-6">
        @if ($this->notes->isEmpty())
            <div class="py-16 text-center">
                @if ($this->hasFilters)
                    <p class="font-hand text-3xl">Rien ne colle avec ta recherche 🤷</p>
                    <button type="button" wire:click="clearFilters" class="mt-3 font-medium text-accent underline-offset-2 hover:underline">
                        Effacer les filtres
                    </button>
                @else
                    <p class="font-hand text-3xl">Ce mur est tout nu 🙈</p>
                    <p class="mt-2 text-ink-alt">
                        @if ($this->canWrite)
                            Colle la première note, montre l'exemple !
                        @else
                            Le créateur du mur n'a encore rien collé.
                        @endif
                    </p>
                @endif
            </div>
        @else
            <div class="columns-2 gap-4 sm:columns-3 lg:columns-4 [&>*]:mb-4 [&>*]:break-inside-avoid">
                @foreach ($this->notes as $note)
                    @php $canManageNote = $this->isAdmin || $note->isOwnedBy($this->authorToken); @endphp
                    <article
                        wire:key="note-{{ $note->id }}"
                        class="{{ $noteColorClasses[$note->color] ?? 'bg-note-jaune' }} {{ $note->pinned ? '' : $rotations[$note->id % 4] }} relative rounded-sm p-4 shadow-soft transition duration-200 ease-out hover:rotate-0"
                    >
                        @if ($note->pinned)
                            <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 text-xl" title="Note épinglée">📌</span>
                        @endif

                        @if ($editingId === $note->id)
                            <form wire:submit="updateNote">
                                <label for="edit-{{ $note->id }}" class="sr-only">Modifier la note</label>
                                <textarea
                                    id="edit-{{ $note->id }}"
                                    wire:model="editingContent"
                                    rows="4"
                                    maxlength="500"
                                    class="w-full resize-none rounded-lg border border-ink/10 bg-paper/70 px-3 py-2 font-hand text-xl leading-snug focus:border-accent focus:outline-none"
                                ></textarea>
                                @error('editingContent')
                                    <p class="mt-1 text-xs font-medium text-accent">{{ $message }}</p>
                                @enderror
                                <div class="mt-2 flex gap-2">
                                    <button type="submit" class="rounded-lg bg-accent px-3 py-1.5 text-xs font-semibold text-paper">Enregistrer</button>
                                    <button type="button" wire:click="cancelEdit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-ink-alt hover:text-ink">Annuler</button>
                                </div>
                            </form>
                        @else
                            <p class="whitespace-pre-wrap break-words font-hand text-2xl leading-snug">{{ $note->content }}</p>

                            @if ($note->tags !== [])
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach ($note->tags as $tag)
                                        <button
                                            type="button"
                                            wire:key="note-{{ $note->id }}-tag-{{ $tag }}"
                                            wire:click="filterByTag(@js($tag))"
                                            class="rounded-full bg-paper/60 px-2 py-0.5 text-xs font-medium text-ink-alt transition duration-200 ease-out hover:text-accent">
                                            #{{ $tag }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <p class="mt-3 text-xs text-ink-alt">
                                {{ $note->author ?? 'Anonyme' }} · {{ $note->created_at->diffForHumans(short: true) }}
                            </p>

                            <div class="mt-2 flex flex-wrap items-center gap-1">
                                @foreach (\App\Models\Note::REACTION_EMOJIS as $emoji)
                                    @php $count = $note->reactions[$emoji] ?? 0; @endphp
                                    <button
                                        type="button"
                                        wire:key="note-{{ $note->id }}-react-{{ $loop->index }}"
                                        x-data="{ reacted: LeMur.hasReacted({{ $note->id }}, @js($emoji)) }"
                                        @click="reacted = ! reacted; LeMur.rememberReaction({{ $note->id }}, @js($emoji), reacted); $wire.react({{ $note->id }}, @js($emoji), reacted)"
                                        :class="reacted ? 'bg-paper ring-1 ring-accent' : 'bg-paper/50 hover:bg-paper'"
                                        class="rounded-full px-2 py-0.5 text-sm transition duration-200 ease-out"
                                    >{{ $emoji }}@if ($count > 0)<span class="ml-1 text-xs font-semibold">{{ $count }}</span>@endif</button>
                                @endforeach

                                @if ($canManageNote)
                                    <span class="ml-auto flex items-center gap-0.5">
                                        <button type="button" wire:click="togglePinned({{ $note->id }})"
                                            class="rounded-full p-1 text-sm opacity-60 transition duration-200 ease-out hover:opacity-100"
                                            title="{{ $note->pinned ? 'Désépingler' : 'Épingler en haut' }}">📌</button>
                                        <button type="button" wire:click="startEdit({{ $note->id }})"
                                            class="rounded-full p-1 text-sm opacity-60 transition duration-200 ease-out hover:opacity-100"
                                            title="Modifier">✏️</button>
                                        <button type="button" wire:click="deleteNote({{ $note->id }})"
                                            wire:confirm="Supprimer cette note ?"
                                            class="rounded-full p-1 text-sm opacity-60 transition duration-200 ease-out hover:opacity-100"
                                            title="Supprimer">🗑️</button>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Panneau de partage --}}
    <div
        x-show="shareOpen"
        x-cloak
        @keydown.escape.window="shareOpen = false"
        class="fixed inset-0 z-50 flex items-end justify-center bg-ink/40 p-4 sm:items-center"
        @click.self="shareOpen = false"
    >
        <div class="w-full max-w-sm rounded-[20px] bg-paper p-6 shadow-soft">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-bold">Partager ce mur</h2>
                <button type="button" @click="shareOpen = false" class="rounded-full p-1 text-ink-alt hover:text-ink" aria-label="Fermer">✕</button>
            </div>
            <p class="mt-1 text-sm text-ink-alt">Envoie ce lien à ta bande : qui l'a, y colle.</p>

            <div class="mt-4 flex items-center gap-2" x-data="{ copied: false }">
                <input type="text" readonly value="{{ $wall->shareUrl() }}"
                    class="w-full truncate rounded-lg border border-cork bg-cork/50 px-3 py-2 text-sm text-ink-alt">
                <button
                    type="button"
                    @click="LeMur.copy(@js($wall->shareUrl())).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                    class="shrink-0 rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-paper transition duration-200 ease-out hover:opacity-90">
                    <span x-show="! copied">Copier</span>
                    <span x-show="copied" x-cloak>Copié ✔</span>
                </button>
            </div>

            <div class="mx-auto mt-5 w-48 rounded-2xl bg-cork p-3">
                <div x-ref="qrcode" wire:ignore class="overflow-hidden rounded-lg [&>svg]:h-auto [&>svg]:w-full"></div>
            </div>
            <p class="mt-2 text-center text-xs text-ink-alt">Ou fais scanner ce QR code en soirée 🎉</p>
        </div>
    </div>
</div>
