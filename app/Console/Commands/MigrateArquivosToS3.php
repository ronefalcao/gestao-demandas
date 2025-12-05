<?php

namespace App\Console\Commands;

use App\Http\Services\S3Service;
use App\Models\DemandaArquivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MigrateArquivosToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arquivos:migrate-to-s3 
                            {--dry-run : Executa sem fazer alterações, apenas mostra o que seria migrado}
                            {--force : Força a migração mesmo se o arquivo já existir no S3}
                            {--demanda-id= : Migra apenas arquivos de uma demanda específica}
                            {--limit= : Limita o número de arquivos a migrar}
                            {--skip-existing : Pula arquivos que já estão no S3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra arquivos de demandas do armazenamento local para o S3';

    protected S3Service $s3Service;
    protected int $total = 0;
    protected int $sucesso = 0;
    protected int $erro = 0;
    protected int $pulados = 0;
    protected array $erros = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando migração de arquivos para S3...');
        $this->newLine();

        // Verificar se o S3 está configurado
        if (!$this->verificarConfiguracaoS3()) {
            $this->error('❌ Configuração do S3 não encontrada. Verifique as variáveis de ambiente.');
            return Command::FAILURE;
        }

        $this->s3Service = new S3Service();

        // Buscar arquivos para migrar
        $query = DemandaArquivo::with('demanda');

        if ($this->option('demanda-id')) {
            $query->where('demanda_id', $this->option('demanda-id'));
        }

        $arquivos = $query->get();
        $this->total = $arquivos->count();

        if ($this->total === 0) {
            $this->warn('⚠️  Nenhum arquivo encontrado para migrar.');
            return Command::SUCCESS;
        }

        $this->info("📦 Total de arquivos encontrados: {$this->total}");
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🔍 Modo DRY-RUN: Nenhuma alteração será feita.');
            $this->newLine();
        }

        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $bar = $this->output->createProgressBar($limit ?? $this->total);
        $bar->start();

        foreach ($arquivos as $index => $arquivo) {
            if ($limit && $index >= $limit) {
                break;
            }

            try {
                $this->migrarArquivo($arquivo);
                $this->sucesso++;
            } catch (\Exception $e) {
                $this->erro++;
                $this->erros[] = [
                    'arquivo_id' => $arquivo->id,
                    'demanda_id' => $arquivo->demanda_id,
                    'caminho' => $arquivo->caminho,
                    'erro' => $e->getMessage(),
                ];
                $this->error("\n❌ Erro ao migrar arquivo ID {$arquivo->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Exibir resumo
        $this->exibirResumo();

        // Salvar log de erros se houver
        if (!empty($this->erros)) {
            $this->salvarLogErros();
        }

        return Command::SUCCESS;
    }

    /**
     * Verifica se o S3 está configurado
     */
    protected function verificarConfiguracaoS3(): bool
    {
        $required = [
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_DEFAULT_REGION',
            'AWS_BUCKET',
        ];

        foreach ($required as $key) {
            if (empty(env($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Migra um arquivo individual
     */
    protected function migrarArquivo(DemandaArquivo $arquivo): void
    {
        // Verificar se o arquivo existe localmente
        if (!Storage::disk('public')->exists($arquivo->caminho)) {
            throw new \Exception("Arquivo não encontrado localmente: {$arquivo->caminho}");
        }

        // Construir novo caminho no S3
        $novoCaminho = $this->construirCaminhoS3($arquivo);

        // Verificar se já existe no S3
        if ($this->s3Service->exists($novoCaminho)) {
            if ($this->option('skip-existing')) {
                $this->pulados++;
                return;
            }

            if (!$this->option('force')) {
                throw new \Exception("Arquivo já existe no S3: {$novoCaminho}");
            }
        }

        // Se for dry-run, apenas simular
        if ($this->option('dry-run')) {
            $this->info("\n📄 [DRY-RUN] Migraria: {$arquivo->caminho} -> {$novoCaminho}");
            return;
        }

        // Ler arquivo local
        $conteudo = Storage::disk('public')->get($arquivo->caminho);
        $mimeType = Storage::disk('public')->mimeType($arquivo->caminho);

        // Criar arquivo temporário para upload
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        file_put_contents($tempPath, $conteudo);

        // Criar UploadedFile simulado
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempPath,
            $arquivo->nome_original,
            $mimeType,
            null,
            true
        );

        // Fazer upload para S3
        $pasta = $arquivo->demanda_id . '/arquivos';
        $resultado = $this->s3Service->putFileAsUploaded($uploadedFile, $pasta, $arquivo->nome_arquivo);

        // Limpar arquivo temporário
        fclose($tempFile);

        // Verificar se o upload foi bem-sucedido
        if (!$this->s3Service->exists($resultado['caminho'])) {
            throw new \Exception("Falha ao verificar arquivo no S3 após upload");
        }

        // Salvar caminho antigo antes de atualizar
        $caminhoAntigo = $arquivo->caminho;
        $caminhoNovo = $resultado['caminho'];

        // Atualizar caminho no banco de dados
        $arquivo->caminho = $caminhoNovo;
        $arquivo->save();

        // Log de sucesso
        Log::info('Arquivo migrado para S3', [
            'arquivo_id' => $arquivo->id,
            'demanda_id' => $arquivo->demanda_id,
            'caminho_antigo' => $caminhoAntigo,
            'caminho_novo' => $caminhoNovo,
        ]);
    }

    /**
     * Constrói o caminho no S3 baseado no arquivo
     */
    protected function construirCaminhoS3(DemandaArquivo $arquivo): string
    {
        $basePath = env('S3_PATH', 'gestor/demandas');
        $basePath = trim($basePath, '/');

        return "{$basePath}/{$arquivo->demanda_id}/arquivos/{$arquivo->nome_arquivo}";
    }

    /**
     * Exibe resumo da migração
     */
    protected function exibirResumo(): void
    {
        $this->newLine();
        $this->info('📊 Resumo da Migração:');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de arquivos', $this->total],
                ['✅ Migrados com sucesso', $this->sucesso],
                ['⏭️  Pulados', $this->pulados],
                ['❌ Erros', $this->erro],
            ]
        );

        if ($this->option('dry-run')) {
            $this->warn('⚠️  Modo DRY-RUN: Nenhuma alteração foi feita.');
        }
    }

    /**
     * Salva log de erros em arquivo
     */
    protected function salvarLogErros(): void
    {
        $logPath = storage_path('logs/migracao-s3-erros-' . date('Y-m-d-His') . '.json');
        file_put_contents($logPath, json_encode($this->erros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->warn("⚠️  Log de erros salvo em: {$logPath}");
    }
}
