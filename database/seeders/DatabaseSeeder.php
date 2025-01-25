<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Tag;
use App\Models\Word;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tags = Tag::factory(10)->create();
        $words = Word::factory(30)->create();

        foreach($words as $word){
            $tagsToGet = random_int(0, 3);
            $wordTags = $tags->random($tagsToGet);
            $word->tags()->sync($wordTags->pluck('id'));
        }
    }
}
