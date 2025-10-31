<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demanda extends Model
{
    use HasFactory;

    protected $fillable = [
        'data',
        'cliente_id',
        'projeto_id',
        'solicitante_id',
        'responsavel_id',
        'modulo',
        'descricao',
        'status_id',
        'observacao',
    ];

    protected $casts = [
        'data' => 'date',
    ];

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
}
