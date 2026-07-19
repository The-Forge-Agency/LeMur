<?php

use App\Models\Wall;
use Livewire\Livewire;

it('creates a wall and redirects to the admin page', function () {
    Livewire::test('create-wall')
        ->set('name', 'Punchlines du squad')
        ->call('create')
        ->assertHasNoErrors();

    $wall = Wall::firstOrFail();

    expect($wall->name)->toBe('Punchlines du squad')
        ->and($wall->public_id)->not->toBeEmpty()
        ->and($wall->admin_token)->not->toBeEmpty()
        ->and(session()->get("wall_admin_{$wall->id}"))->toBeTrue();
});

it('requires a name to create a wall', function () {
    Livewire::test('create-wall')
        ->set('name', '')
        ->call('create')
        ->assertHasErrors(['name' => 'required']);

    expect(Wall::count())->toBe(0);
});

it('generates unguessable distinct public ids', function () {
    $first = Wall::factory()->create();
    $second = Wall::factory()->create();

    expect($first->public_id)->not->toBe($second->public_id)
        ->and(mb_strlen($first->public_id))->toBeGreaterThanOrEqual(20);
});
