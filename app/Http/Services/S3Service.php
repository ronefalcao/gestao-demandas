<?php

namespace App\Http\Services;

use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service
{
    protected array $extensoes = [
        'application/pdf'                                                           => 'pdf',
        'application/msword'                                                        => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
        'application/vnd.ms-excel'                                                  => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
        'application/vnd.ms-powerpoint'                                             => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'video/mp4'                                                                 => 'mp4',
        'image/jpeg'                                                                => 'jpg',
        'image/png'                                                                 => 'png',
        'image/vnd.adobe.photoshop'                                                 => 'psd',
    ];

    /**
     * Faz upload de um arquivo para o S3
     *
     * @param UploadedFile $arquivo
     * @param string $pasta Caminho relativo (ex: "1/arquivos")
     * @return array
     * @throws Exception
     */
    public function uploadFormData(UploadedFile $arquivo, string $pasta): array
    {
        if (!$arquivo->isValid()) {
            throw new Exception('Arquivo inválido ou corrompido.');
        }

        $mimeType = $arquivo->getMimeType();
        $extensao = $this->extensoes[$mimeType] ?? $arquivo->getClientOriginalExtension();

        $filename = Str::slug(pathinfo($arquivo->getClientOriginalName(), PATHINFO_FILENAME), '_');
        $timestamp = now()->format('Ymd_His');
        $filename = $this->sanitizeFileName("{$filename}_{$timestamp}.{$extensao}");
        $path = $this->getFullPath($pasta, $filename);

        Storage::disk('s3')->put($path, file_get_contents($arquivo->getRealPath()));

        return [
            'nome' => $filename,
            'nome_original' => $arquivo->getClientOriginalName(),
            'tipo' => $mimeType,
            'caminho' => $path,
            'tamanho' => $arquivo->getSize(),
            'extensao' => $extensao,
        ];
    }

    /**
     * Faz upload de múltiplos arquivos
     *
     * @param array $arquivos
     * @param string $pasta
     * @return array
     */
    public function uploadMultiple(array $arquivos, string $pasta): array
    {
        $resultados = [];
        $pasta = $this->getFullPath($pasta);

        foreach ($arquivos as $arquivo) {
            if ($arquivo instanceof UploadedFile) {
                $resultados[] = $this->uploadFormData($arquivo, $pasta);
            }
        }

        return $resultados;
    }

    /**
     * Deleta um arquivo do S3
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        return Storage::disk('s3')->delete($path);
    }

    /**
     * Deleta múltiplos arquivos do S3
     *
     * @param array $paths
     * @return bool
     */
    public function deleteMultiple(array $paths): bool
    {
        $success = true;

        foreach ($paths as $path) {
            if (!$this->delete($path)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Retorna a URL pública do arquivo (se configurado)
     *
     * @param string $path
     * @return string
     */
    public function getUrl(string $path): string
    {
        return Storage::disk('s3')->url($path);
    }

    /**
     * Verifica se um arquivo existe no S3
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return Storage::disk('s3')->exists($path);
    }

    /**
     * Faz upload de um arquivo com nome específico
     *
     * @param UploadedFile $arquivo
     * @param string $pasta
     * @param string $nomeArquivo
     * @return array
     * @throws Exception
     */
    public function putFileAsUploaded(UploadedFile $arquivo, string $pasta, string $nomeArquivo): array
    {
        if (!$arquivo->isValid()) {
            throw new Exception('Arquivo inválido ou corrompido.');
        }

        $mimeType = $arquivo->getMimeType();
        $extensao = $this->extensoes[$mimeType] ?? $arquivo->getClientOriginalExtension();

        $path = $this->getFullPath($pasta, $nomeArquivo);

        $disk = Storage::disk('s3');
        /** @var FilesystemAdapter $disk */
        $returned = $disk->putFileAs($pasta, $arquivo, $nomeArquivo);

        return [
            'nome' => $nomeArquivo,
            'nome_original' => $arquivo->getClientOriginalName(),
            'tipo' => $mimeType,
            'caminho' => $returned ?: $path,
            'tamanho' => $arquivo->getSize(),
            'extensao' => $extensao,
        ];
    }

    /**
     * Gera uma URL temporária assinada para um arquivo no S3
     *
     * @param string $path
     * @param \DateTimeInterface $expiry
     * @param array $options Opcional: ['ResponseContentDisposition' => 'attachment; filename="name.pdf"']
     * @return string
     */
    public function temporaryUrl(string $path, \DateTimeInterface $expiry, array $options = []): string
    {
        $disk = Storage::disk('s3');
        /** @var FilesystemAdapter $disk */
        if (!empty($options)) {
            return $disk->temporaryUrl($path, $expiry, $options);
        }

        return $disk->temporaryUrl($path, $expiry);
    }

    /**
     * Sanitiza o nome do arquivo removendo caracteres especiais
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFileName(string $filename): string
    {
        // Remove caracteres especiais e espaços
        $filename = preg_replace('/[^a-zA-Z0-9\.\-\_]/', '_', $filename);

        // Remove múltiplos underscores consecutivos
        $filename = preg_replace('/_{2,}/', '_', $filename);

        return $filename;
    }

    /**
     * Retorna o caminho completo no S3
     * Combina o path base do .env com o caminho relativo fornecido
     *
     * @param string $path Caminho relativo (ex: "1/arquivos")
     * @param string|null $fileName Nome do arquivo (opcional)
     * @return string
     */
    private function getFullPath(string $path, ?string $fileName = null): string
    {
        // Obter o path base da configuração (ex: "gestor/demandas")
        $basePath = config('filesystems.s3_path', 'gestor/demandas');

        // Remover barras no início e fim
        $basePath = trim($basePath, '/');

        // Remover barras no início e fim do path relativo
        $relativePath = trim($path, '/');

        // Construir o caminho completo
        if ($relativePath) {
            $fullPath = $basePath . '/' . $relativePath;
        } else {
            $fullPath = $basePath;
        }

        // Adicionar o nome do arquivo se fornecido
        if ($fileName) {
            $fullPath = rtrim($fullPath, '/') . '/' . ltrim($fileName, '/');
        }

        return $fullPath;
    }
}
