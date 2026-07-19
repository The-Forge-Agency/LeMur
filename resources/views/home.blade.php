@extends('layouts.app')

@section('content')
    <header class="px-4 pt-5">
        <div class="mx-auto flex max-w-5xl items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="{{ asset('images/logo-horizontal.svg') }}" alt="LeMur" class="h-16 w-auto sm:h-20">
            </a>
            <a href="{{ config('lemur.coffee_url') }}" target="_blank" rel="noopener"
                class="rounded-xl border border-cork bg-paper px-3 py-2 text-sm font-medium text-ink-alt transition duration-200 ease-out hover:border-accent hover:text-accent">
                ☕ Soutenir
            </a>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="px-4 pt-10 sm:pt-16">
            <div class="mx-auto max-w-5xl">
                <div class="grid items-center gap-10 lg:grid-cols-2">
                    <div>
                        <h1 class="font-display text-4xl font-bold leading-tight sm:text-5xl">
                            Un lien. Un mur.<br>
                            <span class="text-accent">Tout le monde y colle ses notes.</span>
                        </h1>
                        <p class="mt-4 max-w-md text-lg text-ink-alt">
                            Les punchlines du groupe, la liste de la coloc, les adresses du voyage…
                            Crée un mur de post-it, envoie le lien, c'est tout.
                        </p>

                        <div id="creer" class="mt-8">
                            <livewire:create-wall />
                        </div>

                        <p class="mt-4 text-sm text-ink-alt">
                            100% gratuit · zéro compte · open source
                        </p>
                    </div>

                    {{-- Mur de démo --}}
                    <div class="rounded-[20px] bg-cork p-5 shadow-soft sm:p-7" aria-hidden="true">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="-rotate-2 rounded-sm bg-note-jaune p-4 shadow-soft">
                                <p class="font-hand text-2xl leading-snug">« Je suis pas en retard, je suis en décalé horaire »</p>
                                <p class="mt-2 text-xs text-ink-alt">Karim · il y a 2 h · 😂 12</p>
                            </div>
                            <div class="rotate-1 rounded-sm bg-note-bleu p-4 shadow-soft">
                                <p class="font-hand text-2xl leading-snug">Racheter du PQ !!! #Courses</p>
                                <p class="mt-2 text-xs text-ink-alt">Léa · il y a 5 h · 👍 3</p>
                            </div>
                            <div class="rotate-2 rounded-sm bg-note-vert p-4 shadow-soft">
                                <p class="font-hand text-2xl leading-snug">Trattoria da Enzo, la meilleure carbo de Rome #Voyage</p>
                                <p class="mt-2 text-xs text-ink-alt">Jules · hier · ❤️ 7</p>
                            </div>
                            <div class="-rotate-1 rounded-sm bg-note-rose p-4 shadow-soft">
                                <p class="font-hand text-2xl leading-snug">Joyeux anniv Sarah, reine de la coloc 👑</p>
                                <p class="mt-2 text-xs text-ink-alt">Anonyme · il y a 1 j · 🔥 9</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Comment ça marche --}}
        <section class="px-4 pt-20">
            <div class="mx-auto max-w-5xl">
                <h2 class="text-center font-display text-3xl font-bold">En 30 secondes, promis</h2>
                <div class="mt-10 grid gap-6 sm:grid-cols-3">
                    <div class="rounded-2xl bg-cork p-6 shadow-soft">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent font-display text-lg font-bold text-paper">1</span>
                        <h3 class="mt-4 font-display text-xl font-semibold">Nomme ton mur</h3>
                        <p class="mt-2 text-ink-alt">« Punchlines du squad », « Coloc rue Oberkampf »… Un nom, un clic, c'est créé.</p>
                    </div>
                    <div class="rounded-2xl bg-cork p-6 shadow-soft">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent font-display text-lg font-bold text-paper">2</span>
                        <h3 class="mt-4 font-display text-xl font-semibold">Partage le lien</h3>
                        <p class="mt-2 text-ink-alt">WhatsApp, iMessage, Discord ou QR code en soirée. Qui a le lien est dedans.</p>
                    </div>
                    <div class="rounded-2xl bg-cork p-6 shadow-soft">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent font-display text-lg font-bold text-paper">3</span>
                        <h3 class="mt-4 font-display text-xl font-semibold">Tout le monde colle</h3>
                        <p class="mt-2 text-ink-alt">Chacun écrit sa note, signe (ou pas), réagit en emoji. Sans compte, sans appli.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Exemples d'usage --}}
        <section class="px-4 pt-20">
            <div class="mx-auto max-w-5xl">
                <h2 class="text-center font-display text-3xl font-bold">Un mur pour chaque bande</h2>
                <p class="mx-auto mt-3 max-w-xl text-center text-ink-alt">
                    On ne va pas te faire un dessin. Enfin si, six.
                </p>
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-cork p-5 transition duration-200 ease-out hover:shadow-soft">
                        <span class="inline-block -rotate-2 rounded-sm bg-note-jaune px-3 py-1 font-hand text-xl">😂 #BestOf</span>
                        <h3 class="mt-3 font-display text-lg font-semibold">Les punchlines du groupe</h3>
                        <p class="mt-1 text-sm text-ink-alt">Les dingueries que sort ton pote méritent mieux qu'un scroll infini sur WhatsApp.</p>
                    </div>
                    <div class="rounded-2xl border border-cork p-5 transition duration-200 ease-out hover:shadow-soft">
                        <span class="inline-block rotate-1 rounded-sm bg-note-bleu px-3 py-1 font-hand text-xl">🧻 #Courses</span>
                        <h3 class="mt-3 font-display text-lg font-semibold">La vie de coloc</h3>
                        <p class="mt-1 text-sm text-ink-alt">Ce qu'il faut racheter, qui sort les poubelles, le mot doux du frigo.</p>
                    </div>
                    <div class="rounded-2xl border border-cork p-5 transition duration-200 ease-out hover:shadow-soft">
                        <span class="inline-block -rotate-1 rounded-sm bg-note-vert px-3 py-1 font-hand text-xl">🌍 #Voyage</span>
                        <h3 class="mt-3 font-display text-lg font-semibold">Le prochain trip</h3>
                        <p class="mt-1 text-sm text-ink-alt">Les bonnes adresses, les spots, le resto à ne pas rater. Tout au même endroit.</p>
                    </div>
                    <div class="rounded-2xl border border-cork p-5 transition duration-200 ease-out hover:shadow-soft">
                        <span class="inline-block rotate-2 rounded-sm bg-note-rose px-3 py-1 font-hand text-xl">🎉 #Anniv</span>
                        <h3 class="mt-3 font-display text-lg font-semibold">Les events</h3>
                        <p class="mt-1 text-sm text-ink-alt">Anniv, mariage, EVJF : affiche le QR code, les invités laissent leurs messages.</p>
                    </div>
                    <div class="rounded-2xl border border-cork p-5 transition duration-200 ease-out hover:shadow-soft">
                        <span class="inline-block -rotate-2 rounded-sm bg-note-violet px-3 py-1 font-hand text-xl">💡 #Idées</span>
                        <h3 class="mt-3 font-display text-lg font-semibold">Le brainstorm</h3>
                        <p class="mt-1 text-sm text-ink-alt">Les idées du projet, les références, les « et si on faisait ça ? » de l'équipe.</p>
                    </div>
                    <div class="rounded-2xl border border-cork p-5 transition duration-200 ease-out hover:shadow-soft">
                        <span class="inline-block rotate-1 rounded-sm bg-note-orange px-3 py-1 font-hand text-xl">📌 #ToiMême</span>
                        <h3 class="mt-3 font-display text-lg font-semibold">Ton usage à toi</h3>
                        <p class="mt-1 text-sm text-ink-alt">Un mur, c'est un mur. Colles-y ce que tu veux, on ne juge pas.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Confiance --}}
        <section class="px-4 pt-20">
            <div class="mx-auto max-w-5xl rounded-[20px] bg-cork p-8 shadow-soft sm:p-10">
                <div class="grid gap-8 sm:grid-cols-3">
                    <div>
                        <p class="text-2xl">🕵️</p>
                        <h3 class="mt-2 font-display text-lg font-semibold">Zéro compte, zéro email</h3>
                        <p class="mt-1 text-sm text-ink-alt">Aucune inscription, aucune donnée perso. Le lien suffit, un PIN peut verrouiller l'écriture.</p>
                    </div>
                    <div>
                        <p class="text-2xl">🔓</p>
                        <h3 class="mt-2 font-display text-lg font-semibold">Gratuit et open source</h3>
                        <p class="mt-1 text-sm text-ink-alt">Pas de plan payant, jamais. Le code est public, tu peux même l'héberger toi-même.</p>
                    </div>
                    <div>
                        <p class="text-2xl">☕</p>
                        <h3 class="mt-2 font-display text-lg font-semibold">Financé par les cafés</h3>
                        <p class="mt-1 text-sm text-ink-alt">
                            Si LeMur te rend service,
                            <a href="{{ config('lemur.coffee_url') }}" target="_blank" rel="noopener" class="font-medium text-accent underline-offset-2 hover:underline">offre-nous un café</a>.
                            C'est tout ce qu'on demande.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA final --}}
        <section class="px-4 pt-20 text-center">
            <h2 class="font-display text-3xl font-bold">Ton groupe mérite son mur</h2>
            <a href="#creer"
                class="mt-6 inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 font-semibold text-paper transition duration-200 ease-out hover:opacity-90">
                Créer mon mur · c'est gratuit
            </a>
        </section>
    </main>
@endsection
