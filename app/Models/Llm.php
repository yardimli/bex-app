<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Llm extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'context_length',
        'prompt_price',
        'completion_price',
        'created_at_openrouter',
    ];

    protected $casts = [
        'created_at_openrouter' => 'datetime',
        'prompt_price' => 'float',
        'completion_price' => 'float',
    ];
}
