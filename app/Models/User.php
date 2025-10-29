<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'password',
        'tipo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function demandasSolicitadas()
    {
        return $this->hasMany(Demanda::class, 'solicitante_id');
    }

    public function demandasResponsaveis()
    {
        return $this->hasMany(Demanda::class, 'responsavel_id');
    }

    /**
     * Verifica se o usuário é administrador
     */
    public function isAdmin(): bool
    {
        return $this->tipo === 'administrador';
    }

    /**
     * Verifica se o usuário é um usuário comum
     */
    public function isUsuario(): bool
    {
        return $this->tipo === 'usuario';
    }
}
