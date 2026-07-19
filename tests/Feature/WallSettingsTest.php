<?php

use App\Models\Wall;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

function settings(Wall $wall, bool $asAdmin = true): Testable
{
    if ($asAdmin) {
        session()->put("wall_admin_{$wall->id}", true);
    }

    return Livewire::test('wall-settings', ['wall' => $wall]);
}

it('renames the wall', function () {
    $wall = Wall::factory()->create(['name' => 'Ancien nom']);

    settings($wall)
        ->set('name', 'Nouveau nom')
        ->call('updateName')
        ->assertHasNoErrors();

    expect($wall->refresh()->name)->toBe('Nouveau nom');
});

it('rejects an invalid name', function () {
    $wall = Wall::factory()->create(['name' => 'Ancien nom']);

    settings($wall)->set('name', '')->call('updateName')->assertHasErrors(['name' => 'required']);

    expect($wall->refresh()->name)->toBe('Ancien nom');
});

it('toggles read-only mode', function () {
    $wall = Wall::factory()->create();

    settings($wall)->set('readOnly', true);
    expect($wall->refresh()->read_only)->toBeTrue();

    settings($wall)->set('readOnly', false);
    expect($wall->refresh()->read_only)->toBeFalse();
});

it('sets, changes and removes the PIN', function () {
    $wall = Wall::factory()->create();

    settings($wall)->set('newPin', '4242')->call('setPin')->assertHasNoErrors();
    expect(Hash::check('4242', (string) $wall->refresh()->pin_hash))->toBeTrue();

    settings($wall)->set('newPin', '123456')->call('setPin')->assertHasNoErrors();
    expect(Hash::check('123456', (string) $wall->refresh()->pin_hash))->toBeTrue();

    settings($wall)->call('removePin');
    expect($wall->refresh()->pin_hash)->toBeNull();
});

it('rejects a PIN that is not 4 to 8 digits', function () {
    $wall = Wall::factory()->create();

    settings($wall)->set('newPin', '12')->call('setPin')->assertHasErrors('newPin');
    settings($wall)->set('newPin', 'abcd')->call('setPin')->assertHasErrors('newPin');

    expect($wall->refresh()->pin_hash)->toBeNull();
});

it('refuses settings changes without the admin session', function () {
    $wall = Wall::factory()->create(['name' => 'Intouchable']);

    settings($wall, asAdmin: false)
        ->set('name', 'Piraté')
        ->call('updateName')
        ->assertStatus(403);

    expect($wall->refresh()->name)->toBe('Intouchable');
});
