<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    public $timestamps = false; // We only use created_at here

    protected $fillable = [
        'user_id',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'response_time_ms'
    ];
}
