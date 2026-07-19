@extends('layouts.app')

@section('title', 'Gérer « ' . $wall->name . ' » · LeMur')
@section('description', 'Espace admin de ton mur LeMur.')
@section('no-index', '1')

@section('content')
    <div class="mx-auto max-w-2xl px-4 pt-5">
        <header class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/logo-icon.svg') }}" alt="LeMur" class="h-9 w-9">
                <span class="font-display text-lg font-bold text-accent">LeMur</span>
            </a>
            <a href="{{ $wall->shareUrl() }}"
                class="rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-paper transition duration-200 ease-out hover:opacity-90">
                Voir mon mur →
            </a>
        </header>

        @if ($justCreated)
            <div class="mt-6 rounded-2xl bg-note-vert p-5 shadow-soft">
                <p class="font-display text-xl font-bold">Ton mur est prêt 🎉</p>
                <p class="mt-1 text-sm">
                    Envoie le lien de partage à ta bande, et garde précieusement cette page :
                    c'est ton espace admin (pas de compte = pas de récupération possible).
                </p>
            </div>
        @endif

        <h1 class="mt-6 font-display text-3xl font-bold">{{ $wall->name }}</h1>
        <p class="mt-1 text-sm text-ink-alt">Espace de gestion · toi seul as ce lien</p>

        {{-- Lien de partage --}}
        <section class="mt-6 rounded-2xl bg-cork p-5 shadow-soft" x-data="{ copied: false }">
            <h2 class="font-display text-lg font-semibold">🔗 Le lien à partager</h2>
            <p class="mt-1 text-sm text-ink-alt">Qui a ce lien voit le mur et peut y coller ses notes.</p>
            <div class="mt-3 flex items-center gap-2">
                <input type="text" readonly value="{{ $wall->shareUrl() }}"
                    class="w-full truncate rounded-lg border border-cork bg-paper px-3 py-2 text-sm text-ink-alt">
                <button
                    type="button"
                    @click="LeMur.copy(@js($wall->shareUrl())).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                    class="shrink-0 rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-paper transition duration-200 ease-out hover:opacity-90">
                    <span x-show="! copied">Copier</span>
                    <span x-show="copied" x-cloak>Copié ✔</span>
                </button>
            </div>
            <div class="mx-auto mt-5 w-44 rounded-2xl bg-paper p-3">
                <div x-init="LeMur.qr($el, @js($wall->shareUrl()))" class="overflow-hidden rounded-lg [&>svg]:h-auto [&>svg]:w-full"></div>
            </div>
            <p class="mt-2 text-center text-xs text-ink-alt">Le QR code du mur — parfait en soirée ou affiché à un event.</p>
        </section>

        {{-- Lien admin --}}
        <section class="mt-4 rounded-2xl border-2 border-dashed border-accent/40 bg-paper p-5" x-data="{ copied: false }">
            <h2 class="font-display text-lg font-semibold">🗝️ Ton lien admin (secret)</h2>
            <p class="mt-1 text-sm text-ink-alt">
                Ce lien te redonne accès à cette page et aux super-pouvoirs de modération.
                Garde-le pour toi : mets-le en favori ou colle-le dans tes notes perso.
            </p>
            <div class="mt-3 flex items-center gap-2">
                <input type="text" readonly value="{{ $wall->adminUrl() }}"
                    class="w-full truncate rounded-lg border border-cork bg-cork/50 px-3 py-2 text-sm text-ink-alt">
                <button
                    type="button"
                    @click="LeMur.copy(@js($wall->adminUrl())).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                    class="shrink-0 rounded-xl border border-cork bg-paper px-4 py-2 text-sm font-semibold text-ink transition duration-200 ease-out hover:border-accent hover:text-accent">
                    <span x-show="! copied">Copier</span>
                    <span x-show="copied" x-cloak>Copié ✔</span>
                </button>
            </div>
        </section>

        {{-- Réglages --}}
        <livewire:wall-settings :wall="$wall" />

        <p class="mt-6 text-sm text-ink-alt">
            💡 En tant qu'admin, tu peux aussi épingler, modifier ou supprimer n'importe quelle note,
            directement <a href="{{ $wall->shareUrl() }}" class="font-medium text-accent underline-offset-2 hover:underline">sur ton mur</a>.
        </p>
    </div>
@endsection
