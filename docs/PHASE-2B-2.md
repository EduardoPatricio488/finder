# Finder — Phase 2B.2

## Objetivo

Camada conservadora entre `SiteSection` legacy e Document v2, sem alterar o Builder, renderer, publicação, AI ou persistência existente.

## Integração

`SiteSectionDocument::fromSection()` produz um documento v2 validado em memória. O payload legacy completo fica em `settings.legacy`, preservando campos sem representação conhecida.

`toSectionData()` valida o documento com `BuilderDocument` e devolve dados de `SiteSection` sem queries.

## IDs

Sections persistidas usam o ID SQL como seed determinístico de `BuilderNodeId::stable()`. Elementos usam o ID estável do container e uma chave semântica.

## Compatibilidade

Tipos conhecidos usam `SectionRegistry`; tipos desconhecidos são preservados. `ElementRegistry` continua a ser a fonte de verdade dos seis elementos base. Não há migration, `site_elements` ou reescrita de dados.

## Limitações

O formato legacy continua a ser a fonte de verdade para o editor e renderer. A persistência element-level e a integração do UI ficam para a Phase 2B.3.

> Nota: esta camada mantém compatibilidade durante a evolução do editor.
