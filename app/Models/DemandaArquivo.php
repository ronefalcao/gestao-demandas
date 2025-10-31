<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandaArquivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'demanda_id',
        'nome_original',
        'nome_arquivo',
        'caminho',
        'tipo',
        'tamanho',
    ];

    /**
     * Relacionamento com demanda
     */
    public function demanda()
    {
        return $this->belongsTo(Demanda::class);
    }

    /**
     * Retorna o caminho completo do arquivo para download
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->caminho);
    }

    /**
     * Formata o tamanho do arquivo
     */
    public function getTamanhoFormatadoAttribute()
    {
        if (!$this->tamanho) {
            return 'N/A';
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $tamanho = $this->tamanho;
        $unidade = 0;

        while ($tamanho >= 1024 && $unidade < count($unidades) - 1) {
            $tamanho /= 1024;
            $unidade++;
        }

        return round($tamanho, 2) . ' ' . $unidades[$unidade];
    }
}
