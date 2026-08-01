<?php

use App\Models\User;
use App\Models\Word;
use Illuminate\Support\Facades\Config;

it('aborts immediately when count is higher than the struggles CAP', function () {
    Config::set('words.struggles_cap', 5);

    $user = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);
    recordWordAttempts($user, $word, correct: 0, incorrect: 2);

    $this->artisan('words:seed-struggle', ['--count' => 6])
        ->assertFailed();

    $this->assertDatabaseCount('user_word', 0);
});

it('seeds each users worst words up to the requested count', function () {
    Config::set('words.struggles_cap', 50);

    $user = User::factory()->create();
    $worst = Word::factory()->create(['word' => 'Worst']);
    $worst->translations()->create(['translation' => 'A']);
    $mid = Word::factory()->create(['word' => 'Mid']);
    $mid->translations()->create(['translation' => 'B']);
    $best = Word::factory()->create(['word' => 'Best']);
    $best->translations()->create(['translation' => 'C']);

    recordWordAttempts($user, $worst, correct: 0, incorrect: 2);
    recordWordAttempts($user, $mid, correct: 1, incorrect: 1);
    recordWordAttempts($user, $best, correct: 2, incorrect: 0);

    $this->artisan('words:seed-struggle', ['--count' => 2])
        ->assertSuccessful();

    expect($user->fresh()->struggleWords->pluck('id')->sort()->values()->all())
        ->toBe(collect([$worst->id, $mid->id])->sort()->values()->all());
});

it('does not duplicate words already in struggles when seeding', function () {
    Config::set('words.struggles_cap', 50);

    $user = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);
    recordWordAttempts($user, $word, correct: 0, incorrect: 1);
    attachStruggleWord($user, $word);

    $this->artisan('words:seed-struggle', ['--count' => 1])
        ->assertSuccessful();

    expect($user->fresh()->struggleWords->count())->toBe(1);
});

it('isolates seeded struggles per user', function () {
    Config::set('words.struggles_cap', 50);

    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $wordA = Word::factory()->create(['word' => 'ForA']);
    $wordA->translations()->create(['translation' => 'A']);
    $wordB = Word::factory()->create(['word' => 'ForB']);
    $wordB->translations()->create(['translation' => 'B']);

    recordWordAttempts($userA, $wordA, correct: 0, incorrect: 1);
    recordWordAttempts($userB, $wordB, correct: 0, incorrect: 1);

    $this->artisan('words:seed-struggle', ['--count' => 1])
        ->assertSuccessful();

    expect($userA->fresh()->struggleWords->pluck('id')->all())->toBe([$wordA->id])
        ->and($userB->fresh()->struggleWords->pluck('id')->all())->toBe([$wordB->id]);
});

it('does not write to the database on dry-run', function () {
    Config::set('words.struggles_cap', 50);

    $user = User::factory()->create();
    $word = Word::factory()->create(['word' => 'Ciao']);
    $word->translations()->create(['translation' => 'Hello']);
    recordWordAttempts($user, $word, correct: 0, incorrect: 2);

    $this->artisan('words:seed-struggle', ['--count' => 1, '--dry-run' => true])
        ->expectsOutputToContain('Ciao')
        ->assertSuccessful();

    $this->assertDatabaseCount('user_word', 0);
});

it('does not attach more words than remaining CAP capacity', function () {
    Config::set('words.struggles_cap', 2);

    $user = User::factory()->create();
    $existing = Word::factory()->create(['word' => 'Existing']);
    $existing->translations()->create(['translation' => 'E']);
    attachStruggleWord($user, $existing);

    $words = Word::factory()->count(3)->create()->each(function (Word $word) {
        $word->translations()->create(['translation' => 'T']);
    });

    foreach ($words->values() as $index => $word) {
        recordWordAttempts($user, $word, correct: 0, incorrect: 3 - $index);
    }

    $this->artisan('words:seed-struggle', ['--count' => 3])
        ->assertSuccessful();

    expect($user->fresh()->struggleWords->count())->toBe(2);
});
