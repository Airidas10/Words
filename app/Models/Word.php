<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Word extends Model
{
    use HasFactory;

    protected $fillable = ['word', 'translation_id', 'description'];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('tag');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    public function relatedWords()
    {
        return $this->belongsToMany(Word::class, 'related_words', 'word_id', 'related_word_id');
    }
}
