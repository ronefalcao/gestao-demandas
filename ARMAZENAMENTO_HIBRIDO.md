# Armazenamento Híbrido - Local e S3

Este documento descreve a implementação do sistema de armazenamento híbrido, onde arquivos antigos permanecem no armazenamento local e novos arquivos são salvos no S3.

## 🎯 Estratégia

### Arquivos Existentes (Locais)

- **Mantidos no armazenamento local**: `storage/app/public/demandas/`
- **Caminho no banco**: `demandas/nome_arquivo.ext`
- **Acesso**: Via link simbólico `public/storage`

### Arquivos Novos (S3)

- **Salvos no S3**: Bucket configurado
- **Caminho no banco**: `{S3_PATH}/{demanda_id}/arquivos/{nome_arquivo}`
- **Exemplo**: `gestor/demandas/1/arquivos/documento_20241120_123456.pdf`
- **Acesso**: Via URLs temporárias assinadas

## 🔍 Detecção Automática

O sistema detecta automaticamente se um arquivo está no S3 ou localmente baseado no caminho:

### Critério de Detecção

```php
// Arquivo está no S3 se o caminho começar com S3_PATH
$s3Path = env('S3_PATH', 'gestor/demandas');
$isS3 = strpos($caminho, $s3Path . '/') === 0;
```

### Exemplos

| Caminho                                  | Tipo  | Detecção     |
| ---------------------------------------- | ----- | ------------ |
| `demandas/arquivo.pdf`                   | Local | ✅ Detectado |
| `gestor/demandas/1/arquivos/arquivo.pdf` | S3    | ✅ Detectado |
| `gestor/demandas/2/arquivos/doc.pdf`     | S3    | ✅ Detectado |

## 📦 Modelo DemandaArquivo

O modelo `DemandaArquivo` possui métodos que funcionam automaticamente com ambos os tipos:

### Métodos Disponíveis

#### `isS3(): bool`

Verifica se o arquivo está no S3.

```php
if ($arquivo->isS3()) {
    // Arquivo está no S3
}
```

#### `isLocal(): bool`

Verifica se o arquivo está localmente.

```php
if ($arquivo->isLocal()) {
    // Arquivo está local
}
```

#### `getUrlAttribute()`

Retorna a URL do arquivo (S3 ou local).

```php
$url = $arquivo->url; // Funciona para ambos
```

#### `getDownloadUrl(int $minutes = 5): string`

Retorna URL para download.

```php
$url = $arquivo->getDownloadUrl(5); // 5 minutos de validade
```

#### `getViewUrl(int $minutes = 60): string`

Retorna URL para visualização.

```php
$url = $arquivo->getViewUrl(60); // 60 minutos de validade
```

#### `exists(): bool`

Verifica se o arquivo existe.

```php
if ($arquivo->exists()) {
    // Arquivo existe
}
```

#### `deleteFile(): bool`

Deleta o arquivo (S3 ou local).

```php
$arquivo->deleteFile(); // Funciona para ambos
```

#### `getContent(): string`

Retorna o conteúdo do arquivo.

```php
$conteudo = $arquivo->getContent();
```

#### `getMimeType(): string`

Retorna o MIME type do arquivo.

```php
$mimeType = $arquivo->getMimeType();
```

## 🔄 Comportamento do Sistema

### Upload de Arquivos

**Novos arquivos sempre vão para S3:**

- Upload via Filament → S3
- Upload via Controller → S3
- Caminho salvo: `{S3_PATH}/{demanda_id}/arquivos/{nome_arquivo}`

### Download de Arquivos

**Detecção automática:**

- Se S3 → Gera URL temporária assinada
- Se Local → Faz download direto do storage

### Visualização de Arquivos

**Detecção automática:**

- Se S3 → Gera URL temporária assinada
- Se Local → Serve arquivo diretamente

### Exclusão de Arquivos

**Detecção automática:**

- Se S3 → Deleta do S3
- Se Local → Deleta do storage local

## 📊 Consultas Úteis

### Contar arquivos por tipo

```php
// Total de arquivos
$total = DemandaArquivo::count();

// Arquivos no S3
$s3Path = env('S3_PATH', 'gestor/demandas');
$s3Path = trim($s3Path, '/');
$noS3 = DemandaArquivo::where('caminho', 'like', $s3Path . '/%')->count();

// Arquivos locais
$local = DemandaArquivo::where('caminho', 'like', 'demandas/%')->count();
```

### Listar arquivos por tipo

```php
// Arquivos no S3
$arquivosS3 = DemandaArquivo::where('caminho', 'like', $s3Path . '/%')->get();

// Arquivos locais
$arquivosLocal = DemandaArquivo::where('caminho', 'like', 'demandas/%')->get();
```

## 🔧 Migração Gradual (Opcional)

Se desejar migrar arquivos locais para S3 gradualmente:

### Migrar arquivos de uma demanda específica

```bash
php artisan arquivos:migrate-to-s3 --demanda-id=1
```

### Migrar todos os arquivos locais

```bash
php artisan arquivos:migrate-to-s3
```

**Nota**: A migração é opcional. O sistema funciona perfeitamente com ambos os tipos.

## ✅ Vantagens da Abordagem Híbrida

1. **Sem Interrupção**: Sistema continua funcionando normalmente
2. **Migração Gradual**: Pode migrar quando quiser
3. **Baixo Risco**: Arquivos antigos permanecem seguros
4. **Flexibilidade**: Pode manter arquivos locais se necessário
5. **Transparente**: Usuários não percebem diferença

## 🎨 Interface do Usuário

Na interface do Filament, todos os arquivos aparecem da mesma forma, independente de onde estão armazenados:

- ✅ Visualização funciona para ambos
- ✅ Download funciona para ambos
- ✅ Exclusão funciona para ambos
- ✅ Indicadores visuais (opcional) podem ser adicionados

## 🔐 Segurança

### Arquivos Locais

- Acesso via rotas protegidas
- Validação de permissões
- Link simbólico para acesso público

### Arquivos S3

- URLs temporárias assinadas
- Expiração automática
- Validação de permissões antes de gerar URL

## 📝 Notas Importantes

1. **Novos arquivos sempre vão para S3**: Não há opção de escolher
2. **Arquivos antigos permanecem locais**: Até serem migrados (se desejar)
3. **Detecção automática**: Sistema detecta automaticamente o tipo
4. **Compatibilidade total**: Código funciona com ambos os tipos
5. **Migração opcional**: Pode migrar quando quiser ou manter híbrido

## 🚀 Próximos Passos

1. ✅ Sistema já está configurado para armazenamento híbrido
2. ✅ Novos arquivos vão automaticamente para S3
3. ✅ Arquivos antigos continuam funcionando localmente
4. ⏳ Migração gradual (opcional) quando desejar

## 🔍 Verificação

Para verificar se está funcionando:

```bash
# Verificar arquivos locais
php artisan tinker
>>> App\Models\DemandaArquivo::where('caminho', 'like', 'demandas/%')->count()

# Verificar arquivos no S3
>>> $s3Path = env('S3_PATH', 'gestor/demandas');
>>> App\Models\DemandaArquivo::where('caminho', 'like', trim($s3Path, '/') . '/%')->count()
```


