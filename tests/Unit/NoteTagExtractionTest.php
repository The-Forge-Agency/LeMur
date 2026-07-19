<?php

use App\Models\Note;

it('extracts hashtags from note content', function () {
    expect(Note::extractTags('On rachète du PQ #Courses et du café #courses #Coloc'))
        ->toBe(['courses', 'coloc']);
});

it('returns an empty array when there is no hashtag', function () {
    expect(Note::extractTags('Une note sans tag'))->toBe([]);
});

it('supports accented and underscored hashtags', function () {
    expect(Note::extractTags('#Voyage_2026 direction #Norvège !'))
        ->toBe(['voyage_2026', 'norvège']);
});
