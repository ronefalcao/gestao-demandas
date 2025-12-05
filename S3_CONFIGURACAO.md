# Configuração do S3 para Armazenamento de Arquivos

Este documento descreve como configurar o Amazon S3 para armazenar os arquivos anexados às demandas.

## Dependências

O projeto utiliza o pacote `league/flysystem-aws-s3-v3` para integração com S3. A dependência já foi adicionada ao `composer.json`.

Para instalar as dependências:

```bash
composer install
```

## Configuração do .env

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# Configurações AWS S3
AWS_ACCESS_KEY_ID=your_access_key_id
AWS_SECRET_ACCESS_KEY=your_secret_access_key
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=your_bucket_name
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Caminho base no S3 (sem barra no final)
S3_PATH=gestor/demandas
```

### Explicação das Variáveis

- **AWS_ACCESS_KEY_ID**: Chave de acesso da AWS
- **AWS_SECRET_ACCESS_KEY**: Chave secreta da AWS
- **AWS_DEFAULT_REGION**: Região do bucket S3 (ex: `sa-east-1` para São Paulo)
- **AWS_BUCKET**: Nome do bucket S3
- **AWS_URL**: URL pública do bucket (opcional, deixe vazio se não configurado)
- **AWS_ENDPOINT**: Endpoint customizado (opcional, deixe vazio se usar AWS padrão)
- **AWS_USE_PATH_STYLE_ENDPOINT**: Use `true` se o bucket não suporta subdomínios virtuais
- **S3_PATH**: Caminho base onde os arquivos serão armazenados (ex: `gestor/demandas`)

## Estrutura de Pastas no S3

Os arquivos serão armazenados no S3 seguindo a seguinte estrutura:

```
{S3_PATH}/{demanda_id}/arquivos/{nome_arquivo}
```

Exemplo:

- Se `S3_PATH=gestor/demandas`
- E a demanda tem `id=1`
- O arquivo será salvo em: `gestor/demandas/1/arquivos/nome_arquivo_20241120_123456.pdf`

## Funcionalidades Implementadas

### S3Service

O serviço `App\Http\Services\S3Service` fornece os seguintes métodos:

- `uploadFormData(UploadedFile $arquivo, string $pasta)`: Faz upload de um arquivo
- `putFileAsUploaded(UploadedFile $arquivo, string $pasta, string $nomeArquivo)`: Faz upload com nome específico
- `delete(string $path)`: Deleta um arquivo do S3
- `deleteMultiple(array $paths)`: Deleta múltiplos arquivos
- `temporaryUrl(string $path, \DateTimeInterface $expiry, array $options = [])`: Gera URL temporária assinada
- `exists(string $path)`: Verifica se um arquivo existe
- `getUrl(string $path)`: Retorna URL pública (se configurado)

### Integração com Filament

O `ArquivosRelationManager` foi atualizado para:

1. Fazer upload de arquivos diretamente para o S3
2. Gerar URLs temporárias para visualização e download
3. Deletar arquivos do S3 quando removidos

### Integração com Controllers

O `DemandaController` foi atualizado para:

1. Fazer upload de arquivos via formulário web para o S3
2. Gerar URLs temporárias para download e visualização
3. Deletar arquivos do S3 quando removidos

## URLs Temporárias

As URLs temporárias são geradas com validade de:

- **60 minutos** para visualização
- **5 minutos** para download

Isso garante segurança ao acessar os arquivos, já que as URLs expiram automaticamente.

## Migração de Arquivos Existentes

Se você já possui arquivos armazenados localmente e deseja migrá-los para o S3, será necessário criar um script de migração. Os arquivos antigos estão no disco `public` em `storage/app/public/demandas/`.

## Testes

Após configurar o `.env`, teste o upload de arquivos:

1. Acesse uma demanda no sistema
2. Clique em "Enviar Arquivo" na seção de arquivos
3. Selecione um arquivo
4. Verifique se o arquivo foi enviado corretamente para o S3

## Troubleshooting

### Erro: "Class 'League\Flysystem\AwsS3v3\AwsS3V3Adapter' not found"

Execute:

```bash
composer require league/flysystem-aws-s3-v3
```

### Erro: "Access Denied" ao fazer upload

Verifique:

1. As credenciais AWS estão corretas
2. O bucket existe e está acessível
3. As permissões do IAM permitem PutObject e GetObject

### Arquivos não aparecem

Verifique:

1. O caminho `S3_PATH` está configurado corretamente
2. As URLs temporárias estão sendo geradas (verifique os logs)
3. O bucket está na região correta

## Notas Importantes

- Os arquivos são armazenados com nomes únicos (timestamp + nome sanitizado)
- O caminho completo no S3 é construído automaticamente pelo `S3Service`
- As URLs temporárias são assinadas e seguras
- Não é necessário configurar CORS se usar apenas URLs temporárias


