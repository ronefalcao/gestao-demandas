# Comandos de Migração S3 - Guia Rápido

## 🚀 Comandos Principais

### 1. Teste Simulado (Dry-Run)

Executa sem fazer alterações, apenas mostra o que seria migrado:

```bash
php artisan arquivos:migrate-to-s3 --dry-run
```

### 2. Teste com Uma Demanda

Migra apenas os arquivos de uma demanda específica:

```bash
php artisan arquivos:migrate-to-s3 --demanda-id=1
```

### 3. Teste com Amostra

Migra apenas os primeiros 10 arquivos:

```bash
php artisan arquivos:migrate-to-s3 --limit=10
```

### 4. Migração Completa

Migra todos os arquivos:

```bash
php artisan arquivos:migrate-to-s3
```

### 5. Continuar Migração Interrompida

Pula arquivos que já estão no S3:

```bash
php artisan arquivos:migrate-to-s3 --skip-existing
```

### 6. Forçar Migração (Sobrescrever)

Força a migração mesmo se o arquivo já existir:

```bash
php artisan arquivos:migrate-to-s3 --force
```

## 📊 Verificar Status da Migração

### Contar arquivos no banco

```bash
php artisan tinker
>>> App\Models\DemandaArquivo::count()
```

### Contar arquivos ainda locais

```bash
php artisan tinker
>>> App\Models\DemandaArquivo::where('caminho', 'like', 'demandas/%')->count()
```

### Contar arquivos já no S3

```bash
php artisan tinker
>>> App\Models\DemandaArquivo::where('caminho', 'like', env('S3_PATH') . '/%')->count()
```

## 🔍 Verificar Logs

### Log do Laravel

```bash
tail -f storage/logs/laravel.log
```

### Log de erros da migração

Os erros são salvos em:

```
storage/logs/migracao-s3-erros-YYYY-MM-DD-HHMMSS.json
```

## ⚠️ Antes de Executar

1. **Fazer backup do banco de dados**
2. **Fazer backup dos arquivos locais**
3. **Verificar configuração do S3 no .env**
4. **Testar com --dry-run primeiro**

## 📝 Exemplo de Fluxo Completo

```bash
# 1. Backup
pg_dump -U postgres demandas > backup.sql
tar -czf backup_arquivos.tar.gz storage/app/public/demandas/

# 2. Teste simulado
php artisan arquivos:migrate-to-s3 --dry-run

# 3. Teste com uma demanda
php artisan arquivos:migrate-to-s3 --demanda-id=1

# 4. Teste com amostra
php artisan arquivos:migrate-to-s3 --limit=10

# 5. Migração completa
php artisan arquivos:migrate-to-s3

# 6. Verificar resultado
php artisan tinker
>>> App\Models\DemandaArquivo::where('caminho', 'like', 'gestor/demandas/%')->count()
```


