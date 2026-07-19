<?php

namespace App\Models;

use Database\Factories\WallFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wall extends Model
{
    /** @use HasFactory<WallFactory> */
    use HasFactory;

    protected $fillable = [
        'public_id',
        'admin_token',
        'name',
        'pin_hash',
        'read_only',
    ];

    protected static function booted(): void
    {
        static::creating(function (Wall $wall) {
            $wall->public_id ??= strtolower(Str::ulid()->toBase32());
            $wall->admin_token ??= Str::random(48);
        });
    }

    /**
     * @return HasMany<Note, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function hasPin(): bool
    {
        return $this->pin_hash !== null;
    }

    public function shareUrl(): string
    {
        return route('walls.show', $this);
    }

    public function adminUrl(): string
    {
        return route('walls.manage', ['wall' => $this, 'k' => $this->admin_token]);
    }

    protected function casts(): array
    {
        return [
            'read_only' => 'boolean',
        ];
    }
}
