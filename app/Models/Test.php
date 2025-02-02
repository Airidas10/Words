<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Test extends Model
{
    protected $fillable = ['user_id', 'number_of_questions', 'questions_and_answers', 'score'];

    public function users(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }
}
