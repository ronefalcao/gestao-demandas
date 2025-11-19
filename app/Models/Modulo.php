<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $fillable = [
        'projeto_id',
        'nome',
        'descricao',
    ];

    /**
     * Relacionamento com projeto
     */
    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }

    /**
     * Relacionamento com features
     */
    public function features()
    {
        return $this->hasMany(Feature::class);
    }
}
