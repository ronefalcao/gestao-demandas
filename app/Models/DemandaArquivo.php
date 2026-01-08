<?php

namespace App\Models;

use App\Http\Services\S3Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
     * Verifica se o arquivo está armazenado no S3
     */
    public function isS3(): bool
    {
        if (empty($this->caminho)) {
            return false;
        }

        $s3Path = config('filesystems.s3_path', 'gestor/demandas');
        $s3Path = trim($s3Path, '/');

        // Verifica se o caminho começa com o path do S3
        return strpos($this->caminho, $s3Path . '/') === 0;
    }

    /**
     * Verifica se o arquivo está armazenado localmente
     */
    public function isLocal(): bool
    {
        return !$this->isS3();
    }

    /**
     * Retorna a URL do arquivo (S3 ou local)
     */
    public function getUrlAttribute()
    {
        if ($this->isS3()) {
            try {
                $s3Service = app(S3Service::class);
                return $s3Service->temporaryUrl($this->caminho, now()->addMinutes(60));
            } catch (\Exception $e) {
                Log::error('Erro ao gerar URL S3', ['erro' => $e->getMessage(), 'arquivo_id' => $this->id]);
                return null;
            }
        }

        // Arquivo local
        return asset('storage/' . $this->caminho);
    }

    /**
     * Retorna URL temporária para download (S3) ou URL local
     */
    public function getDownloadUrl(int $minutes = 5): string
    {
        if ($this->isS3()) {
            try {
                $s3Service = app(S3Service::class);
                return $s3Service->temporaryUrl(
                    $this->caminho,
                    now()->addMinutes($minutes),
                    ['ResponseContentDisposition' => 'attachment; filename="' . $this->nome_original . '"']
                );
            } catch (\Exception $e) {
                Log::error('Erro ao gerar URL de download S3', ['erro' => $e->getMessage(), 'arquivo_id' => $this->id]);
                throw $e;
            }
        }

        // Arquivo local - retorna rota de download
        return route('demandas.arquivos.download', $this);
    }

    /**
     * Retorna URL temporária para visualização (S3) ou URL local
     */
    public function getViewUrl(int $minutes = 60): string
    {
        if ($this->isS3()) {
            try {
                $s3Service = app(S3Service::class);
                return $s3Service->temporaryUrl($this->caminho, now()->addMinutes($minutes));
            } catch (\Exception $e) {
                Log::error('Erro ao gerar URL de visualização S3', ['erro' => $e->getMessage(), 'arquivo_id' => $this->id]);
                throw $e;
            }
        }

        // Arquivo local - retorna rota de visualização
        return route('demandas.arquivos.view', $this);
    }

    /**
     * Verifica se o arquivo existe (S3 ou local)
     */
    public function exists(): bool
    {
        if (empty($this->caminho)) {
            return false;
        }

        if ($this->isS3()) {
            try {
                $s3Service = app(S3Service::class);
                return $s3Service->exists($this->caminho);
            } catch (\Exception $e) {
                Log::error('Erro ao verificar existência no S3', ['erro' => $e->getMessage(), 'arquivo_id' => $this->id]);
                return false;
            }
        }

        return Storage::disk('public')->exists($this->caminho);
    }

    /**
     * Deleta o arquivo (S3 ou local)
     */
    public function deleteFile(): bool
    {
        if (empty($this->caminho)) {
            return false;
        }

        if ($this->isS3()) {
            try {
                $s3Service = app(S3Service::class);
                return $s3Service->delete($this->caminho);
            } catch (\Exception $e) {
                Log::error('Erro ao deletar arquivo do S3', ['erro' => $e->getMessage(), 'arquivo_id' => $this->id]);
                return false;
            }
        }

        return Storage::disk('public')->delete($this->caminho);
    }

    /**
     * Retorna o conteúdo do arquivo (S3 ou local)
     */
    public function getContent(): string
    {
        if ($this->isS3()) {
            return Storage::disk('s3')->get($this->caminho);
        }

        return Storage::disk('public')->get($this->caminho);
    }

    /**
     * Retorna o MIME type do arquivo (S3 ou local)
     */
    public function getMimeType(): string
    {
        // Tentar obter do tipo salvo no banco
        if ($this->tipo) {
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'psd' => 'image/vnd.adobe.photoshop',
                'mp4' => 'video/mp4',
            ];

            $extensao = strtolower($this->tipo);
            if (isset($mimeTypes[$extensao])) {
                return $mimeTypes[$extensao];
            }
        }

        // Fallback: tentar detectar pelo conteúdo
        try {
            if ($this->isS3()) {
                $conteudo = Storage::disk('s3')->get($this->caminho);
            } else {
                $conteudo = Storage::disk('public')->get($this->caminho);
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $conteudo);
            finfo_close($finfo);

            return $mimeType ?: 'application/octet-stream';
        } catch (\Exception $e) {
            return 'application/octet-stream';
        }
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
