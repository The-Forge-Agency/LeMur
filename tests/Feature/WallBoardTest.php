<?php

use App\Models\Note;
use App\Models\Wall;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

function board(Wall $wall, string $token = 'mon-token'): Testable
{
    return Livewire::withCookies(['lemur_token' => $token])->test('wall-board', ['wall' => $wall]);
}

it('adds a note with author, color and extracted tags', function () {
    $wall = Wall::factory()->create();

    board($wall)
        ->set('content', 'La meilleure carbo de Rome #Voyage')
        ->set('author', 'Jules')
        ->set('color', 'vert')
        ->call('addNote')
        ->assertHasNoErrors()
        ->assertSet('content', '');

    $note = $wall->notes()->firstOrFail();

    expect($note->content)->toBe('La meilleure carbo de Rome #Voyage')
        ->and($note->author)->toBe('Jules')
        ->and($note->color)->toBe('vert')
        ->and($note->tags)->toBe(['voyage'])
        ->and($note->author_token)->toBe('mon-token');
});

it('allows an anonymous note without pseudo', function () {
    $wall = Wall::factory()->create();

    board($wall)->set('content', 'Note anonyme')->call('addNote')->assertHasNoErrors();

    expect($wall->notes()->firstOrFail()->author)->toBeNull();
});

it('rejects an empty or too long note', function () {
    $wall = Wall::factory()->create();

    board($wall)->set('content', '')->call('addNote')->assertHasErrors(['content' => 'required']);
    board($wall)->set('content', str_repeat('a', 501))->call('addNote')->assertHasErrors(['content' => 'max']);

    expect($wall->notes()->count())->toBe(0);
});

it('lets the author edit their own note', function () {
    $note = Note::factory()->create(['author_token' => 'mon-token', 'content' => 'Avant']);

    board($note->wall)
        ->call('startEdit', $note->id)
        ->assertSet('editingContent', 'Avant')
        ->set('editingContent', 'Après #Modif')
        ->call('updateNote')
        ->assertHasNoErrors();

    expect($note->refresh()->content)->toBe('Après #Modif')
        ->and($note->tags)->toBe(['modif']);
});

it('prevents editing a note that belongs to someone else', function () {
    $note = Note::factory()->create(['author_token' => 'le-token-de-quelqu-un-d-autre', 'content' => 'Original']);

    board($note->wall)
        ->call('startEdit', $note->id)
        ->assertStatus(403)
        ->assertSet('editingId', null);

    expect($note->refresh()->content)->toBe('Original');
});

it('lets the author delete their own note', function () {
    $note = Note::factory()->create(['author_token' => 'mon-token']);

    board($note->wall)->call('deleteNote', $note->id);

    expect(Note::count())->toBe(0);
});

it('prevents deleting a note that belongs to someone else', function () {
    $note = Note::factory()->create(['author_token' => 'le-token-de-quelqu-un-d-autre']);

    board($note->wall)->call('deleteNote', $note->id)->assertStatus(403);

    expect(Note::count())->toBe(1);
});

it('lets the wall admin moderate any note', function () {
    $note = Note::factory()->create(['author_token' => 'le-token-de-quelqu-un-d-autre']);

    session()->put("wall_admin_{$note->wall->id}", true);

    board($note->wall)->call('togglePinned', $note->id);
    expect($note->refresh()->pinned)->toBeTrue();

    board($note->wall)->call('deleteNote', $note->id);
    expect(Note::count())->toBe(0);
});

it('lets the author pin and unpin their note', function () {
    $note = Note::factory()->create(['author_token' => 'mon-token']);

    board($note->wall)->call('togglePinned', $note->id);
    expect($note->refresh()->pinned)->toBeTrue();

    board($note->wall)->call('togglePinned', $note->id);
    expect($note->refresh()->pinned)->toBeFalse();
});

it('shows pinned notes first, then the newest', function () {
    $wall = Wall::factory()->create();
    $old = Note::factory()->for($wall)->create(['created_at' => now()->subDay()]);
    $new = Note::factory()->for($wall)->create(['created_at' => now()]);
    $pinned = Note::factory()->for($wall)->pinned()->create(['created_at' => now()->subWeek()]);

    $component = board($wall);

    expect($component->get('notes')->pluck('id')->all())
        ->toBe([$pinned->id, $new->id, $old->id]);
});

it('blocks writing on a read-only wall', function () {
    $wall = Wall::factory()->readOnly()->create();

    board($wall)->set('content', 'Interdit')->call('addNote')->assertStatus(403);

    expect($wall->notes()->count())->toBe(0);
});

it('lets the admin write on a read-only wall', function () {
    $wall = Wall::factory()->readOnly()->create();

    session()->put("wall_admin_{$wall->id}", true);

    board($wall)->set('content', 'Le boss écrit')->call('addNote')->assertHasNoErrors();

    expect($wall->notes()->count())->toBe(1);
});

it('requires the PIN before writing on a locked wall', function () {
    $wall = Wall::factory()->withPin('4242')->create();

    board($wall)->set('content', 'Sans code')->call('addNote')->assertStatus(403);

    expect($wall->notes()->count())->toBe(0);
});

it('rejects a wrong PIN', function () {
    $wall = Wall::factory()->withPin('4242')->create();

    board($wall)
        ->set('pin', '0000')
        ->call('unlock')
        ->assertHasErrors('pin');

    expect(session()->get("wall_unlocked_{$wall->id}"))->toBeNull();
});

it('unlocks the wall with the right PIN and allows writing', function () {
    $wall = Wall::factory()->withPin('4242')->create();

    board($wall)
        ->set('pin', '4242')
        ->call('unlock')
        ->assertHasNoErrors()
        ->set('content', 'Déverrouillé !')
        ->call('addNote')
        ->assertHasNoErrors();

    expect($wall->notes()->count())->toBe(1);
});

it('adds and removes emoji reactions without going below zero', function () {
    $note = Note::factory()->create(['reactions' => []]);

    $component = board($note->wall);

    $component->call('react', $note->id, '🔥', true);
    $component->call('react', $note->id, '🔥', true);
    expect($note->refresh()->reactions)->toBe(['🔥' => 2]);

    $component->call('react', $note->id, '🔥', false);
    $component->call('react', $note->id, '🔥', false);
    $component->call('react', $note->id, '🔥', false);
    expect($note->refresh()->reactions)->toBe([]);
});

it('rejects an emoji outside the allowed list', function () {
    $note = Note::factory()->create(['reactions' => []]);

    board($note->wall)->call('react', $note->id, '💩', true)->assertStatus(422);

    expect($note->refresh()->reactions)->toBe([]);
});

it('filters notes by tag', function () {
    $wall = Wall::factory()->create();
    $courses = Note::factory()->for($wall)->create(['content' => 'PQ #Courses', 'tags' => ['courses']]);
    Note::factory()->for($wall)->create(['content' => 'Carbo #Voyage', 'tags' => ['voyage']]);

    $component = board($wall)->call('filterByTag', 'courses');

    expect($component->get('notes')->pluck('id')->all())->toBe([$courses->id]);

    // Recliquer sur le tag actif le désactive.
    $component->call('filterByTag', 'courses');
    expect($component->get('notes')->count())->toBe(2);
});

it('searches notes by content and author', function () {
    $wall = Wall::factory()->create();
    $match = Note::factory()->for($wall)->create(['content' => 'La carbo de Rome', 'author' => 'Jules']);
    Note::factory()->for($wall)->create(['content' => 'Autre chose', 'author' => 'Léa']);

    $component = board($wall)->set('search', 'carbo');
    expect($component->get('notes')->pluck('id')->all())->toBe([$match->id]);

    $component = board($wall)->set('search', 'jules');
    expect($component->get('notes')->pluck('id')->all())->toBe([$match->id]);
});

it('cannot manage notes of another wall through this board', function () {
    $wall = Wall::factory()->create();
    $foreignNote = Note::factory()->create(['author_token' => 'mon-token']);

    expect(fn () => board($wall)->call('deleteNote', $foreignNote->id))
        ->toThrow(ModelNotFoundException::class);

    expect(Note::count())->toBe(1);
});
