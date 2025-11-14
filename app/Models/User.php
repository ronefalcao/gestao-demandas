<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasName
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
     * Verifica se o usuário é gestor
     */
    public function isGestor(): bool
    {
        return $this->tipo === 'gestor';
    }

    /**
     * Verifica se o usuário é um usuário comum
     */
    public function isUsuario(): bool
    {
        return $this->tipo === 'usuario';
    }

    /**
     * Verifica se o usuário pode visualizar todas as demandas
     */
    public function canViewAllDemandas(): bool
    {
        return $this->isAdmin() || $this->isGestor();
    }

    /**
     * Verifica se o usuário pode gerenciar usuários, clientes e status
     */
    public function canManageSystem(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Relacionamento muitos-para-muitos com projetos
     */
    public function projetos()
    {
        return $this->belongsToMany(Projeto::class, 'projeto_user');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canManageSystem() || $this->isGestor();
    }

    public function getFilamentName(): string
    {
        return $this->nome ?: ($this->email ?: 'Usuário');
    }
}
