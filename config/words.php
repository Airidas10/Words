<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Words Per Page
    |--------------------------------------------------------------------------
    |
    | The amount of words to paginate on
    |
    */

    'words_per_page' => env('WORDS_PER_PAGE', 24),

    'questions_per_test' => env('QUESTIONS_PER_TEST', 10),

    'stats_min_attempts' => (int) env('STATS_MIN_ATTEMPTS', 1),
];