<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = ['translation', 'test_help', 'hidden_from_card'];
    
    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }
}
