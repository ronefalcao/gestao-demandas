<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'itens';

    protected $fillable = [
        'feature_id',
        'numero',
        'titulo',
        'descricao',
        'figma',
        'sprint_id',
        'us',
        'status',
    ];

    /**
     * Relacionamento com feature
     */
    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * Relacionamento com sprint
     */
    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->numero) && !empty($item->feature_id)) {
                $item->numero = self::gerarNumero($item->feature_id);
            }
        });
    }

    private static function gerarNumero($featureId)
    {
        // Buscar a feature para pegar o número base (ex: 1.0)
        $feature = Feature::find($featureId);
        if (!$feature || !$feature->numero) {
            return '1.1';
        }

        // Extrair a parte inteira do número da feature (ex: 1 de "1.0")
        $partesFeature = explode('.', $feature->numero);
        $numeroBase = $partesFeature[0] ?? '1';

        // Buscar todos os itens da feature e ordenar pela parte decimal
        $itens = self::where('feature_id', $featureId)
            ->whereNotNull('numero')
            ->get()
            ->map(function ($item) {
                $partes = explode('.', $item->numero);
                return [
                    'numero' => $item->numero,
                    'decimal' => floatval($item->numero),
                ];
            })
            ->sortByDesc('decimal');

        if ($itens->isNotEmpty()) {
            $ultimoNumero = $itens->first()['numero'];
            $partes = explode('.', $ultimoNumero);
            $ultimoDecimal = intval($partes[1] ?? 0);
            $proximoDecimal = $ultimoDecimal + 1;
        } else {
            $proximoDecimal = 1;
        }

        // Retornar no formato "1.1", "1.2", etc.
        return $numeroBase . '.' . $proximoDecimal;
    }
}
