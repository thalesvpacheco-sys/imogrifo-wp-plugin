# ImoGrifo — Architecture

> Este documento é a **constituição** do plugin. Qualquer mudança que contrarie algo aqui exige discussão explícita e registro em "Decisões" (DR-NN) antes de ser implementada.

---

## Propósito

ImoGrifo é um plugin **fino** de estrutura de dados e filtro para sites de empreendimentos imobiliários (incorporadoras, bairros planejados, loteamentos, condomínios) construídos com WordPress + Elementor Pro.

O plugin fornece a camada de dados (CPT + taxonomias) e o mecanismo de filtro (widgets + query). **A camada visual é 100% responsabilidade do Elementor Pro** — Theme Builder para archive e single, Loop Grid para listagem, Post Info / Post Title / Featured Image nativos para renderizar campos no card.

O plugin não desenha páginas, não impõe templates, não duplica funcionalidade que o Elementor Pro já oferece nativamente.

---

## Quem usa o plugin

**O operador é a agência (o desenvolvedor que monta o site), não o cliente final.**

Consequências práticas dessa definição:

- **Cliente final nunca toca o admin do WordPress.** Recebe o site pronto e interage apenas com o frontend.
- **UX do admin prioriza eficiência de cadastro em lote**, não simplicidade para usuário leigo.
- **Decisões entre "fácil para principiante" vs "rápido para quem sabe" optam sempre pelo segundo.**
- **Convenções e flows estabelecidos por quem conhece o plugin** (edição rápida, checkboxes de taxonomia, duplicate post) são preservados.

Fluxo real de trabalho, em sequência:

1. **Cadastrar termos das taxonomias** que o cliente atende (Status, Cidades, Tipos, Estados). Uma vez no início do projeto.
2. **Criar empreendimento** via "Adicionar novo Empreendimento". Apenas título e slug nesse momento.
3. **Classificar via Edição Rápida** na listagem — checkboxes das 4 taxonomias numa tela só. Fluxo de dez segundos por empreendimento.
4. **Editar a página individual no Elementor** — design livre, conteúdo específico daquele empreendimento.
5. **Duplicar empreendimentos similares** via plugin Yoast Duplicate Post quando a estrutura se repete.
6. Empreendimento aparece automaticamente no archive (template do Elementor Theme Builder) com o Card Empreendimento (template Loop Item) como visualização.

---

## Escopo

### Dentro de escopo

- Custom Post Type `empreendimento`
- Taxonomias: `cidade`, `estado`, `tipo`, `status` (obra), `lotes` (quantidade — administrativa, não filtrável)
- Filtro de archive via URL (`?q=...&cidade=...&status_obra=...`) usando `pre_get_posts`
- Widget Elementor de barra de filtros (Filters) para uso em Archive templates
- Widget Elementor de busca compacta (MiniSearch) com autocomplete AJAX
- Widgets auxiliares Hero e CTA para página single — opcionais, meta fields específicos
- Dynamic Tag `TagPostTermsFiltered` (filtragem de termos que o Post Info nativo não oferece)
- Endpoint AJAX para autocomplete de busca por título
- Seeds opcionais de termos iniciais (27 UFs, tipos básicos, status básicos)

### Fora de escopo — por design, não por falta de tempo

Os itens abaixo **não são** e **não devem virar** parte deste plugin. Se a demanda aparecer, a resposta correta é construir um plugin complementar ou usar solução existente do mercado.

- **Imóveis avulsos** (apartamento/casa individual com preço, área, dormitórios, vagas). Isso é modelagem de *unidade*, não de *empreendimento*.
- **Checkout, reserva online, pagamento.** Sem carrinho, sem gateway.
- **CRM / pipeline de leads.** Plugin não captura, envia e-mail transacional ou integra com RD Station/HubSpot.
- **Geolocalização / busca por raio no mapa.** Sem latitude/longitude. Localização é categórica, não geográfica.
- **Portal multi-imobiliária / marketplace.** Um site = uma empresa.
- **Editor visual próprio.** Não reinventamos Elementor.
- **Meta fields estruturados** para preço, metragem, vagas, dormitórios, endereço. Ver DR-03.
- **Duplicação de posts.** Ver DR-07. Use Yoast Duplicate Post.
- **Dynamic tags redundantes com o Post Info nativo** do Elementor Pro. Ver DR-08.

---

## Filosofia

### 1. Plugin fino, Elementor grosso

O plugin fornece **dados e estrutura**. O Elementor Pro fornece **tudo visual e todo o framework de templates dinâmicos**: card (Loop Item), archive (Theme Builder), single (Theme Builder), header, footer, popups.

A divisão é propositalmente assimétrica — ambiciosa na modelagem, mínima na renderização.

### 2. Não duplicar o Elementor Pro

Se o Elementor Pro já resolve nativamente algum problema (Post Info exibindo Terms com ícone e link, Post URL alimentando botões, Featured Image renderizando miniatura), o plugin **não reimplementa**. Isso evita manutenção duplicada, bugs de sincronia e confusão ("uso o Post Info ou o Imo: Post Terms?").

### 3. Taxonomia > post_meta para eixos categóricos

Cidade, estado, tipo e status são **taxonomias nativas hierárquicas**, não campos customizados. Razões:

- Índice em nível de banco (performance em escala)
- Página de arquivo automática (`/cidade/goiania/`)
- `tax_query` é ordens de grandeza mais rápida que `meta_query`
- Reutilização de termos entre empreendimentos
- **Edição Rápida com checkboxes** (hierarquia habilita essa UI)
- Zero dependência de ACF ou similar

### 4. Taxonomias ortogonais entre si

As 4 taxonomias (cidade, estado, tipo, status) são **independentes**. Não há vínculo automático entre elas — cadastrar "Paço do Lumiar" não implica "MA" automaticamente. A correspondência é feita manualmente pela agência no momento da Edição Rápida.

Razão: o operador conhece cada empreendimento quando cadastra. Vincular taxonomias automaticamente adicionaria fricção (precisar manter tabela de cidade→UF atualizada) em troca de "consistência" que o fluxo manual já garante.

### 5. Página individual é editada livremente no Elementor

Cada empreendimento tem sua página individual desenhada no Elementor Pro — imagens próprias, seções específicas, CTAs únicos, galerias, mapas embed. O plugin **não força** template de single.

Consequência: **sem schema de meta fields** para preço, metragem, dormitórios, vagas, área de lazer, número de torres, etc. Conteúdo visual específico vive dentro do post, não em metadados.

### 6. Filtro via GET, não AJAX

Filtros de archive usam `pre_get_posts` com parâmetros na URL. Razões:

- URLs compartilháveis e indexáveis por buscadores
- Histórico do navegador funciona (voltar/avançar)
- Sem dependência de JavaScript pesado para o filtro básico
- SEO-friendly

Autocomplete do input de busca é a única parte AJAX — necessidade real da UX do componente.

### 7. Zero dependência paga além do Elementor Pro

Sem ACF, sem FacetWP, sem JetEngine, sem Toolset, sem MetaBox, sem tabelas customizadas.

Se o plugin for removido, não há migração complexa — posts e termos continuam no banco, acessíveis por qualquer consulta padrão.

---

## Camadas

```
┌─────────────────────────────────────────────────────────┐
│ Visitante do site                                        │
│   Vê cards, navega archive, usa filtros                  │
└─────────────────────────────────────────────────────────┘
                          ▲
┌─────────────────────────────────────────────────────────┐
│ Elementor Pro (camada visual)                            │
│   Theme Builder: Archive, Loop Item, Single              │
│   Widgets nativos: Loop Grid, Post Info, Post Title,     │
│     Featured Image, Post Excerpt                         │
│   Dynamic Tags framework                                 │
│   Post URL / Post Meta nativos                           │
└─────────────────────────────────────────────────────────┘
                          ▲
┌─────────────────────────────────────────────────────────┐
│ ImoGrifo (camada de dados e filtro)                      │
│   CPT empreendimento                                     │
│   Taxonomias: cidade, estado, tipo, status               │
│   Widgets: Filters (Archive), MiniSearch, Hero, CTA      │
│   Dynamic Tag: TagPostTermsFiltered                      │
│   pre_get_posts (filtro de archive)                      │
│   AJAX suggest endpoint                                  │
└─────────────────────────────────────────────────────────┘
                          ▲
┌─────────────────────────────────────────────────────────┐
│ WordPress Core                                           │
└─────────────────────────────────────────────────────────┘
```

### Fluxo típico de uma listagem

1. Visitante acessa `/empreendimentos?cidade=goiania`
2. WP resolve como archive do CPT `empreendimento`
3. Elementor renderiza o **Archive template** (Theme Builder)
4. Dentro do template, o **widget Filters** (plugin) renderiza a barra de filtros
5. Dentro do template, o **Loop Grid** (Elementor nativo) consulta os posts
6. O plugin intercepta a query via `pre_get_posts` e aplica `tax_query` conforme os GETs
7. O Loop Grid renderiza cada post usando o template **Loop Item "Card Empreendimento"**
8. Dentro do Card, o **Post Info nativo** do Elementor Pro renderiza Cidade, Estado e Status (cada um com seu ícone)
9. O **Post Title nativo** renderiza o nome do empreendimento
10. O **Botão** tem seu link vindo de **Post URL** (nativo)

Nenhuma dessas etapas renderiza HTML vindo do plugin, exceto a barra de filtros do widget Filters.

---

## Modelo de dados

### Custom Post Type: `empreendimento`

| Propriedade | Valor |
|---|---|
| Slug | `empreendimento` |
| Archive slug | `empreendimentos` |
| Public | sim |
| Show in REST | sim (obrigatório para dynamic tags) |
| Supports | `title`, `editor`, `excerpt`, `thumbnail` |
| Menu icon | `dashicons-building` |
| Has archive | sim |

### Taxonomias

Públicas, `show_in_rest: true`. Todas `hierarchical: true` (UI de checkboxes), **exceto `lotes`** (`hierarchical: false`, estilo Tags — ver DR-12):

| Slug interno | Label | Hierárquica | Rewrite | REST base | Uso |
|---|---|---|---|---|---|
| `cidade` | Cidades | sim | `/cidade/` | `cidades` | Município do empreendimento |
| `estado` | Estados (UF) | sim | `/estado/` | `estados` | UF (AC..TO) |
| `tipo` | Tipos | sim | `/tipo-empreendimento/` | `tipos` | Bairro Planejado, Condomínio Vertical, Loteamento... |
| `status` | Status (Obra) | sim | `/status-obra/` | `status-obra` | Lançamento, Em obras, Pronto para construir, Pronto |
| `lotes` | Lotes | **não** | `/lotes/` | `lotes` | Quantidade de lotes do empreendimento — administrativa, não usada como filtro (ver DR-12) |

**Observação sobre o slug `status`:** o slug interno é `status` mas o rewrite é `/status-obra/` e o REST base é `status-obra`. O parâmetro de URL aceito no filtro é `status_obra` (com compat para `status` por histórico). Ver lógica em `apply_archive_filters()`.

### Seeds iniciais (opcional, `Seeds.php`)

Executado na ativação/boot, idempotente (não sobrescreve termos existentes):

- **Tipos:** Residencial, Condomínio, Comercial, Misto, Loteamento
- **Status:** Lançamento, Em obras, Pronto
- **Estados:** todas as 27 UFs

A agência **deleta manualmente** as UFs (e outros termos) que o cliente não atende no início do projeto. O seed completo existe por conveniência — vale mais um momento de limpeza do que cadastrar UF a UF.

### Meta fields

**O plugin não define schema oficial de meta fields.**

Meta field **oficial e ativo**:

- `_imo_external_link` — URL de LP externa que sobrescreve o permalink do empreendimento (DR-13)

Meta fields pontuais usados pelos widgets auxiliares Hero e CTA (**depreciados**, ver nota DR-11 abaixo):

- `_imo_cta_label` — texto do botão CTA
- `_imo_cta_url` — URL do botão CTA
- `_imo_title` — título do hero (override do post_title)
- `_imo_subtitle` — subtítulo do hero
- `_imo_hero_bg_id` — ID de attachment para background do hero

> **Nota (DR-11):** os 5 meta fields `_imo_cta_*` / `_imo_*` do Hero listados acima estão ligados exclusivamente aos widgets Hero e FormCTA, depreciados em maio/2026. Serão removidos do código na Fase 2. Registros órfãos no banco de dados de sites existentes são inofensivos.

Esses são **conveniências dos widgets auxiliares**, não schema de domínio. Se Hero/CTA forem removidos, os fields ficam órfãos no banco mas não quebram nada.

Qualquer novo meta field proposto deve passar pela pergunta da Filosofia #5 antes de ser adicionado: *é display ou é filtro/ordenação?*

---

## Componentes do plugin

| Módulo | Responsabilidade | Status |
|---|---|---|
| `imo-grifo.php` | Entry point, constantes, instancia `Plugin` | ativo |
| `includes/Plugin.php` | Bootstrap geral, hooks, filtro de archive | ativo |
| `includes/Autoloader.php` | PSR-4 com fallback case-insensitive | ativo |
| `includes/PostType.php` | Registra CPT `empreendimento` | ativo |
| `includes/Taxonomies.php` | Registra as 4 taxonomias | ativo |
| `includes/Seeds.php` | População inicial de termos | ativo |
| `includes/ExternalLink.php` | Meta `_imo_external_link` + override de permalink | ativo (DR-13) |
| `includes/Elementor/Bootstrap.php` | Init Elementor: categoria, widgets, assets | ativo |
| `includes/Elementor/Widgets/Filters.php` | Barra de filtros para Archive | ativo — **core** |
| `includes/Elementor/Widgets/MiniSearch.php` | Busca compacta + autocomplete | ativo |
| `includes/Elementor/Widgets/Hero.php` | Hero para página single | deprecated — remover na Fase 2 (DR-11) |
| `includes/Elementor/Widgets/FormCTA.php` | Botão CTA | deprecated — remover na Fase 2 (DR-11) |
| `includes/DynamicTags/Bootstrap.php` | Registra dynamic tags | ativo |
| `includes/DynamicTags/TagPostTermsFiltered.php` | Include/exclude/order de termos | ativo — único com valor real |
| `includes/DynamicTags/TagPostTerms.php` | Lista termos de N taxonomias | **depreciar** (ver DR-08) |
| `includes/DynamicTags/TagPostInfoCompact.php` | Cidade/Estado/Status/Tipo em linha | **depreciar** (ver DR-08) |
| `includes/DynamicTags/TagMetaCTALabel.php` | Meta field _imo_cta_label como tag | deprecated — remover na Fase 2 (DR-11) |
| `includes/DynamicTags/TagMetaCTAUrl.php` | Meta field _imo_cta_url como tag | deprecated — remover na Fase 2 (DR-11) |
| `includes/Rest/SuggestController.php` | Endpoint AJAX de autocomplete | ativo |

### Fonte única de verdade para o card

O **Card Empreendimento** (template Loop Item do Elementor) é construído exclusivamente com widgets nativos do Elementor Pro:

- **Imagem** → Featured Image nativo
- **Pill de Tipo** ("Bairro Planejado") → Post Info nativo com Meta Data > Terms, taxonomia `tipo`
- **Localização** (Estado | Cidade) → Post Info nativo com dois itens de Terms, taxonomias `estado` e `cidade`, cada um com ícone custom
- **Título** → Post Title nativo
- **Status** ("Em obra") → Post Info nativo com Terms, taxonomia `status`
- **Botão "Veja mais"** → Botão nativo com link em Post URL dinâmico

**Nenhuma Dynamic Tag do ImoGrifo é usada no card padrão.** O plugin só precisa garantir que as taxonomias estejam registradas corretamente — o Elementor consome nativo.

---

## Dependências e compatibilidade

### Obrigatórias

| Dependência | Versão mínima | Por quê |
|---|---|---|
| PHP | 8.1 | `declare(strict_types=1)`, union types, match expression |
| WordPress | 6.2 | APIs modernas de REST, block editor compat |
| Elementor Pro | Última estável | Theme Builder, Loop Grid, Post Info com Terms, Dynamic Tags |

### Plugins recomendados (não incluídos)

| Plugin | Função |
|---|---|
| Yoast Duplicate Post | Duplicar empreendimentos com estrutura similar |

### Política de versão

- **Adaptar continuamente** à versão mais recente de WP, PHP e Elementor Pro.
- **Não manter compat retroativa eterna.** Se o Elementor Pro renomear uma constante, o plugin atualiza.
- **PHP e WP mínimo podem subir** conforme o ecossistema avança.

### Não suportado

- **Elementor Free.** Theme Builder, Loop Grid e Post Info com Terms são exclusivos do Pro.
- **Outros builders** (Divi, Bricks, Oxygen, Breakdance, Beaver Builder).
- **Temas específicos.** Plugin é theme-agnostic.

### Internacionalização

- Textdomain: `imo-grifo`
- Strings em PT-BR por padrão
- Preparado para `.pot` (todas as strings passam por `__()` / `_e()`)
- Tradução para outros idiomas: possível, não é prioridade atual

---

## Decisões registradas (DRs)

Formato: cada decisão arquitetural relevante recebe número, data, contexto, decisão e consequência. DRs antigas não são editadas — são **superadas** por novas.

### DR-01: Taxonomia nativa em vez de ACF para eixos categóricos

**Data:** 2025-10
**Contexto:** Padrão de mercado em WP é usar ACF para tudo que parece "campo customizado".
**Decisão:** Usar taxonomias nativas hierárquicas para cidade, estado, tipo, status.
**Consequência:** Melhor performance em `tax_query`, URLs de arquivo limpas, admin UI nativa com checkboxes na Edição Rápida, zero dependência paga.

### DR-02: Elementor Pro é dependência obrigatória

**Data:** 2025-10
**Contexto:** Considerado oferecer fallback para Elementor Free ou funcionar sem builder.
**Decisão:** Depender explicitamente do Pro. Sem fallback.
**Consequência:** Público-alvo fica restrito a agências que já licenciam Pro (o perfil real do mercado brasileiro imobiliário atendido). Simplifica o código.

### DR-03: Sem meta fields estruturados para preço, área, dormitórios, vagas, endereço

**Data:** 2025-10
**Contexto:** Tentativa inicial criou arquivos `TagMetaPreco`, `TagMetaDorms`, `TagMetaMetragem`, `TagMetaVagas`, `TagMetaEndereco` (vazios).
**Decisão:** Abandonar. Esse dado fica na página individual editada livremente no Elementor.
**Consequência:** Não é possível filtrar archive por faixa de preço via plugin. Se essa demanda virar crítica, reavaliar com plugin complementar.
**Ação:** arquivos vazios devem ser removidos do repositório (Fase 1).

### DR-04: Filtro via GET + `pre_get_posts`, não AJAX

**Data:** 2025-10
**Contexto:** Tendência moderna é filtro AJAX com UX "instant".
**Decisão:** GET + `pre_get_posts`. Única parte AJAX é o autocomplete do input de busca.
**Consequência:** URLs indexáveis, SEO-friendly, voltar/avançar do navegador funciona. Troca: sem filtragem "sem reload". Aceito.

### DR-05: Plugin não renderiza layouts, só dados e filtro

**Data:** 2025-10
**Contexto:** Plugins similares (Houzez, WP Residence) vêm com templates prontos acoplados a temas.
**Decisão:** Plugin fornece apenas widgets de filtro/busca. Card, archive e single são templates do Elementor Theme Builder, desenhados por quem constrói cada site.
**Consequência:** Cliente/agência tem liberdade total de design. Plugin é reutilizável entre projetos sem ajustes visuais.

### DR-06: Taxonomias são ortogonais entre si

**Data:** 2025-10
**Contexto:** Considerado criar hierarquia automática `estado > cidade` para consistência.
**Decisão:** As 4 taxonomias são independentes. Nenhum vínculo automático.
**Consequência:** Fluxo de Edição Rápida funciona perfeitamente com 4 caixas de checkbox independentes. Operador garante consistência manualmente (conhece cada empreendimento).

### DR-07: Duplicação de posts via plugin externo

**Data:** 2025-10
**Contexto:** Duplicar empreendimentos com estrutura similar é caso de uso real.
**Decisão:** Usar Yoast Duplicate Post (gratuito, consolidado). Não reimplementar no ImoGrifo.
**Consequência:** Menos código para manter. O menu "Duplicar" que aparece na listagem vem desse plugin, não do ImoGrifo.

### DR-08: Depreciar dynamic tags redundantes com Post Info nativo

**Data:** 2025-10
**Contexto:** `TagPostTerms` e `TagPostInfoCompact` foram criadas antes de descobrir que o widget Post Info do Elementor Pro já renderiza Terms com ícone, link, separador, etc.
**Decisão:** Manter apenas `TagPostTermsFiltered` (que agrega valor real com include/exclude/orderby). Depreciar `TagPostTerms` e `TagPostInfoCompact`.
**Consequência:** Plugin fica mais fino. Card Empreendimento usa 100% widgets nativos do Elementor Pro.
**Ação:** marcar como deprecated em Fase 1, remover em versão major futura.

### DR-09: Seeds de 27 UFs com limpeza manual pela agência

**Data:** 2025-10
**Contexto:** Considerado remover seed de UFs ou torná-lo opcional via botão.
**Decisão:** Manter seed das 27 UFs. Agência deleta manualmente as que o cliente não atende.
**Consequência:** Um momento de "limpeza inicial" em troca de não precisar cadastrar UF a UF. Aceito pelo operador principal.

### DR-10: Operador é a agência, não o cliente final

**Data:** 2025-10
**Contexto:** Decisões de UX do admin (simplicidade vs poder) dependem de quem usa.
**Decisão:** O plugin é operado pela agência. Cliente final nunca toca o admin.
**Consequência:** Admin prioriza eficiência de cadastro em lote (Edição Rápida, checkboxes, duplicação) em vez de "simplicidade para leigo". Não há esforço de onboarding ou tour-guide para usuários novos.

### DR-11: Hero e FormCTA depreciados — marcados para remoção

**Data:** 2026-05
**Contexto:** Inspeção visual dos templates reais em harmonia.grifo.agency (Card Empreendimento e single Morada dos Pássaros) confirmou que os widgets Hero e FormCTA nunca são usados em produção. O botão real do card usa Post URL nativo do Elementor. O hero da single é montado com Container + Imagem nativos do Elementor. Ambos os widgets duplicavam funcionalidade nativa do Elementor Pro, violando Filosofia #2.
**Decisão:** Depreciar imediatamente. Remover na Fase 2 (F2-04 e F2-05), junto com suas dependências: dynamic tags TagMetaCTALabel e TagMetaCTAUrl, e meta fields _imo_cta_label, _imo_cta_url, _imo_title, _imo_subtitle, _imo_hero_bg_id.
**Consequência:** Plugin fica mais fino. Supera a indefinição original de F2-04/F2-05 (que antes eram "investigar e decidir"). Meta fields órfãos no banco de dados de sites existentes não causam problema — são ignorados pelo WP.

### DR-12: Taxonomia `lotes` para quantidade de lotes por empreendimento

**Data:** 2026-08-03
**Contexto:** Operador pediu uma forma de registrar a quantidade de lotes de cada empreendimento (loteamentos, bairros planejados) sem precisar pré-cadastrar cada número numa tela separada antes — quer digitar o valor direto na Edição Rápida, na hora, olhando a lista de empreendimentos.
**Decisão:** Criar taxonomia `lotes`, **não-hierárquica** (`hierarchical: false`, estilo Tags), diferente das outras 4 taxonomias do plugin que são hierárquicas (estilo Categoria, checkbox). Essa escolha é proposital: taxonomias não-hierárquicas mostram um campo de texto livre na Edição Rápida, e o WordPress cria o termo automaticamente se o número digitado ainda não existir — sem precisar passar pela tela Empreendimentos → Lotes antes. Sem seed inicial. **Não é exposta como filtro** no widget Filters nem em `apply_archive_filters()` — existe só para exibição/organização administrativa, consumida no card via Post Info nativo do Elementor (Terms).
**Consequência:** Quinta taxonomia do plugin, e a primeira não-hierárquica. Não conflita com DR-03 (que trata de meta fields para dados quantitativos como preço/metragem/vagas) porque `lotes` é implementada como taxonomia, não como meta field — segue o raciocínio de DR-01 (taxonomia > post_meta para eixos administrados via Edição Rápida), adaptado pra permitir digitação livre em vez de checkbox. Se no futuro precisar virar filtro de verdade ou faixa numérica, reavaliar como campo estruturado — essa decisão cobre só o uso administrativo/exibição atual.

### DR-13: Link externo sobrescreve Post URL nativo por empreendimento

**Data:** 2026-08-05
**Contexto:** Cliente Nova Harmonia recusou usar LPs internas (construídas no próprio site via Elementor) por decisão comercial, sem motivo técnico. Passou a exigir vincular landing pages hospedadas em outros domínios para determinados empreendimentos. O botão "Veja mais" do Card Empreendimento usa Post URL nativo do Elementor, que resolve via `get_permalink()`.
**Decisão:** Criar meta field `_imo_external_link` (URL), editável via Edição Rápida na listagem de Empreendimentos. Filtrar `post_type_link` para o CPT `empreendimento`: quando o post tiver `_imo_external_link` preenchido, `get_permalink()` retorna essa URL; senão, comportamento padrão do WordPress.
**Consequência:** Qualquer lugar que use `get_permalink()`/`the_permalink()` para aquele post passa a resolver para a URL externa — inclui o botão do card (Post URL nativo), mas também `sitemap.xml`, canonical de SEO, link "Ver" no admin, RSS. Aceito conscientemente: é o comportamento desejado. A página interna do Elementor continua no banco e acessível por URL direta (a rota/rewrite não muda, só o que `get_permalink()` retorna como string), útil para edição futura, mas sem nenhum link automático apontando pra ela enquanto a LP externa estiver setada.
Não conflita com DR-11 (removeu `_imo_cta_url`, ligado ao widget FormCTA depreciado): motivo diferente — aquele era campo de um widget específico depreciado; este é override de permalink a nível de post, sem widget acoplado, e existe justamente porque o Post URL nativo é a fonte única de link do card (Filosofia #2 — não duplicar Elementor).

---

## Governança

### Como este documento evolui

Mudanças neste `ARCHITECTURE.md` exigem:

1. **Motivo documentado** — o porquê deve estar explícito em commit message, issue ou PR.
2. **Revisão da seção de Escopo** — nenhum item de "Fora de escopo" vira "Dentro de escopo" sem discussão.
3. **Registro em Decisões** — toda mudança de direção arquitetural vira nova DR-NN. DRs antigas permanecem como histórico.
4. **Atualização dos documentos dependentes** — `ROADMAP.md` e `CLAUDE.md` devem refletir as mudanças.

### Quando criar plugin complementar em vez de expandir este

Se a demanda futura exigir:

- Modelagem de **unidades** (apartamento, casa, lote individual)
- **Checkout** ou reserva online
- **CRM** / captura de leads
- **Multi-tenancy** (várias imobiliárias no mesmo site)
- **Integração com portais externos** (ZAP, VivaReal, OLX)
- **Geolocalização** / busca por raio

...a resposta padrão é **construir outro plugin** que complementa o ImoGrifo. O ImoGrifo tem escopo fechado. Sua utilidade vem da restrição.
