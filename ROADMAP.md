# ImoGrifo — Roadmap

> Este documento organiza o trabalho em **fases sequenciais**. Cada fase tem issues numeradas (`F1-01`, `F2-03`...) que podem ser referenciadas direto em commits, PRs e prompts do Claude Code. Ordem importa: Fase 2 pressupõe Fase 1 concluída.

**Documento base:** [`ARCHITECTURE.md`](./ARCHITECTURE.md) — qualquer issue que conflite com uma DR (decisão) exige atualização do ARCHITECTURE.md antes de ser executada.

---

## Estado atual

- **Versão:** 0.3.0 (declarada em `imo-grifo.php`)
- **Status:** funcional em produção para o caso Nova Harmonia, mas com bugs críticos listados na análise inicial
- **Commits pendentes:** nenhum (Git ainda não configurado — setup é pré-requisito do ROADMAP)

---

## Pré-requisitos

Antes de começar qualquer fase:

- [ ] `git init` no diretório do plugin
- [ ] Repositório criado no GitHub (privado ou público, a discutir)
- [ ] Primeiro commit com o código atual (versão 0.3.0) + `ARCHITECTURE.md` + `ROADMAP.md` + `CLAUDE.md`
- [ ] `.gitignore` configurado (ignorar `node_modules/`, `vendor/`, `.DS_Store`, etc.)

Essa parte fazemos juntos no terminal antes de ligar o Claude Code em qualquer tarefa.

---

## Fase 1 — Estabilização

**Objetivo:** eliminar os bugs críticos identificados na análise inicial e limpar arquivos mortos. Sem funcionalidades novas. Resultado esperado: plugin 100% funcional no estado documentado pelo `ARCHITECTURE.md`.

**Estimativa:** 1-2 blocos de trabalho (uma tarde ou duas)
**Release alvo:** 0.4.0

### F1-01 — Corrigir boot do Seeds

**Problema:** `Plugin.php` tenta chamar `$o->boot()` ou `$o->run()` no objeto `Seeds`, mas a classe só define `seed()`. Seeds nunca executa.

**Ação:**
- Renomear método `Seeds::seed()` para `Seeds::boot()` OU adicionar alias público `run()` que chama `seed()` internamente
- Preferência: renomear para `boot()` (padroniza com PostType/Taxonomies)

**Critério de pronto:**
- Ativar o plugin num WP limpo cria as 5 tipos, 3 status e 27 UFs automaticamente
- Reativar o plugin não duplica termos (idempotência)

**Referências:** DR-09 (seeds de 27 UFs)

---

### F1-02 — Definir constante `IMOGRIFO_VER`

**Problema:** `Hero.php` e `FormCTA.php` referenciam `IMOGRIFO_VER` em `wp_enqueue_style(..., IMOGRIFO_VER)`, mas a constante nunca é definida. Gera PHP Notice.

**Ação:**
- Em `imo-grifo.php`, adicionar `define('IMOGRIFO_VER', '0.4.0');` junto das outras constantes
- Versão deve bater com a declarada no header do plugin

**Critério de pronto:**
- Nenhum PHP Notice/Warning sobre constante indefinida
- Assets dos widgets Hero e CTA carregam com parâmetro de versão correto

---

### F1-03 — Bootar SuggestController

**Problema:** `SuggestController::boot()` registra os hooks `wp_ajax_imo_suggest` mas nunca é chamado. Autocomplete AJAX fica morto.

**Ação:**
- Em `Plugin.php::boot()`, adicionar require + chamada de boot do SuggestController
- Garantir que ambos `wp_ajax_imo_suggest` e `wp_ajax_nopriv_imo_suggest` estão registrados

**Critério de pronto:**
- Endpoint `admin-ajax.php?action=imo_suggest&term=reserva&nonce=...` responde JSON válido
- Nonce é validado corretamente (retorna 403 sem nonce válido)
- Busca com termo < 3 caracteres retorna array vazio

**Referências:** dependência de F2-02 (Filters autocomplete)

---

### F1-04 — Corrigir namespaces das Dynamic Tags

**Problema:**
- `TagPostInfoCompact.php` usa namespace `ImoGrifo\Elementor\DynamicTags` (inconsistente)
- Todas as outras usam `ImoGrifo\DynamicTags`
- `DynamicTags/Bootstrap.php` instancia `new \ImoGrifo\DynamicTags\TagPostInfoCompact()` → fatal error

**Ação:**
- Padronizar todas as dynamic tags no namespace `ImoGrifo\DynamicTags`
- Corrigir o `TagPostInfoCompact.php`
- Validar que o `DynamicTags/Bootstrap.php` encontra todas as classes

**Critério de pronto:**
- Todas as dynamic tags no mesmo namespace
- Nenhum fatal error ao ativar Elementor Pro com o plugin
- Dynamic tags aparecem no seletor de tags do Elementor (mesmo as que serão depreciadas — F1-09)

---

### F1-05 — Bootar DynamicTags integrado ao Elementor Bootstrap

**Problema:** `DynamicTags/Bootstrap.php` existe mas não é chamado em lugar nenhum. Nenhuma dynamic tag do plugin é registrada.

**Ação:**
- Em `Elementor/Bootstrap.php::init()`, adicionar chamada para `DynamicTags\Bootstrap::init()`
- Corrigir os paths dos `require_once` dentro do `DynamicTags/Bootstrap.php` para usar `includes/DynamicTags/` (case correto) em vez de caminhos quebrados

**Critério de pronto:**
- `TagPostTermsFiltered`, `TagMetaCTALabel`, `TagMetaCTAUrl` aparecem como opções de dynamic tag no Elementor
- Testar funcionamento da `TagPostTermsFiltered` num widget de texto do Elementor
- Nenhum erro de require_once no log do PHP

---

### F1-06 — Renomear `unistall.php` para `uninstall.php`

**Problema:** WordPress só reconhece `uninstall.php` (dois 'l'). Com o nome atual, limpeza de desinstalação nunca executa.

**Ação:**
- Renomear `unistall.php` para `uninstall.php`
- Implementar rotina básica de limpeza:
  - `if (!defined('WP_UNINSTALL_PLUGIN')) exit;`
  - Deletar options criadas pelo plugin (se houver)
  - Deletar transients do plugin (se houver)
  - **NÃO deletar** posts de empreendimento nem termos (dados do cliente)

**Critério de pronto:**
- Arquivo renomeado
- Código da rotina de uninstall documentado com comentário explicando o que faz
- Testar: desinstalar o plugin não deleta empreendimentos nem termos

---

### F1-07 — Remover arquivos vazios do repositório

**Problema:** Diversos arquivos existem com 0 bytes — confundem quem lê o código e sujam a estrutura.

**Arquivos para deletar:**
- `includes/elementor/widgets/Caracteristicas.php`
- `includes/elementor/widgets/Card.php`
- `includes/elementor/widgets/Estagio.php`
- `includes/elementor/widgets/Frase.php`
- `includes/elementor/widgets/Galeria.php`
- `includes/elementor/widgets/Grid.php`
- `includes/elementor/widgets/Mapa.php`
- `includes/elementor/widgets/Sobre.php`
- `includes/helpers/Assets.php`
- `includes/helpers/Sanitize.php`
- `includes/helpers/Template.php`
- `includes/elementor/dynamictags/TagMetaDorms.php`
- `includes/elementor/dynamictags/TagMetaEndereco.php`
- `includes/elementor/dynamictags/TagMetaMetragem.php`
- `includes/elementor/dynamictags/TagMetaPreco.php`
- `includes/elementor/dynamictags/TagMetaVagas.php`
- `includes/rest/SearchCrontoller.php` (arquivo vazio + typo no nome)
- `assets/js/min-search.js` (arquivo vazio — será reconstruído em F2-03)

**Critério de pronto:**
- Nenhum arquivo com 0 bytes no repositório
- Plugin continua funcionando normalmente (esses arquivos nunca foram usados ativamente)

**Referências:** DR-03 (sem meta fields estruturados)

---

### F1-08 — Corrigir duplo registro de widget/categoria do Elementor

**Problema:** Tanto `Plugin.php` quanto `Elementor/Bootstrap.php` registram a categoria `imo-grifo` e o widget Filters. Gera warnings.

**Ação:**
- Remover o "fallback" de registro de categoria e widget no `Plugin.php`
- Centralizar todo o registro Elementor no `Elementor/Bootstrap.php`
- O `Plugin.php` só é responsável por dar require no Bootstrap quando `did_action('elementor/loaded')` for true

**Critério de pronto:**
- Nenhum warning sobre duplo registro no Elementor
- Categoria "Imo Grifo" aparece apenas uma vez no painel de widgets
- Widget Filters aparece apenas uma vez

---

### F1-09 — Depreciar `TagPostTerms` e `TagPostInfoCompact`

**Problema:** Essas duas tags duplicam funcionalidade que o Post Info nativo do Elementor Pro já oferece. Ver DR-08.

**Ação:**
- Adicionar comentário `@deprecated since 0.4.0` no docblock de cada classe
- Mudar o título exibido no Elementor para incluir `(deprecated)` — ex: `Imo: Post Terms (Lista) [deprecated]`
- **NÃO remover** do código ainda — podem estar em uso em templates de clientes existentes
- Documentar no CHANGELOG que serão removidas na 1.0.0

**Critério de pronto:**
- Tags aparecem no Elementor com sufixo `[deprecated]`
- Docblock `@deprecated` presente
- Entrada no CHANGELOG explicando o motivo e data de remoção planejada

**Referências:** DR-08

---

### F1-10 — Corrigir caminhos case-sensitive

**Problema:** `Elementor/Bootstrap.php` usa caminho `includes/Elementor/Widgets/Filters.php` (capitalizado) mas o arquivo está em `includes/elementor/widgets/Filters.php` (minúsculo). Falha em Linux.

**Ação:**
- Padronizar estrutura: **todos os diretórios em minúsculo, todos os arquivos em PascalCase** (já é o padrão majoritário)
- Atualizar `Elementor/Bootstrap.php` e qualquer outro `require_once` que referencie path com case errado
- O autoloader já tem fallback case-insensitive, mas não podemos depender disso

**Critério de pronto:**
- Todos os `require_once` usam paths com case correspondente ao filesystem
- Plugin funciona em ambiente Linux (case-sensitive) sem precisar do fallback do autoloader
- Estrutura de diretórios consistente

---

## Fase 2 — Widget Filters personalizável e busca funcional

**Objetivo:** transformar o widget Filters de "barra hardcoded com cores fixas" em componente 100% personalizável via controles do Elementor. Implementar autocomplete real. Reconstruir a MiniSearch.

**Estimativa:** 2-4 blocos de trabalho
**Release alvo:** 0.5.0

### F2-01 — Refatorar widget Filters em subcomponentes estilizáveis

**Problema atual:** o widget renderiza uma barra com CSS hardcoded (`--bg:#f0ebf5`, `--orange:#EA932A`). Cliente/agência não consegue mudar cores, fontes, espaçamentos sem editar CSS.

**Ação:**
- Adicionar controles de estilo do Elementor para cada elemento visual da barra:
  - Wrapper: background, border-radius, padding, box-shadow
  - Input de busca: background, cor do texto, placeholder, font-size, padding
  - Selects de cidade/status: background, cor, border, ícone do dropdown
  - Botão de busca: background (hover incluído), cor do ícone, border-radius, tamanho
  - Lista de autocomplete: background, cor do item, hover, border
- Reorganizar CSS para usar CSS variables passadas via controles do Elementor (`{{WRAPPER}}`)
- Remover o `@import` de Google Fonts (deixar o tema/Elementor controlar fonte)
- Manter o CSS default como "tema inicial" aplicado via valores default dos controles

**Critério de pronto:**
- Usuário consegue mudar cada cor do widget pelo painel do Elementor
- Usuário consegue mudar tipografia via controles do Elementor
- CSS "hardcoded" elimina variáveis `--bg`, `--blue`, `--orange`, etc.
- Default visual mantém aparência atual (retrocompatível — sites existentes não mudam)

---

### F2-02 — Implementar autocomplete real no Filters

**Problema atual:** o widget tem uma `<ul class="rn-autocomplete">` no HTML e chama `wp_localize_script` com nonce, mas o JS nunca consome isso. Lista fica sempre vazia.

**Ação:**
- Reescrever `assets/js/filters.js` para:
  - Escutar `input` no campo de busca (debounce 250ms)
  - Se termo ≥ 3 caracteres, fazer request em `admin-ajax.php?action=imo_suggest`
  - Popular a `<ul>` com resultados (título + link)
  - Click num item navega para o link
  - ESC fecha a lista
  - Click fora fecha a lista
  - Navegação via teclado (↑↓ + Enter) — nice-to-have
- Integrar com o SuggestController (F1-03)

**Critério de pronto:**
- Digitar "res" na busca mostra os empreendimentos que contêm "res" no título
- Clicar numa sugestão navega para a página individual
- Fecha corretamente (ESC, click fora)
- Sem requests antes de 3 caracteres
- Sem bugs visuais (lista sobreposta, z-index correto)

**Referências:** F1-03

---

### F2-03 — Reconstruir widget MiniSearch

**Problema atual:** widget MiniSearch existe, mas:
- `assets/js/min-search.js` é arquivo vazio (0 bytes)
- Widget chama endpoint REST `rest_url('imo-grifo/v1/suggest')` que não existe (só existe o endpoint AJAX)
- Autocomplete nunca funcionou

**Ação:**
- Decidir: endpoint único? AJAX (`admin-ajax.php?action=imo_suggest`) ou REST (`/wp-json/imo-grifo/v1/suggest`)?
  - Recomendação: **AJAX** (já existe, menos dependências, suficiente pro caso)
- Atualizar `MiniSearch.php::render()` para usar AJAX com nonce (mesma abordagem do Filters)
- Criar `assets/js/mini-search.js` com lógica de autocomplete (similar ao Filters, mais simples)
- Adicionar controles de estilo Elementor (mesmo padrão do F2-01)

**Critério de pronto:**
- Widget MiniSearch funciona standalone (não depende de estar dentro de archive)
- Autocomplete funcional
- Estilizável via Elementor
- Pode ser colocado em header, sidebar, footer — qualquer lugar

---

### F2-04 — Revisar widget Hero (limpar ou manter)

**Problema:** widget Hero existe como auxiliar, mas foi criado no contexto antigo (quando imaginávamos que o plugin desenhasse layouts). Hoje, com a filosofia "página individual é editada livremente no Elementor", pode ser redundante.

**Ação — etapa de decisão:**
- Confirmar com o operador (você): Hero ainda é usado em algum site em produção?
- Se sim: manter, documentar, adicionar controles de estilo faltantes
- Se não: **depreciar** com mesma estratégia do F1-09 (manter no código mas marcar como deprecated, remover em 1.0.0)

**Critério de pronto:**
- Decisão registrada em DR nova
- Ação (manter+melhorar ou depreciar) executada conforme decisão

---

### F2-05 — Revisar widget CTA (mesma análise do Hero)

Mesma lógica do F2-04, aplicada ao widget CTA (`FormCTA.php`).

---

## Fase 3 — Polimento e preparação para distribuição

**Objetivo:** deixar o plugin num estado apresentável — documentação, screenshots, i18n completo. Preparar (sem obrigação de executar) para eventual publicação no WordPress.org.

**Estimativa:** 1-2 blocos de trabalho
**Release alvo:** 1.0.0

### F3-01 — Atualizar `readme.txt` no padrão WordPress.org

**Ação:**
- Reescrever `readme.txt` seguindo template oficial do WordPress.org
- Seções: Description, Installation, Frequently Asked Questions, Screenshots, Changelog, Upgrade Notice
- Declarar corretamente: `Requires at least`, `Tested up to`, `Requires PHP`, `Stable tag`

**Critério de pronto:**
- `readme.txt` passa no validador do WordPress.org (https://wordpress.org/plugins/developers/readme-validator/)

---

### F3-02 — Gerar arquivo `.pot` para i18n

**Ação:**
- Rodar WP-CLI ou Poedit para extrair todas as strings de tradução
- Colocar `imo-grifo.pot` em `languages/`
- Documentar processo no `CLAUDE.md`

**Critério de pronto:**
- `languages/imo-grifo.pot` existe e tem todas as strings do plugin
- Nenhuma string de UI sem `__()` ou `_e()`

---

### F3-03 — Screenshots e documentação visual

**Ação:**
- Criar diretório `.wordpress-org/` com screenshots nomeados (`screenshot-1.png`, `screenshot-2.png`...)
- Capturar:
  - Widget Filters funcional no frontend
  - Edição Rápida com as 4 taxonomias
  - Card Empreendimento
  - Archive template do Elementor usando o widget Filters
- Criar banner e icon do plugin (opcional, mas necessário se publicar no WordPress.org)

**Critério de pronto:**
- Screenshots capturados e referenciados no `readme.txt`
- Banner e ícone criados (ou decidido não publicar no .org e isso vira opcional)

---

### F3-04 — CHANGELOG.md

**Ação:**
- Criar `CHANGELOG.md` na raiz seguindo padrão [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/)
- Documentar todas as versões: 0.3.0 (atual), 0.4.0 (Fase 1), 0.5.0 (Fase 2), 1.0.0 (Fase 3)
- Especificar: Added, Changed, Deprecated, Removed, Fixed, Security

**Critério de pronto:**
- `CHANGELOG.md` existe
- Todas as mudanças das Fases 1 e 2 documentadas
- Convenção de formato estabelecida para futuras versões

---

### F3-05 — Remover tags depreciadas da Fase 1

**Ação (opcional, só executar se tiver certeza):**
- Remover definitivamente `TagPostTerms` e `TagPostInfoCompact` do código
- Atualizar `DynamicTags/Bootstrap.php` para não registrá-las mais
- Entrada no CHANGELOG em "Removed"

**Critério de pronto:**
- Código dessas classes removido
- Plugin continua funcionando
- Sites em produção que usam essas tags foram **verificados antes** — se algum ainda usa, adiar remoção

---

## Fase 4 — Backlog futuro

*Esta seção é propositalmente vazia.*

Ideias para expansão futura (ex: import via CSV, campo de "destaque", integração com Google Maps, campo de localização com lat/lng) **não são registradas preventivamente** aqui. Quando uma demanda real aparecer, aí sim criamos issue específica, revisamos contra o ARCHITECTURE.md (especialmente a seção "Fora de escopo") e decidimos:

1. Entra como F4-XX neste roadmap?
2. Vira plugin complementar separado?
3. É rejeitada?

Adicionar itens antes de existir demanda real é violação do princípio "escopo fechado" registrado no ARCHITECTURE.md.

---

## Fluxo de execução

Para cada issue:

1. **Eu (Claude no chat)** escrevo spec detalhada referenciando a issue — ex: "Execute F1-01 conforme ROADMAP.md"
2. **Você** cola a spec no Claude Code (VS Code)
3. **Claude Code** executa, cria branch (`fix/f1-01-seeds-boot`), implementa, testa localmente
4. **Você** revisa o diff, testa na Hostinger se necessário
5. **Você** faz merge pra `main` via terminal (comando `git merge` que a gente combina)
6. **Eu** atualizo o roadmap marcando a issue como concluída

Uma fase só é considerada **concluída** quando todas as issues dela têm critério de pronto atendido e o código foi mergeado em `main`.

---

## Estado das fases

| Fase | Nome | Issues | Status |
|---|---|---|---|
| Pré-req | Setup Git | 4 checkboxes | 🟡 Em aberto |
| Fase 1 | Estabilização | F1-01 a F1-10 (10 issues) | ⏸️ Aguardando pré-req |
| Fase 2 | Filters personalizável | F2-01 a F2-05 (5 issues) | ⏸️ Aguardando Fase 1 |
| Fase 3 | Polimento | F3-01 a F3-05 (5 issues) | ⏸️ Aguardando Fase 2 |
| Fase 4 | Backlog futuro | — | 📋 Vazio por design |

Legenda: 🟡 em aberto · 🟢 em andamento · ✅ concluído · ⏸️ aguardando dependência · 📋 backlog
