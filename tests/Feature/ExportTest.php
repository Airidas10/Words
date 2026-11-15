<?php

use App\Http\Controllers\WordController;
use App\Models\User;
use App\Models\Word;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

it('redirects guests away from export', function () {
    $this->get('/export')->assertRedirect('/login');
});

it('downloads a csv of words for an authenticated user', function () {
    Carbon::setTestNow(Carbon::parse('2024-06-15 12:00:00'));

    Sanctum::actingAs(User::factory()->create());

    $word = createWordWithTranslationAndTag('Ciao', 'Hello', 'Greeting');
    $word->translations()->create(['translation' => 'Hi']);

    Word::factory()->create(['word' => 'Solo']);

    $filename = 'words-'.Carbon::now()->format('m-d-Y').'.csv';

    $response = $this->get('/export');

    $response->assertOk()
        ->assertDownload($filename);

    $handle = fopen($response->baseResponse->getFile()->getPathname(), 'r');
    $header = fgetcsv($handle);
    $translationsByWord = [];

    while (($row = fgetcsv($handle)) !== false) {
        [$word, $translations] = $row;
        $translationsByWord[$word] = $translations;
    }

    fclose($handle);

    $ciaoTranslations = array_map('trim', explode(',', $translationsByWord['Ciao']));

    expect($header)->toBe(['Word', 'Translations'])
        ->and($ciaoTranslations)->toEqualCanonicalizing(['Hello', 'Hi'])
        ->and($translationsByWord['Solo'])->toBe('-');
});

it('returns an error page when csv generation fails', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->partialMock(WordController::class, function ($mock) {
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('generateWordsCsv')
            ->once()
            ->andThrow(new Exception('CSV generation failed'));
    });

    $this->get('/export')
        ->assertStatus(500)
        ->assertSee('Export Failed')
        ->assertSee('Sorry, the CSV file could not be generated. Please try again later.')
        ->assertSee('Back to Home');
});

it('returns an error page when the csv file is missing', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->partialMock(WordController::class, function ($mock) {
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('generateWordsCsv')
            ->once()
            ->andReturn(storage_path('app/does-not-exist-'.uniqid().'.csv'));
    });

    $this->get('/export')
        ->assertStatus(500)
        ->assertSee('Export Failed')
        ->assertSee('Sorry, the CSV file could not be generated. Please try again later.');
});
