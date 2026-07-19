<?php

namespace App\Models;

use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    /** @use HasFactory<NoteFactory> */
    use HasFactory;

    public const COLORS = ['jaune', 'rose', 'bleu', 'vert', 'violet', 'orange'];

    public const REACTION_EMOJIS = ['😂', '🔥', '👍', '❤️'];

    protected $fillable = [
        'wall_id',
        'content',
        'author',
        'author_token',
        'color',
        'tags',
        'reactions',
        'pinned',
    ];

    /**
     * @return BelongsTo<Wall, $this>
     */
    public function wall(): BelongsTo
    {
        return $this->belongsTo(Wall::class);
    }

    public function isOwnedBy(?string $authorToken): bool
    {
        return $authorToken !== null && $authorToken !== '' && hash_equals($this->author_token, $authorToken);
    }

    /**
     * Extract #hashtags from a note's text content.
     *
     * @return list<string>
     */
    public static function extractTags(string $content): array
    {
        preg_match_all('/#([\p{L}\p{N}_-]+)/u', $content, $matches);

        return array_values(array_unique(array_map(
            fn (string $tag): string => mb_strtolower($tag),
            $matches[1],
        )));
    }

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'reactions' => 'array',
            'pinned' => 'boolean',
        ];
    }
}
