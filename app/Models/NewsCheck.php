<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsCheck extends Model
{
    use HasFactory;

    // Allow mass assignment for these fields
    protected $fillable = [
        'content',
        'verdict',
        'ai_response',
    ];
}
