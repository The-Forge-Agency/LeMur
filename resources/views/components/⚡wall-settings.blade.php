<?php

use App\Models\Wall;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Wall $wall;

    public string $name = '';

    public bool $readOnly = false;

    public string $newPin = '';

    public string $status = '';

    public function mount(): void
    {
        $this->name = $this->wall->name;
        $this->readOnly = $this->wall->read_only;
    }

    public function updateName(): void
    {
        $this->authorizeAdmin();

        $this->validate(
            ['name' => ['required', 'string', 'min:2', 'max:80']],
            [
                'name.required' => 'Ton mur mérite un nom 🙂',
                'name.min' => 'Deux lettres minimum.',
                'name.max' => '80 caractères max.',
            ],
        );

        $this->wall->update(['name' => trim($this->name)]);
        $this->status = 'Nom du mur mis à jour ✔';
    }

    public function updatedReadOnly(): void
    {
        $this->authorizeAdmin();

        $this->wall->update(['read_only' => $this->readOnly]);
        $this->status = $this->readOnly
            ? 'Mur passé en lecture seule 👀'
            : 'Tout le monde peut de nouveau écrire ✍️';
    }

    public function setPin(): void
    {
        $this->authorizeAdmin();

        $this->validate(
            ['newPin' => ['required', 'digits_between:4,8']],
            ['newPin.digits_between' => 'Le PIN doit faire entre 4 et 8 chiffres.', 'newPin.required' => 'Choisis un code PIN.'],
        );

        $this->wall->update(['pin_hash' => Hash::make($this->newPin)]);
        $this->reset('newPin');
        $this->status = 'Code PIN activé 🔒 Seuls ceux qui l\'ont peuvent écrire.';
    }

    public function removePin(): void
    {
        $this->authorizeAdmin();

        $this->wall->update(['pin_hash' => null]);
        $this->status = 'Code PIN retiré : le lien suffit pour écrire.';
    }

    private function authorizeAdmin(): void
    {
        abort_unless((bool) session()->get("wall_admin_{$this->wall->id}", false), 403);
    }
};
?>

<section class="mt-4 rounded-2xl bg-cork p-5 shadow-soft">
    <h2 class="font-display text-lg font-semibold">⚙️ Réglages du mur</h2>

    @if ($status !== '')
        <p class="mt-2 rounded-lg bg-note-vert/60 px-3 py-2 text-sm font-medium">{{ $status }}</p>
    @endif

    {{-- Nom --}}
    <form wire:submit="updateName" class="mt-4">
        <label for="wall-rename" class="text-sm font-medium">Nom du mur</label>
        <div class="mt-1.5 flex gap-2">
            <input
                id="wall-rename"
                type="text"
                wire:model="name"
                maxlength="80"
                class="w-full rounded-lg border border-cork bg-paper px-3 py-2 text-sm shadow-soft transition duration-200 ease-out focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30"
            >
            <button type="submit"
                class="shrink-0 rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-paper transition duration-200 ease-out hover:opacity-90">
                Renommer
            </button>
        </div>
        @error('name')
            <p class="mt-1 text-sm font-medium text-accent">{{ $message }}</p>
        @enderror
    </form>

    {{-- Lecture seule --}}
    <div class="mt-5 flex items-start gap-3">
        <input
            id="read-only"
            type="checkbox"
            wire:model.live="readOnly"
            class="mt-0.5 h-5 w-5 rounded border-cork accent-accent"
        >
        <label for="read-only" class="text-sm">
            <span class="font-medium">Mode lecture seule</span>
            <span class="block text-ink-alt">Le mur reste visible par le lien, mais toi seul peux le modifier. Pratique pour partager un best-of figé.</span>
        </label>
    </div>

    {{-- PIN --}}
    <div class="mt-5 border-t border-paper pt-4">
        <p class="text-sm font-medium">
            Code PIN d'écriture
            @if ($wall->hasPin())
                <span class="ml-1 rounded-full bg-note-jaune px-2 py-0.5 text-xs font-semibold">activé 🔒</span>
            @endif
        </p>
        <p class="mt-0.5 text-sm text-ink-alt">
            Avec un PIN, tout le monde peut lire, mais il faut le code pour écrire.
        </p>
        <form wire:submit="setPin" class="mt-2 flex gap-2">
            <label for="new-pin" class="sr-only">Nouveau code PIN</label>
            <input
                id="new-pin"
                type="text"
                inputmode="numeric"
                wire:model="newPin"
                maxlength="8"
                placeholder="{{ $wall->hasPin() ? 'Nouveau PIN (4-8 chiffres)' : 'PIN (4-8 chiffres)' }}"
                class="w-full rounded-lg border border-cork bg-paper px-3 py-2 text-sm shadow-soft transition duration-200 ease-out focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30 sm:w-56"
            >
            <button type="submit"
                class="shrink-0 rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-paper transition duration-200 ease-out hover:opacity-90">
                {{ $wall->hasPin() ? 'Changer' : 'Activer' }}
            </button>
            @if ($wall->hasPin())
                <button type="button" wire:click="removePin"
                    wire:confirm="Retirer le code PIN ? Tout le monde avec le lien pourra de nouveau écrire."
                    class="shrink-0 rounded-xl border border-cork bg-paper px-4 py-2 text-sm font-medium text-ink-alt transition duration-200 ease-out hover:border-accent hover:text-accent">
                    Retirer
                </button>
            @endif
        </form>
        @error('newPin')
            <p class="mt-1 text-sm font-medium text-accent">{{ $message }}</p>
        @enderror
    </div>
</section>
