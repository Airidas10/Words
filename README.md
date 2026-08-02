# Words (Parole)

Personal Italian vocabulary app: browse and search words, tag them, practice with Daily Dose tests, track accuracy stats, and keep a **My Struggles** list for harder words.

## Stack

Laravel 13, Inertia + Vue 3, MariaDB, Redis (cache). Local dev via Laravel Sail (`words.wip:8080`).

## Local

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
npm install && npm run dev
```

Tests: `./vendor/bin/sail artisan test` (Pest). Browser tests need Playwright inside Sail.
