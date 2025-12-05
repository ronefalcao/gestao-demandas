# Perfis de Acesso - Sistema de Gestão de Demandas

Este documento descreve os perfis de acesso do sistema e as regras de acesso para cada feature.

## Perfis Disponíveis

O sistema possui 5 perfis de usuário:

1. **Administrador** (`administrador`)
2. **Gestor** (`gestor`)
3. **Analista** (`analista`)
4. **Planejador** (`planejador`)
5. **Usuário** (`usuario`)

---

## 1. Administrador

### Acesso ao Painel

✅ **Tem acesso completo ao painel Filament**

### Recursos e Funcionalidades

#### ✅ **Usuários** (`UserResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**: Acesso total ao gerenciamento de usuários

#### ✅ **Clientes** (`ClienteResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**: Acesso total ao gerenciamento de clientes

#### ✅ **Projetos** (`ProjetoResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**:
  - Vê todos os projetos ativos
  - Pode gerenciar projetos de qualquer usuário

#### ✅ **Módulos** (`ModuloResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**:
  - Vê todos os módulos de todos os projetos
  - Pode criar módulos em qualquer projeto

#### ✅ **Status** (`StatusResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**: Acesso exclusivo ao gerenciamento de status

#### ✅ **Demandas** (`DemandaResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim (todas as demandas, independente do status)
- **Excluir**: Sim
- **Observações**:
  - Vê todas as demandas
  - Pode editar demandas em qualquer status
  - Pode alterar qualquer campo da demanda
  - Pode excluir qualquer demanda
  - Acesso a ações em massa (bulk actions)

#### ✅ **Sprints** (`SprintResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**: Acesso total ao gerenciamento de sprints

#### ✅ **Features** (`FeatureResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**:
  - Vê todas as features de todos os projetos
  - Pode criar features em qualquer projeto

#### ✅ **Itens** (via `ItensRelationManager`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**: Acesso total aos itens das features

### Widgets

- ✅ **PlanejamentoGanttWidget**: Visível
- ✅ **PlanejamentoTimelineWidget**: Visível
- ✅ Todos os outros widgets: Visíveis

### Regras Especiais

- Pode gerenciar o sistema completamente (`canManageSystem()` retorna `true`)
- Não possui restrições de acesso por projeto
- Pode visualizar todas as demandas (`canViewAllDemandas()` retorna `true`)

---

## 2. Gestor

### Acesso ao Painel

✅ **Tem acesso ao painel Filament**

### Recursos e Funcionalidades

#### ✅ **Usuários** (`UserResource`)

- **Visualizar**: Sim (somente visualização)
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não
- **Observações**: Pode apenas visualizar a lista de usuários

#### ✅ **Clientes** (`ClienteResource`)

- **Visualizar**: Sim
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não
- **Observações**:
  - Pode visualizar clientes
  - Pode ver demandas relacionadas aos clientes (via RelationManager)
  - Não pode criar, editar ou excluir clientes

#### ✅ **Projetos** (`ProjetoResource`)

- **Visualizar**: Sim
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não
- **Observações**: Apenas visualização de projetos

#### ✅ **Módulos** (`ModuloResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**: Pode gerenciar módulos

#### ✅ **Status** (`StatusResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não
- **Observações**: Sem acesso ao gerenciamento de status

#### ✅ **Demandas** (`DemandaResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Não (regras específicas - ver abaixo)
- **Excluir**: Não
- **Observações**:
  - Pode visualizar e criar demandas
  - **NÃO pode editar demandas** (mesmo que seja o solicitante) - Nova Regra: pode editar desde que o status esteja em rascunho;
  - Pode ver todas as demandas

#### ✅ **Sprints** (`SprintResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não
- **Observações**: Sem acesso ao gerenciamento de sprints

#### ✅ **Features** (`FeatureResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**: Pode gerenciar features

#### ❌ **Itens** (via `ItensRelationManager`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não
- **Observações**: Sem acesso aos itens das features

### Widgets

- ✅ **PlanejamentoGanttWidget**: Visível
- ❌ **PlanejamentoTimelineWidget**: Não visível
- ✅ Outros widgets: Visíveis (conforme regras específicas)

### Regras Especiais

- Pode visualizar todas as demandas (`canViewAllDemandas()` retorna `true`)
- Não possui restrições de acesso por projeto (vê todos os projetos)
- Não pode gerenciar usuários, status ou sprints

---

## 3. Analista

### Acesso ao Painel

✅ **Tem acesso ao painel Filament**

### Recursos e Funcionalidades

#### ❌ **Usuários** (`UserResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Clientes** (`ClienteResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Projetos** (`ProjetoResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não
- **Observações**: Não tem acesso direto ao recurso, mas pode ver projetos através de demandas

#### ❌ **Módulos** (`ModuloResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Status** (`StatusResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ✅ **Demandas** (`DemandaResource`)

- **Visualizar**: Sim (apenas demandas dos projetos com acesso)
- **Criar**: Sim
- **Editar**: Sim (apenas demandas dos projetos com acesso)
- **Excluir**: Não
- **Observações**:
  - **Restrição por Projeto**: Só vê e edita demandas dos projetos aos quais tem acesso (relacionamento `projetos`)
  - Pode editar demandas em qualquer status (Solicitada ou posterior)
  - Pode criar novas demandas
  - Ao criar/editar, só vê projetos aos quais tem acesso
  - Pode alterar todos os campos da demanda (incluindo status, responsável, etc.)

#### ❌ **Sprints** (`SprintResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Features** (`FeatureResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Itens** (via `ItensRelationManager`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

### Widgets

- ❌ **PlanejamentoGanttWidget**: Não visível
- ❌ **PlanejamentoTimelineWidget**: Não visível
- ✅ Outros widgets: Visíveis (conforme regras específicas)

### Regras Especiais

- **Acesso restrito por projeto**: Só vê demandas dos projetos aos quais está vinculado
- Pode visualizar todas as demandas dos seus projetos (`canViewAllDemandas()` retorna `true`)
- Não pode excluir demandas
- Pode editar demandas mesmo após serem solicitadas (status >= 1)

---

## 4. Planejador

### Acesso ao Painel

✅ **Tem acesso ao painel Filament**

### Recursos e Funcionalidades

#### ❌ **Usuários** (`UserResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Clientes** (`ClienteResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ✅ **Projetos** (`ProjetoResource`)

- **Visualizar**: Sim (apenas projetos com acesso)
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não
- **Observações**:
  - **Restrição por Projeto**: Só vê projetos aos quais tem acesso
  - Apenas visualização

#### ✅ **Módulos** (`ModuloResource`)

- **Visualizar**: Sim (apenas módulos dos projetos com acesso)
- **Criar**: Sim (apenas em projetos com acesso)
- **Editar**: Sim (apenas módulos dos projetos com acesso)
- **Excluir**: Sim (apenas módulos dos projetos com acesso)
- **Observações**:
  - **Restrição por Projeto**: Só vê e gerencia módulos dos projetos aos quais tem acesso
  - Ao criar/editar, só vê projetos aos quais tem acesso

#### ❌ **Status** (`StatusResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Demandas** (`DemandaResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não
- **Observações**: **Sem acesso a demandas**

#### ✅ **Sprints** (`SprintResource`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**: Acesso total ao gerenciamento de sprints

#### ✅ **Features** (`FeatureResource`)

- **Visualizar**: Sim (apenas features dos projetos com acesso)
- **Criar**: Sim (apenas em projetos com acesso)
- **Editar**: Sim (apenas features dos projetos com acesso)
- **Excluir**: Sim (apenas features dos projetos com acesso)
- **Observações**:
  - **Restrição por Projeto**: Só vê e gerencia features dos projetos aos quais tem acesso
  - Ao criar/editar, só vê projetos aos quais tem acesso
  - Pode criar módulos inline ao criar/editar features

#### ✅ **Itens** (via `ItensRelationManager`)

- **Visualizar**: Sim
- **Criar**: Sim
- **Editar**: Sim
- **Excluir**: Sim
- **Observações**:
  - Acesso total aos itens das features
  - Pode gerenciar itens (criar, editar, excluir)
  - Pode associar itens a sprints

### Widgets

- ✅ **PlanejamentoGanttWidget**: Visível
- ✅ **PlanejamentoTimelineWidget**: Visível
- ✅ Outros widgets: Visíveis (conforme regras específicas)

### Regras Especiais

- **Acesso restrito por projeto**: Só vê e gerencia recursos dos projetos aos quais está vinculado
- Foco em planejamento: gerencia sprints, features, módulos e itens
- Não tem acesso a demandas
- Pode criar módulos inline ao criar features

---

## 5. Usuário

### Acesso ao Painel

✅ **Tem acesso ao painel Filament**

### Recursos e Funcionalidades

#### ❌ **Usuários** (`UserResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Clientes** (`ClienteResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Projetos** (`ProjetoResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não
- **Observações**: Não tem acesso direto ao recurso, mas pode ver projetos através de demandas

#### ❌ **Módulos** (`ModuloResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Status** (`StatusResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ✅ **Demandas** (`DemandaResource`)

- **Visualizar**: Sim (apenas suas próprias demandas)
- **Criar**: Sim
- **Editar**: Sim (apenas suas próprias demandas em status "Rascunho")
- **Excluir**: Sim (apenas suas próprias demandas em status "Rascunho")
- **Observações**:
  - **Restrição por Propriedade**: Só vê suas próprias demandas (onde `solicitante_id = user.id`)
  - **Restrição por Status**:
    - Só pode editar/excluir demandas com status "Rascunho"
    - Ao criar, a demanda é automaticamente definida como "Rascunho"
    - Não pode alterar o status da demanda (campo desabilitado)
    - Não pode alterar o solicitante (campo desabilitado, sempre será ele mesmo)
  - Não vê a coluna "Solicitante" na listagem (já que todas são dele)
  - Não pode ver/alterar o campo "Responsável"
  - Ao criar, só vê projetos aos quais tem acesso
  - Pode cancelar solicitação (voltar demanda de "Solicitada" para "Rascunho")

#### ❌ **Sprints** (`SprintResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Features** (`FeatureResource`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

#### ❌ **Itens** (via `ItensRelationManager`)

- **Visualizar**: Não
- **Criar**: Não
- **Editar**: Não
- **Excluir**: Não

### Widgets

- ❌ **PlanejamentoGanttWidget**: Não visível
- ❌ **PlanejamentoTimelineWidget**: Não visível
- ✅ Outros widgets: Visíveis (conforme regras específicas)

### Regras Especiais

- **Acesso muito restrito**: Foco apenas em criar e gerenciar suas próprias demandas
- **Restrição por Propriedade**: Só vê suas próprias demandas
- **Restrição por Status**: Só pode editar/excluir demandas em "Rascunho"
- Não pode visualizar todas as demandas (`canViewAllDemandas()` retorna `false`)
- Pode cancelar solicitação de demandas que ele mesmo solicitou

---

## Resumo de Acesso por Recurso

| Recurso      | Administrador | Gestor       | Analista       | Planejador | Usuário         |
| ------------ | ------------- | ------------ | -------------- | ---------- | --------------- |
| **Usuários** | ✅ Total      | 👁️ Ver       | ❌             | ❌         | ❌              |
| **Clientes** | ✅ Total      | 👁️ Ver       | ❌             | ❌         | ❌              |
| **Projetos** | ✅ Total      | 👁️ Ver       | ❌             | 👁️ Ver\*   | ❌              |
| **Módulos**  | ✅ Total      | ✅ Total     | ❌             | ✅ Total\* | ❌              |
| **Status**   | ✅ Total      | ❌           | ❌             | ❌         | ❌              |
| **Demandas** | ✅ Total      | 👁️ Ver/Criar | ✅ Gerenciar\* | ❌         | ✅ Próprias\*\* |
| **Sprints**  | ✅ Total      | ❌           | ❌             | ✅ Total   | ❌              |
| **Features** | ✅ Total      | ✅ Total     | ❌             | ✅ Total\* | ❌              |
| **Itens**    | ✅ Total      | ❌           | ❌             | ✅ Total   | ❌              |

**Legenda:**

- ✅ Total = Criar, Visualizar, Editar, Excluir
- 👁️ Ver = Apenas visualização
- 👁️ Ver/Criar = Visualizar e criar (sem editar/excluir)
- ✅ Gerenciar\* = Gerenciar apenas demandas dos projetos com acesso
- ✅ Total\* = Gerenciar apenas recursos dos projetos com acesso
- ✅ Próprias\*\* = Gerenciar apenas suas próprias demandas em status "Rascunho"

---

## Regras de Restrição por Projeto

Alguns perfis têm acesso restrito baseado em projetos:

1. **Analista**:

   - Só vê/edita demandas dos projetos aos quais está vinculado
   - Projetos são vinculados através da tabela `projeto_user`

2. **Planejador**:

   - Só vê/gerencia projetos, módulos, features e itens dos projetos aos quais está vinculado
   - Projetos são vinculados através da tabela `projeto_user`

3. **Usuário**:
   - Só vê projetos aos quais tem acesso ao criar demandas
   - Projetos são vinculados através da tabela `projeto_user`

**Nota**: Administradores e Gestores não têm restrição por projeto e veem todos os recursos.

---

## Regras Especiais de Edição de Demandas

### Status "Rascunho" (ordem = 0)

- **Administrador**: Pode editar qualquer demanda
- **Analista**: Pode editar demandas dos projetos com acesso
- **Usuário**: Pode editar apenas suas próprias demandas

### Status "Solicitada" ou posterior (ordem >= 1)

- **Administrador**: Pode editar qualquer demanda
- **Analista**: Pode editar demandas dos projetos com acesso
- **Usuário**: **NÃO pode editar** (apenas cancelar solicitação)
- **Gestor**: **NÃO pode editar**

### Exclusão de Demandas

- **Administrador**: Pode excluir qualquer demanda
- **Usuário**: Pode excluir apenas suas próprias demandas em status "Rascunho"
- **Outros perfis**: Não podem excluir demandas

---

## Métodos de Verificação no Modelo User

O modelo `User` possui os seguintes métodos auxiliares:

- `isAdmin()`: Verifica se é administrador
- `isGestor()`: Verifica se é gestor
- `isUsuario()`: Verifica se é usuário comum
- `isAnalista()`: Verifica se é analista
- `isPlanejador()`: Verifica se é planejador
- `canViewAllDemandas()`: Retorna `true` para Admin, Gestor e Analista
- `canManageSystem()`: Retorna `true` apenas para Administrador

---

## Observações Importantes

1. **Acesso ao Painel**: Todos os perfis têm acesso ao painel Filament, mas com diferentes recursos visíveis.

2. **Restrições de Projeto**: Analistas, Planejadores e Usuários só veem recursos dos projetos aos quais estão vinculados na tabela `projeto_user`.

3. **Restrições de Status**: Usuários comuns só podem editar/excluir demandas em status "Rascunho".

4. **Restrições de Propriedade**: Usuários comuns só veem suas próprias demandas.

5. **Widgets**: Alguns widgets são específicos para perfis de planejamento (Planejador, Gestor, Admin).

6. **RelationManagers**: Alguns recursos têm RelationManagers com regras de acesso próprias (ex: Itens dentro de Features, Demandas dentro de Clientes).


