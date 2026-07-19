<?php

use App\Models\Wall;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public function create(): mixed
    {
        $this->validate(
            ['name' => ['required', 'string', 'min:2', 'max:80']],
            [
                'name.required' => 'Donne un petit nom à ton mur 🙂',
                'name.min' => 'Deux lettres minimum, tu peux le faire !',
                'name.max' => '80 caractères max, garde la punchline pour une note 😄',
            ],
        );

        $wall = Wall::create(['name' => trim($this->name)]);

        session()->put("wall_admin_{$wall->id}", true);

        return $this->redirect(route('walls.manage', [
            'wall' => $wall,
            'k' => $wall->admin_token,
            'bienvenue' => 1,
        ]));
    }
};
?>

<form wire:submit="create" class="w-full max-w-md">
    <div class="flex flex-col gap-3 sm:flex-row">
        <label for="wall-name" class="sr-only">Nom de ton mur</label>
        <input
            id="wall-name"
            type="text"
            wire:model="name"
            placeholder="Le nom de ton mur, ex : Punchlines du squad"
            maxlength="80"
            class="w-full rounded-lg border border-cork bg-paper px-4 py-3 text-ink placeholder-ink-alt/70 shadow-soft transition duration-200 ease-out focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30"
        >
        <button
            type="submit"
            class="shrink-0 rounded-xl bg-accent px-6 py-3 font-semibold text-paper transition duration-200 ease-out hover:opacity-90 disabled:opacity-60"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove>Créer mon mur</span>
            <span wire:loading>Création…</span>
        </button>
    </div>
    @error('name')
        <p class="mt-2 text-sm font-medium text-accent">{{ $message }}</p>
    @enderror
</form>
