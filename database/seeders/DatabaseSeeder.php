<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Tag;
use App\Models\Word;
use App\Models\Translation;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tags = Tag::factory(10)->create();

        $words = Word::factory(30)->create()->each(function ($word) use ($tags){
            $translationCount = $this->getRandomTranslationCount();
            $word->translations()->saveMany(Translation::factory($translationCount)->make());

            $tagsToGet = random_int(0, 3);
            $wordTags = $tags->random($tagsToGet);
            $word->tags()->sync($wordTags->pluck('id'));
        });

        User::create(['username' => 'Foo', 'password' => \Hash::make('secret'), 'is_admin' => 1]);
    }

    protected function getRandomTranslationCount()
    {
        $translationCount = 1;
        $translationCountModifier = random_int(1, 10);
        if($translationCountModifier == 8 || $translationCountModifier == 9){
            $translationCount = 2;
        } else if($translationCountModifier == 10){
            $translationCount = 3;
        }

        return $translationCount;
    }
}
