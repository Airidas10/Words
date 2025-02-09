<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class Test extends Model
{
    protected $fillable = ['user_id', 'number_of_questions', 'questions_and_answers', 'score'];

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->timezone('Europe/Vilnius')->format('Y-m-d H:i:s'),
        );
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->timezone('Europe/Vilnius')->format('Y-m-d H:i:s'),
        );
    }

    public function scopeFinished(Builder $query): void
    {
        $query->whereNotNull('score');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }
}
