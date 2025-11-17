<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demanda extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'data',
        'cliente_id',
        'projeto_id',
        'solicitante_id',
        'responsavel_id',
        'modulo',
        'descricao',
        'status_id',
        'prioridade',
        'observacao',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($demanda) {
            if (empty($demanda->numero)) {
                $demanda->numero = self::gerarNumero();
            }
        });
    }

    private static function gerarNumero()
    {
        // Usar sintaxe compatível com PostgreSQL
        $ultimaDemanda = self::orderByRaw('CAST(numero AS INTEGER) DESC')
            ->first();

        if ($ultimaDemanda && is_numeric($ultimaDemanda->numero)) {
            $sequencial = intval($ultimaDemanda->numero) + 1;
        } else {
            $sequencial = 1;
        }

        return str_pad($sequencial, 5, '0', STR_PAD_LEFT);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function projeto()
    {
        return $this->belongsTo(Projeto::class);
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function arquivos()
    {
        return $this->hasMany(DemandaArquivo::class);
    }
}
