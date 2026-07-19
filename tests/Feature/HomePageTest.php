<?php

it('displays the landing page with the create wall form', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Tout le monde y colle ses notes.')
        ->assertSeeLivewire('create-wall')
        ->assertSee('buymeacoffee.com/tfa.the.forge.agency', escape: false);
});
