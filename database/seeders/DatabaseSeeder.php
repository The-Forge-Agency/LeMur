<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Wall;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a demo wall.
     */
    public function run(): void
    {
        $wall = Wall::factory()->create(['name' => 'Punchlines du squad']);

        $notes = [
            ['content' => '« Je suis pas en retard, je suis en décalé horaire » #BestOf', 'author' => 'Karim', 'color' => 'jaune', 'reactions' => ['😂' => 12], 'pinned' => true],
            ['content' => 'Racheter du PQ !!! #Courses', 'author' => 'Léa', 'color' => 'bleu', 'reactions' => ['👍' => 3], 'pinned' => false],
            ['content' => 'Trattoria da Enzo, la meilleure carbo de Rome #Voyage', 'author' => 'Jules', 'color' => 'vert', 'reactions' => ['❤️' => 7], 'pinned' => false],
            ['content' => 'Joyeux anniv Sarah, reine de la coloc 👑 #Anniv', 'author' => null, 'color' => 'rose', 'reactions' => ['🔥' => 9], 'pinned' => false],
            ['content' => 'Et si on faisait un mur pour chaque voyage ? #Idées', 'author' => 'Nina', 'color' => 'violet', 'reactions' => [], 'pinned' => false],
        ];

        foreach ($notes as $index => $attributes) {
            Note::factory()->for($wall)->create([
                ...$attributes,
                'author_token' => Str::random(40),
                'tags' => Note::extractTags($attributes['content']),
                'created_at' => now()->subHours(count($notes) - $index),
            ]);
        }

        $this->command->info("Mur de démo : {$wall->shareUrl()}");
        $this->command->info("Lien admin : {$wall->adminUrl()}");
    }
}
