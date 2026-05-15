CLAUDE.md — Instruções para Claude Code



Este arquivo é lido automaticamente pelo Claude Code (extensão VS Code) ao abrir o projeto. Define convenções, fluxos de trabalho e limites do que o assistente pode fazer sem confirmação.

Documentos base: ARCHITECTURE.md define a constituição do plugin. ROADMAP.md define as fases e issues. Toda decisão de implementação deve estar consistente com esses dois documentos.





Sobre o projeto

ImoGrifo é um plugin WordPress para sites de empreendimentos imobiliários, construído sobre Elementor Pro.

Princípio guia: plugin fino (dados + filtro), Elementor grosso (toda camada visual). Não duplicar funcionalidade que o Elementor Pro já oferece.

Operador único: Thales Pacheco (agência Grifo Propaganda). O plugin é usado pela agência para entregar sites a clientes. Cliente final nunca toca o admin do WordPress.



Stack técnica



PHP: 8.1+ (usar declare(strict\_types=1) em arquivos novos)

WordPress: 6.2+

Elementor Pro: versão estável mais recente

Frontend: vanilla JS + CSS (sem build step, sem framework)

Sem dependências externas: sem Composer, sem npm, sem ACF, sem FacetWP





Estrutura de pastas

imo-grifo/

├── imo-grifo.php           # entry point + define IMOGRIFO\_VER

├── ARCHITECTURE.md         # constituição do projeto

├── ROADMAP.md              # fases e issues

├── CLAUDE.md               # este arquivo

├── LICENSE                 # GPLv2

├── readme.txt              # padrão WordPress

├── .gitignore

├── .editorconfig

├── phpcs.xml.dist          # configuração PHP CodeSniffer

├── uninstall.php           # rotina de desinstalação (após F1-06)

├── assets/

│   ├── css/                # estilos dos widgets

│   └── js/                 # scripts dos widgets

└── includes/

&#x20;   ├── Autoloader.php

&#x20;   ├── Plugin.php

&#x20;   ├── PostType.php

&#x20;   ├── Taxonomies.php

&#x20;   ├── Seeds.php

&#x20;   ├── elementor/

&#x20;   │   ├── Bootstrap.php

&#x20;   │   ├── widgets/        # Filters, MiniSearch, Hero, FormCTA

&#x20;   │   └── dynamictags/    # TagPostTermsFiltered (única core)

&#x20;   └── rest/

&#x20;       └── SuggestController.php

Convenções de nomes:



Diretórios: lowercase (elementor/, widgets/, dynamictags/)

Arquivos PHP de classe: PascalCase (Plugin.php, Filters.php)

Arquivos de configuração: lowercase com ponto (.gitignore, .editorconfig)





Convenções de código PHP

Namespace e autoload

Namespace raiz: ImoGrifo. Estrutura espelha pasta:

phpnamespace ImoGrifo;                              // includes/Plugin.php

namespace ImoGrifo\\Elementor;                    // includes/elementor/Bootstrap.php

namespace ImoGrifo\\Elementor\\Widgets;            // includes/elementor/widgets/Filters.php

namespace ImoGrifo\\DynamicTags;                  // includes/elementor/dynamictags/\*.php

namespace ImoGrifo\\Rest;                         // includes/rest/SuggestController.php

Importante: o autoloader (Autoloader.php) tem fallback case-insensitive, mas não dependa disso. Sempre use o case correto no require\_once e nos namespaces.

Padrões obrigatórios



declare(strict\_types=1); no topo de todo arquivo novo

Type hints em parâmetros e returns

wp\_unslash() antes de qualquer sanitize\_\*() em dados de $\_GET, $\_POST, $\_REQUEST

Nonces em endpoints AJAX (sempre validar com wp\_verify\_nonce)

Prefixo imo\_ em handles, meta keys, options e action hooks (ex: \_imo\_cta\_label, imo\_suggest)



Sanitização — exemplos canônicos

php// Texto simples vindo de input

$q = isset($\_GET\['q']) ? sanitize\_text\_field(wp\_unslash($\_GET\['q'])) : '';



// Slug (URL, taxonomia)

$slug = isset($\_GET\['cidade']) ? sanitize\_title(wp\_unslash($\_GET\['cidade'])) : '';



// Inteiro

$id = isset($\_GET\['id']) ? absint($\_GET\['id']) : 0;



// HTML controlado (excerpt, descrição)

$desc = wp\_kses\_post($input);

O que NÃO fazer



❌ Não usar $wpdb direto se houver função WP equivalente (get\_posts, get\_terms, WP\_Query)

❌ Não criar tabelas customizadas

❌ Não usar extract(), eval(), assert() com strings

❌ Não fazer echo direto sem esc\_html(), esc\_attr(), esc\_url() apropriado

❌ Não adicionar dependência paga ou plugin externo sem registro em DR





Padrão de commits

Formato obrigatório: Conventional Commits.

<tipo>(<escopo opcional>): <descrição curta em minúsculas>



<corpo opcional explicando o porquê>



<rodapé opcional com referência à issue>

Tipos aceitos:

TipoQuando usarfeatNova funcionalidadefixCorreção de bugrefactorMudança de código sem alterar comportamentodocsDocumentação (README, comentários, ARCHITECTURE.md)styleFormatação, indentação (sem mudar lógica)choreManutenção (gitignore, deps, configs)testAdição/correção de testesperfMelhoria de performance

Exemplos válidos:

fix(seeds): renomeia seed() para boot() — referência F1-01

feat(filters): adiciona controles de cor no widget Elementor

docs: adiciona CLAUDE.md com convenções do projeto

refactor(autoload): remove fallback case-insensitive

chore: atualiza .gitignore para incluir .vscode/

Sempre referenciar a issue do ROADMAP quando aplicável (F1-01, F2-03, etc.). Facilita rastreabilidade.



Estratégia de branches

Branch principal



main → código estável. Sempre deployável. Nunca commitar direto aqui (exceto docs raros).



Branches de trabalho

Uma branch por issue do ROADMAP. Nome segue padrão:

<tipo>/<id-issue>-<descrição-curta-em-kebab-case>

Exemplos:

fix/f1-01-seeds-boot

fix/f1-02-imogrifo-ver-constant

feat/f2-01-filters-style-controls

feat/f2-02-filters-autocomplete

docs/f3-04-changelog

Fluxo de trabalho (manual, no terminal)

bash# 1. Antes de começar uma issue

git checkout main

git pull origin main

git checkout -b fix/f1-01-seeds-boot



\# 2. Trabalha, faz commits incrementais

git add <arquivos>

git commit -m "fix(seeds): renomeia seed() para boot() — F1-01"



\# 3. Quando terminar e testar localmente + na Hostinger

git checkout main

git merge --no-ff fix/f1-01-seeds-boot

git push origin main

git branch -d fix/f1-01-seeds-boot



\# 4. (opcional) Deletar branch remota se foi pushada

git push origin --delete fix/f1-01-seeds-boot

Importante: --no-ff no merge é obrigatório. Preserva o desenho visual da branch no histórico.



Limite de autonomia do Claude Code

Pode fazer SEM perguntar



Ler qualquer arquivo do projeto

Sugerir mudanças em código (mostrar diff antes de aplicar)

Refatorar função interna preservando assinatura pública

Corrigir typos, indentação, formatação

Adicionar comentários explicativos

Atualizar documentação para refletir código existente



DEVE perguntar antes



Criar arquivos novos na raiz do projeto

Adicionar ou remover dependências

Modificar ARCHITECTURE.md, ROADMAP.md ou este CLAUDE.md

Mudar slugs de taxonomia ou CPT (afeta URLs e dados existentes)

Mudar assinatura de métodos públicos (afeta integrações Elementor)

Mudar nomes de hooks, action hooks ou filtros (afeta extensões)

Mudar estrutura de meta fields (\_imo\_\*)

Renomear arquivos (afeta require\_once e autoload)

Criar branches ou fazer commits

Fazer push ou merge



NUNCA fazer



Executar comandos git commit, git push, git merge — isso é manual, feito por Thales no terminal

Adicionar "Co-Authored-By: Claude" em commits

Adicionar dependência paga ou plugin externo sem DR registrada

Criar tabelas customizadas no banco

Modificar schema do WordPress core

Modificar arquivos fora do diretório do plugin

Tomar decisões que conflitam com DRs do ARCHITECTURE.md sem reabrir a DR





Como testar mudanças

Ambientes

AmbienteURLUsoDesenvolvimentoD:\\Wordpress\\imo-grifo\\ (local)Edição de códigoTesteharmonia.grifo.agencyValidação após cada issueProduçãonovaharmonia.com.brCliente real — só recebe após validado em teste

Fluxo atual (manual)



Alterar código localmente no VS Code

Salvar arquivos

Compactar pasta do plugin em ZIP

WordPress Admin do harmonia.grifo.agency → Plugins → Adicionar Novo → Enviar Plugin

Sobrescrever versão anterior

Testar funcionalidade afetada

Se ok, repetir passo 3-6 para novaharmonia.com.br



Deploy automático — futuro (issue F3-XX)

Configurar deploy automático via Git Hook ou GitHub Actions para harmonia.grifo.agency. Produção continua manual. Não fazer agora — Fase 3.

Verificação obrigatória antes de marcar issue como concluída



&#x20;PHP error log limpo (sem warnings/notices novos)

&#x20;Funcionalidade testada em harmonia.grifo.agency

&#x20;Pelo menos um caso de uso real funcionando ponta a ponta

&#x20;Critério de pronto da issue (ROADMAP.md) atendido





Decisões registradas (DRs) — referência rápida

IDResumoImplicação práticaDR-01Taxonomia > ACF para eixos categóricosNunca usar ACFDR-02Elementor Pro obrigatórioNão criar fallback para FreeDR-03Sem meta fields estruturados (preço, área, dorms)Nunca adicionar campos numéricos de domínioDR-04Filtro via GET, não AJAXFiltros = URL params + pre\_get\_postsDR-05Plugin não renderiza layoutsToda visualização vem do ElementorDR-06Taxonomias ortogonais entre siSem vínculo automático cidade↔estadoDR-07Duplicação via Yoast Duplicate PostNão reimplementar duplicaçãoDR-08Depreciar TagPostTerms e TagPostInfoCompactPost Info nativo do Elementor Pro resolveDR-09Seeds de 27 UFs com limpeza manualAgência apaga UFs não usadasDR-10Operador é a agência, não cliente finalAdmin pode ser técnico, não precisa onboarding

Antes de tomar qualquer decisão estrutural, conferir essa tabela. Se uma demanda conflita com DR, reabrir explicitamente em conversa antes de implementar.



Convenções i18n



Textdomain: imo-grifo

Todas as strings de UI passam por \_\_() ou \_e()

Strings em PT-BR

Arquivo .pot gerado em languages/imo-grifo.pot (após F3-02)



php// Correto

echo esc\_html\_\_('Encontre um Empreendimento', 'imo-grifo');



// Errado

echo 'Encontre um Empreendimento';



Adição de novas taxonomias (procedimento padrão)

Quando o operador (Thales) pedir uma taxonomia nova, seguir esse procedimento:



Confirmar:



Nome (label visível no admin)

Slug (técnico, lowercase com hífens)

Termos iniciais (lista pra seed)





Registrar nova DR-NN no ARCHITECTURE.md justificando

Editar Taxonomies.php adicionando o register\_taxonomy()

Editar Seeds.php adicionando os termos iniciais

Atualizar Plugin.php se precisar adicionar param ao filtro de archive (apply\_archive\_filters)

Atualizar widget Filters.php se a taxonomia for filtrável

Testar Edição Rápida no admin (checkboxes devem aparecer)

Commit com feat(taxonomies): adiciona taxonomia <slug>



Padrão atual: todas as taxonomias são hierarchical: true (UI de checkboxes), public: true, show\_in\_rest: true.



Erros conhecidos atualmente

Lista dos bugs identificados na análise inicial. Cada um vira issue na Fase 1 do roadmap:

BugIssueSeveridadeSeeds::seed() nunca é chamadoF1-01críticaConstante IMOGRIFO\_VER indefinidaF1-02críticaSuggestController nunca é bootadoF1-03críticaNamespace inconsistente em TagPostInfoCompactF1-04críticaDynamicTags/Bootstrap nunca chamadoF1-05críticaunistall.php (typo, deveria ser uninstall.php)F1-06altaArquivos vazios poluindo repoF1-07baixaDuplo registro de widget/categoria ElementorF1-08médiaTags depreciadas precisam ser marcadasF1-09baixaCaminhos case-sensitive quebradosF1-10alta

Ao executar uma issue, sempre referenciar o ID no commit e validar o critério de pronto definido no ROADMAP.md.



Comunicação com o operador



Idioma: PT-BR

Estilo: direto, sem floreio, sem "claro!" no início

Quando sugerir mudança grande: mostrar o diff completo e esperar aprovação

Quando achar bug não previsto: parar, relatar, perguntar se vira issue nova no ROADMAP antes de corrigir

Quando algo do código contradiz documentação: assumir que documentação está certa, código está errado. Reportar para alinhar.





Referências externas confiáveis



WordPress Plugin Handbook

Elementor Developer Docs

Conventional Commits

Keep a Changelog

PHP The Right Way

