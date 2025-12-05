# Plano de Migração de Arquivos para S3

Este documento descreve o plano completo para migrar os arquivos de demandas do armazenamento local para o Amazon S3.

## 📋 Situação Atual

### Armazenamento Local

- **Localização**: `storage/app/public/demandas/`
- **Estrutura**: Arquivos armazenados diretamente na pasta `demandas/`
- **Formato do caminho no banco**: `demandas/nome_arquivo.ext`
- **Acesso**: Via link simbólico em `public/storage`

### Armazenamento S3 (Novo)

- **Localização**: Bucket S3 configurado
- **Estrutura**: `{S3_PATH}/{demanda_id}/arquivos/{nome_arquivo}`
- **Exemplo**: `gestor/demandas/1/arquivos/documento_20241120_123456.pdf`
- **Acesso**: Via URLs temporárias assinadas

## 🎯 Objetivos da Migração

1. Migrar todos os arquivos existentes do armazenamento local para o S3
2. Atualizar os registros no banco de dados com os novos caminhos
3. Manter a integridade dos dados
4. Permitir rollback se necessário
5. Validar que todos os arquivos foram migrados corretamente

## 🔧 Comando de Migração

Foi criado o comando Artisan `arquivos:migrate-to-s3` com as seguintes opções:

### Uso Básico

```bash
php artisan arquivos:migrate-to-s3
```

### Opções Disponíveis

#### `--dry-run`

Executa a migração em modo simulação, sem fazer alterações reais.

```bash
php artisan arquivos:migrate-to-s3 --dry-run
```

**Uso**: Recomendado para testar antes da migração real.

#### `--force`

Força a migração mesmo se o arquivo já existir no S3 (sobrescreve).

```bash
php artisan arquivos:migrate-to-s3 --force
```

#### `--demanda-id=ID`

Migra apenas os arquivos de uma demanda específica.

```bash
php artisan arquivos:migrate-to-s3 --demanda-id=1
```

**Uso**: Útil para testar com uma demanda específica antes de migrar tudo.

#### `--limit=N`

Limita o número de arquivos a migrar.

```bash
php artisan arquivos:migrate-to-s3 --limit=10
```

**Uso**: Útil para testar com uma amostra pequena.

#### `--skip-existing`

Pula arquivos que já estão no S3.

```bash
php artisan arquivos:migrate-to-s3 --skip-existing
```

**Uso**: Útil para continuar uma migração interrompida.

## 📝 Plano de Execução

### Fase 1: Preparação

1. **Backup do Banco de Dados**

   ```bash
   pg_dump -U postgres demandas > backup_pre_migracao_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Backup dos Arquivos Locais**

   ```bash
   tar -czf backup_arquivos_$(date +%Y%m%d_%H%M%S).tar.gz storage/app/public/demandas/
   ```

3. **Verificar Configuração do S3**

   - Verificar variáveis de ambiente no `.env`
   - Testar conexão com S3
   - Verificar permissões do bucket

4. **Contar Arquivos**
   ```bash
   php artisan tinker
   >>> App\Models\DemandaArquivo::count()
   ```

### Fase 2: Teste com Amostra

1. **Teste com uma demanda específica (dry-run)**

   ```bash
   php artisan arquivos:migrate-to-s3 --dry-run --demanda-id=1
   ```

2. **Teste real com uma demanda**

   ```bash
   php artisan arquivos:migrate-to-s3 --demanda-id=1
   ```

3. **Validar resultado**

   - Verificar se o arquivo está no S3
   - Verificar se o caminho foi atualizado no banco
   - Testar download/visualização do arquivo

4. **Teste com amostra maior**
   ```bash
   php artisan arquivos:migrate-to-s3 --limit=10
   ```

### Fase 3: Migração Completa

1. **Executar migração completa**

   ```bash
   php artisan arquivos:migrate-to-s3
   ```

2. **Monitorar progresso**

   - O comando exibe barra de progresso
   - Verificar logs em `storage/logs/laravel.log`
   - Verificar log de erros se houver

3. **Validar migração**
   - Verificar resumo exibido pelo comando
   - Contar arquivos no S3
   - Comparar com total no banco

### Fase 4: Validação e Limpeza

1. **Validar Integridade**

   ```bash
   php artisan tinker
   >>> $total = App\Models\DemandaArquivo::count();
   >>> $comErro = App\Models\DemandaArquivo::where('caminho', 'like', 'demandas/%')->count();
   >>> echo "Total: $total, Ainda local: $comErro";
   ```

2. **Verificar Arquivos no S3**

   - Usar console AWS ou CLI para verificar
   - Comparar quantidade com banco de dados

3. **Testar Funcionalidades**

   - Testar download de arquivos
   - Testar visualização de arquivos
   - Verificar se URLs temporárias funcionam

4. **Limpeza (Opcional)**
   - Após validação completa, pode-se remover arquivos locais
   - **ATENÇÃO**: Fazer backup antes!

## 🔄 Rollback

Se necessário fazer rollback:

1. **Restaurar Backup do Banco**

   ```bash
   psql -U postgres demandas < backup_pre_migracao_YYYYMMDD_HHMMSS.sql
   ```

2. **Restaurar Arquivos Locais**

   ```bash
   tar -xzf backup_arquivos_YYYYMMDD_HHMMSS.tar.gz
   ```

3. **Reverter Código**
   - Reverter alterações no código se necessário
   - Manter código compatível com ambos os sistemas durante transição

## ⚠️ Tratamento de Erros

### Erros Comuns

1. **Arquivo não encontrado localmente**

   - **Causa**: Arquivo foi deletado manualmente
   - **Ação**: Registrar no log de erros, continuar migração

2. **Arquivo já existe no S3**

   - **Causa**: Migração parcial anterior
   - **Ação**: Usar `--skip-existing` ou `--force`

3. **Falha de conexão com S3**

   - **Causa**: Credenciais incorretas ou rede
   - **Ação**: Verificar configuração, tentar novamente

4. **Timeout durante upload**
   - **Causa**: Arquivo muito grande ou conexão lenta
   - **Ação**: Aumentar timeout, migrar em lotes menores

### Log de Erros

O comando gera um arquivo JSON com todos os erros encontrados:

```
storage/logs/migracao-s3-erros-YYYY-MM-DD-HHMMSS.json
```

Formato:

```json
[
  {
    "arquivo_id": 1,
    "demanda_id": 1,
    "caminho": "demandas/arquivo.pdf",
    "erro": "Arquivo não encontrado localmente"
  }
]
```

## 📊 Monitoramento

### Durante a Migração

- **Barra de progresso**: Mostra progresso em tempo real
- **Resumo final**: Exibe estatísticas da migração
- **Logs**: Registrados em `storage/logs/laravel.log`

### Após a Migração

1. **Verificar Estatísticas**

   ```sql
   SELECT
       COUNT(*) as total,
       COUNT(CASE WHEN caminho LIKE 'gestor/demandas/%' THEN 1 END) as no_s3,
       COUNT(CASE WHEN caminho LIKE 'demandas/%' THEN 1 END) as local
   FROM demanda_arquivos;
   ```

2. **Verificar Tamanho Total**
   ```sql
   SELECT
       SUM(tamanho) as tamanho_total_bytes,
       ROUND(SUM(tamanho) / 1024.0 / 1024.0, 2) as tamanho_total_mb
   FROM demanda_arquivos;
   ```

## 🔐 Segurança

1. **Credenciais AWS**

   - Nunca commitar credenciais no código
   - Usar variáveis de ambiente
   - Rotacionar credenciais periodicamente

2. **Permissões S3**

   - Usar IAM com permissões mínimas necessárias
   - Habilitar versionamento do bucket (opcional)
   - Habilitar logging do bucket (recomendado)

3. **Backup**
   - Sempre fazer backup antes da migração
   - Manter backups por período determinado

## 📅 Cronograma Sugerido

### Dia 1: Preparação

- [ ] Backup do banco de dados
- [ ] Backup dos arquivos locais
- [ ] Configurar e testar S3
- [ ] Teste com uma demanda (dry-run)

### Dia 2: Testes

- [ ] Teste real com uma demanda
- [ ] Teste com amostra de 10 arquivos
- [ ] Validar resultados
- [ ] Ajustar se necessário

### Dia 3: Migração

- [ ] Executar migração completa
- [ ] Monitorar progresso
- [ ] Validar resultados
- [ ] Corrigir erros se houver

### Dia 4: Validação

- [ ] Validar integridade
- [ ] Testar funcionalidades
- [ ] Documentar resultados
- [ ] Planejar limpeza (se aplicável)

## ✅ Checklist de Migração

### Antes da Migração

- [ ] Backup do banco de dados realizado
- [ ] Backup dos arquivos locais realizado
- [ ] S3 configurado e testado
- [ ] Teste com amostra realizado com sucesso
- [ ] Equipe notificada sobre a migração
- [ ] Janela de manutenção agendada (se necessário)

### Durante a Migração

- [ ] Executar comando de migração
- [ ] Monitorar progresso
- [ ] Verificar logs
- [ ] Anotar erros encontrados

### Após a Migração

- [ ] Validar total de arquivos migrados
- [ ] Testar download de arquivos
- [ ] Testar visualização de arquivos
- [ ] Verificar se não há erros críticos
- [ ] Documentar resultados
- [ ] Notificar equipe sobre conclusão

## 🆘 Suporte

Em caso de problemas:

1. Verificar logs em `storage/logs/laravel.log`
2. Verificar log de erros da migração
3. Verificar configuração do S3
4. Consultar documentação AWS
5. Revisar este plano de migração

## 📝 Notas Importantes

1. **Não deletar arquivos locais imediatamente**: Manter por período de segurança
2. **Monitorar custos S3**: Verificar custos de armazenamento e transferência
3. **Performance**: Migração pode demorar dependendo da quantidade de arquivos
4. **Manutenção**: Considerar janela de manutenção para migração completa
5. **Comunicação**: Notificar usuários sobre possível indisponibilidade temporária


