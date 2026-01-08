<?php

namespace App\Filament\Resources\DemandaResource\RelationManagers;

use App\Http\Services\S3Service;
use App\Models\DemandaArquivo;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class ArquivosRelationManager extends RelationManager
{
    protected static string $relationship = 'arquivos';

    protected static ?string $title = 'Arquivos Anexados';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('arquivo_temp')
                    ->label('Arquivo')
                    ->required()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                        'image/vnd.adobe.photoshop',
                        'video/mp4',
                    ])
                    ->maxSize(10240) // 10MB
                    ->disk('public')
                    ->directory('temp')
                    ->visibility('public')
                    ->storeFileNamesIn('nome_original_temp')
                    ->dehydrated(false),
            ]);
    }

    public function table(Table $table): Table
    {
        /** @var User $user */
        $user = Auth::user();
        $demanda = $this->getOwnerRecord();
        
        // Garantir que o status está carregado
        if (!$demanda->relationLoaded('status')) {
            $demanda->load('status');
        }
        
        $statusBloqueados = ['Concluído', 'Homologada', 'Publicada', 'Cancelada'];
        
        // Permitir anexar arquivos se:
        // 1. Status for "Rascunho" (qualquer usuário pode anexar)
        // 2. OU se não estiver na lista de bloqueados E não for usuário comum
        $podeAnexarArquivo = $demanda->status && (
            $demanda->status->nome === 'Rascunho' 
            || (!in_array($demanda->status->nome, $statusBloqueados) && !$user->isUsuario())
        );
        
        // Analista pode deletar arquivos se o status for "Solicitada" (assim como admin e gestor)
        // Usuário comum pode deletar arquivos se o status for "Rascunho"
        $podeDeletarArquivo = $demanda->status && (
            (!$user->isUsuario() && $demanda->status->nome === 'Solicitada')
            || ($user->isUsuario() && $demanda->status->nome === 'Rascunho')
        );

        return $table
            ->recordTitleAttribute('nome_original')
            ->columns([
                Tables\Columns\TextColumn::make('nome_original')
                    ->label('Nome do Arquivo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn($state) => strtoupper($state))
                    ->color('secondary'),
                Tables\Columns\TextColumn::make('tamanho')
                    ->label('Tamanho')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'N/A';
                        $unidades = ['B', 'KB', 'MB', 'GB'];
                        $tamanho = $state;
                        $unidade = 0;
                        while ($tamanho >= 1024 && $unidade < count($unidades) - 1) {
                            $tamanho /= 1024;
                            $unidade++;
                        }
                        return round($tamanho, 2) . ' ' . $unidades[$unidade];
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Upload')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Enviar Arquivo')
                    ->visible(function () use ($user) {
                        $demanda = $this->getOwnerRecord();
                        
                        // Garantir que o status está carregado
                        if (!$demanda->relationLoaded('status')) {
                            $demanda->load('status');
                        }
                        
                        if (!$demanda->status) {
                            return false;
                        }
                        
                        $statusBloqueados = ['Concluído', 'Homologada', 'Publicada', 'Cancelada'];
                        
                        // Permitir anexar arquivos se:
                        // 1. Status for "Rascunho" (qualquer usuário pode anexar)
                        // 2. OU se não estiver na lista de bloqueados E não for usuário comum
                        return $demanda->status->nome === 'Rascunho' 
                            || (!in_array($demanda->status->nome, $statusBloqueados) && !$user->isUsuario());
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $caminho = $data['arquivo_temp'] ?? null;
                        if (!$caminho) {
                            throw new \Exception('Arquivo não foi enviado corretamente.');
                        }

                        // Obter o arquivo do storage temporário
                        $fullPath = storage_path('app/public/' . $caminho);
                        if (!file_exists($fullPath)) {
                            throw new \Exception('Arquivo temporário não encontrado.');
                        }

                        // Obter nome original do arquivo
                        $originalName = $data['nome_original_temp'] ?? basename($caminho);
                        if (empty($originalName)) {
                            $originalName = basename($caminho);
                        }
                        
                        // Garantir que temos um nome original válido
                        if (empty($originalName) || $originalName === '.' || $originalName === '..') {
                            // Tentar extrair do caminho
                            $pathInfo = pathinfo($caminho);
                            $originalName = $pathInfo['filename'] ?? 'arquivo_' . time();
                            if (isset($pathInfo['extension'])) {
                                $originalName .= '.' . $pathInfo['extension'];
                            } else {
                                $originalName .= '.pdf'; // fallback
                            }
                        }

                        // Obter informações do arquivo
                        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

                        // Criar UploadedFile a partir do arquivo temporário
                        $uploadedFile = new \Illuminate\Http\UploadedFile(
                            $fullPath,
                            $originalName,
                            $mimeType,
                            UPLOAD_ERR_OK,
                            false // não é test mode, é um arquivo real
                        );

                        try {
                            // Usar S3Service para fazer upload (como no DemandaController)
                            $s3Service = new S3Service();
                            $demanda = $this->getOwnerRecord();
                            $pasta = $demanda->id . '/arquivos';
                            $resultado = $s3Service->uploadFormData($uploadedFile, $pasta);

                            // Validar que o resultado contém todos os campos obrigatórios
                            $requiredFields = ['nome_original', 'nome', 'caminho', 'extensao', 'tamanho'];
                            foreach ($requiredFields as $field) {
                                if (!isset($resultado[$field]) || $resultado[$field] === null || $resultado[$field] === '') {
                                    throw new \Exception("Campo obrigatório '{$field}' não foi retornado pelo serviço de upload ou está vazio.");
                                }
                            }

                            // Garantir que nome_original não está vazio
                            if (empty($resultado['nome_original'])) {
                                $resultado['nome_original'] = $originalName;
                            }

                            // Limpar arquivo temporário
                            if (Storage::disk('public')->exists($caminho)) {
                                Storage::disk('public')->delete($caminho);
                            }

                            return [
                                'demanda_id' => $demanda->id,
                                'nome_original' => $resultado['nome_original'],
                                'nome_arquivo' => $resultado['nome'],
                                'caminho' => $resultado['caminho'],
                                'tipo' => $resultado['extensao'],
                                'tamanho' => $resultado['tamanho'],
                            ];
                        } catch (\Exception $e) {
                            // Limpar arquivo temporário em caso de erro
                            if (Storage::disk('public')->exists($caminho)) {
                                Storage::disk('public')->delete($caminho);
                            }
                            Log::error('Erro ao fazer upload de arquivo no RelationManager', [
                                'erro' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                                'demanda_id' => $this->getOwnerRecord()->id,
                                'arquivo' => $originalName,
                                'caminho_temp' => $caminho,
                            ]);
                            throw new \Exception('Erro ao fazer upload do arquivo: ' . $e->getMessage());
                        }
                    })
                    ->successNotificationTitle('Arquivo enviado com sucesso!'),
            ])
            ->actions([
                Tables\Actions\Action::make('visualizar')
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading(fn(DemandaArquivo $record) => $record->nome_original)
                    ->modalContent(function (DemandaArquivo $record) {
                        // Usar rota para servir o arquivo ao invés do link simbólico
                        $url = route('demandas.arquivos.view', $record);
                        return view('filament.components.file-preview', [
                            'url' => $url,
                            'tipo' => strtolower($record->tipo),
                            'nome' => $record->nome_original,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->extraModalFooterActions([
                        Tables\Actions\Action::make('download')
                            ->label('Baixar')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('gray')
                            ->action(function (DemandaArquivo $record) {
                                // Usar o método do modelo que funciona com S3 e local
                                return redirect($record->getDownloadUrl(5));
                            }),
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->visible(function () use ($user) {
                        $demanda = $this->getOwnerRecord();
                        
                        // Garantir que o status está carregado
                        if (!$demanda->relationLoaded('status')) {
                            $demanda->load('status');
                        }
                        
                        if (!$demanda->status) {
                            return false;
                        }
                        
                        // Analista pode deletar arquivos se o status for "Solicitada" (assim como admin e gestor)
                        // Usuário comum pode deletar arquivos se o status for "Rascunho"
                        return (!$user->isUsuario() && $demanda->status->nome === 'Solicitada')
                            || ($user->isUsuario() && $demanda->status->nome === 'Rascunho');
                    })
                    ->successNotificationTitle('Arquivo excluído com sucesso!')
                    ->before(function (DemandaArquivo $record) {
                        // Usar o método do modelo que funciona com S3 e local
                        $record->deleteFile();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(function () use ($user) {
                            $demanda = $this->getOwnerRecord();
                            
                            // Garantir que o status está carregado
                            if (!$demanda->relationLoaded('status')) {
                                $demanda->load('status');
                            }
                            
                            if (!$demanda->status) {
                                return false;
                            }
                            
                            // Analista pode deletar arquivos se o status for "Solicitada" (assim como admin e gestor)
                            // Usuário comum pode deletar arquivos se o status for "Rascunho"
                            return (!$user->isUsuario() && $demanda->status->nome === 'Solicitada')
                                || ($user->isUsuario() && $demanda->status->nome === 'Rascunho');
                        })
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                // Usar o método do modelo que funciona com S3 e local
                                $record->deleteFile();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('Nenhum arquivo anexado')
            ->emptyStateDescription('Clique em "Enviar Arquivo" para adicionar arquivos a esta demanda.')
            ->emptyStateIcon('heroicon-o-document');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        /** @var User $user */
        $user = Auth::user();

        // Verificar se o usuário tem acesso à demanda
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if (!in_array($ownerRecord->projeto_id, $projetosIds->toArray())) {
                return false;
            }

            // Usuário comum só pode ver arquivos de suas próprias demandas
            // Analista pode ver arquivos de todas as demandas dos projetos que tem acesso
            if ($user->isUsuario() && $ownerRecord->solicitante_id !== $user->id) {
                return false;
            }
        }

        return true;
    }
}
