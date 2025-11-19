<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projeto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento com demandas
     */
    public function demandas()
    {
        return $this->hasMany(Demanda::class);
    }

    /**
     * Relacionamento muitos-para-muitos com usuários
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'projeto_user');
    }

    /**
     * Relacionamento com features
     */
    public function features()
    {
        return $this->hasMany(Feature::class);
    }

    /**
     * Relacionamento com módulos
     */
    public function modulos()
    {
        return $this->hasMany(Modulo::class);
    }
}
