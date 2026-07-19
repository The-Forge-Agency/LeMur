<?php

use App\Models\Note;
use App\Models\Wall;

it('displays a wall with its notes', function () {
    $wall = Wall::factory()->create(['name' => 'Coloc rue Oberkampf']);
    Note::factory()->for($wall)->create(['content' => 'Racheter du PQ #Courses', 'author' => 'Léa']);

    $this->get($wall->shareUrl())
        ->assertOk()
        ->assertSee('Coloc rue Oberkampf')
        ->assertSee('Racheter du PQ #Courses')
        ->assertSee('Léa');
});

it('returns 404 for an unknown wall', function () {
    $this->get('/m/unmurquinexistepas123456789')->assertNotFound();
});

it('shows an empty state on a wall without notes', function () {
    $wall = Wall::factory()->create();

    $this->get($wall->shareUrl())
        ->assertOk()
        ->assertSee('Ce mur est tout nu');
});

it('grants admin access with the right token', function () {
    $wall = Wall::factory()->create();

    $this->get($wall->adminUrl())
        ->assertOk()
        ->assertSee('Réglages du mur')
        ->assertSessionHas("wall_admin_{$wall->id}", true);
});

it('rejects the admin page with a wrong token', function () {
    $wall = Wall::factory()->create();

    $this->get(route('walls.manage', ['wall' => $wall, 'k' => 'mauvais-token']))
        ->assertForbidden();
});

it('marks wall pages as noindex but not the landing', function () {
    $wall = Wall::factory()->create();

    $this->get($wall->shareUrl())->assertSee('noindex', escape: false);
    $this->get(route('home'))->assertDontSee('noindex', escape: false);
});
