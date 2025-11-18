<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'projeto_id',
        'modulo',
        'titulo',
        'descricao',
        'status_id',
    ];

    /**
     * Relacionamento com projeto
     */
    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }

    /**
     * Relacionamento com status
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Relacionamento com itens
     */
    public function itens()
    {
        return $this->hasMany(Item::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($feature) {
            if (empty($feature->numero) && !empty($feature->projeto_id)) {
                $feature->numero = self::gerarNumero($feature->projeto_id);
            }
        });
    }

    private static function gerarNumero($projetoId)
    {
        // Buscar todas as features do projeto e ordenar pela parte inteira do número
        $features = self::where('projeto_id', $projetoId)
            ->whereNotNull('numero')
            ->get()
            ->map(function ($feature) {
                $partes = explode('.', $feature->numero);
                return [
                    'numero' => $feature->numero,
                    'inteiro' => intval($partes[0] ?? 0),
                ];
            })
            ->sortByDesc('inteiro');

        if ($features->isNotEmpty()) {
            $ultimoInteiro = $features->first()['inteiro'];
            $sequencial = $ultimoInteiro + 1;
        } else {
            $sequencial = 1;
        }

        // Retornar no formato "1.0", "2.0", etc.
        return $sequencial . '.0';
    }
}
