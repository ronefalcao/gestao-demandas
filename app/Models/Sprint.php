<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'inicio',
        'termino',
    ];

    protected $casts = [
        'inicio' => 'date',
        'termino' => 'date',
    ];

    /**
     * Relacionamento com itens
     */
    public function itens()
    {
        return $this->hasMany(Item::class);
    }
}
