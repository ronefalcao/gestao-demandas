<?php

namespace App\Filament\Resources\DemandaResource\RelationManagers;

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
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'video/mp4'])
                    ->maxSize(10240) // 10MB
                    ->disk('public')
                    ->directory('demandas')
                    ->visibility('public')
                    ->storeFileNamesIn('nome_original_temp')
                    ->dehydrated(false),
            ]);
    }

    public function table(Table $table): Table
    {
        /** @var User $user */
        $user = Auth::user();
        $demanda = $this->getOwnerRecord()->load('status');
        $statusBloqueados = ['Concluído', 'Homologada', 'Publicada', 'Cancelada'];
        $podeAnexarArquivo = !in_array($demanda->status->nome, $statusBloqueados);
        $podeDeletarArquivo = !$user->isUsuario() && $demanda->status->nome === 'Solicitada';

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
                    ->visible($podeAnexarArquivo)
                    ->mutateFormDataUsing(function (array $data): array {
                        $caminho = $data['arquivo_temp'] ?? null;
                        if (!$caminho) {
                            return $data;
                        }

                        $originalName = $data['nome_original_temp'] ?? basename($caminho);
                        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

                        // Renomear arquivo para nome único
                        $fileName = uniqid() . '_' . time() . '.' . $extension;
                        $newPath = 'demandas/' . $fileName;

                        // Mover arquivo para o novo nome
                        if (Storage::disk('public')->exists($caminho)) {
                            Storage::disk('public')->move($caminho, $newPath);
                        }

                        return [
                            'demanda_id' => $this->getOwnerRecord()->id,
                            'nome_original' => $originalName,
                            'nome_arquivo' => $fileName,
                            'caminho' => $newPath,
                            'tipo' => $extension,
                            'tamanho' => Storage::disk('public')->exists($newPath) ? Storage::disk('public')->size($newPath) : 0,
                        ];
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
                                $file = Storage::disk('public')->get($record->caminho);
                                $tempPath = sys_get_temp_dir() . '/' . $record->nome_original;
                                file_put_contents($tempPath, $file);
                                return Response::download($tempPath, $record->nome_original)->deleteFileAfterSend(true);
                            }),
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->visible($podeDeletarArquivo)
                    ->successNotificationTitle('Arquivo excluído com sucesso!')
                    ->before(function (DemandaArquivo $record) {
                        Storage::disk('public')->delete($record->caminho);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible($podeDeletarArquivo)
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                Storage::disk('public')->delete($record->caminho);
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
            if ($user->isUsuario() && $ownerRecord->solicitante_id !== $user->id) {
                return false;
            }
        }

        return true;
    }
}