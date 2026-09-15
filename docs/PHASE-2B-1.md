# Finder — Phase 2B.1

## Purpose

Phase 2B.1 introduces the internal foundation for the future Section → Container → Element builder model without replacing the existing `SiteSection` persistence model or renderer.

## Registries

`ElementRegistry` is the source of truth for the initial Document v2 elements:

- `heading`
- `text`
- `image`
- `button`
- `divider`
- `spacer`

It exposes `all()`, `get()`, `has()`, `types()` and `defaults()` and contains metadata, parent rules, defaults and lightweight schema information. It performs no database work.

`SectionRegistry` registers the current Finder section types and exposes `all()`, `get()`, `has()` and `types()`. It describes the existing sections rather than converting them to elements.

## Document v2

The opt-in document contract is:

```text
Document
 ├─ schema_version: 2
 └─ nodes[]
     └─ container
         ├─ id: cnt_xxxxxxxxxxxxxxxx
         ├─ settings: {}
         └─ children[]
             └─ element
                 ├─ id: el_xxxxxxxxxxxxxxxx
                 ├─ type
                 ├─ content: {}
                 └─ settings: {}
```

Root nodes must be containers. Containers contain elements. Elements cannot contain children and containers cannot be nested. Element types must exist in `ElementRegistry`.

## Node IDs

`BuilderNodeId` generates random identifiers using Laravel's `Str::random()`. IDs are independent from SQL IDs and array indexes:

- containers: `cnt_` + 16 lowercase alphanumeric characters;
- initial elements: `el_` + 16 lowercase alphanumeric characters.

Normalization preserves existing valid IDs. Missing IDs are generated. Duplicate IDs are rejected by validation.

## Normalization and validation

`BuilderDocument::normalize()` only accepts Document v2. It does **not** auto-convert legacy `SiteSection` data. This keeps the new model opt-in and prevents accidental destructive migrations.

Normalization:

- applies element defaults when content/settings are absent;
- guarantees arrays for structural fields;
- generates missing node IDs;
- preserves valid user data and existing IDs;
- rejects impossible structures instead of silently deleting data.

`BuilderDocument::validate()` verifies schema version, node shape, ID format and uniqueness, parent/child rules, registered types, content/settings array shape and registry enum constraints. `isValid()` is the non-throwing convenience API.

## Legacy compatibility

Existing `SiteSection` records remain unchanged:

```text
SiteSection
 ├─ type
 ├─ content
 ├─ settings
 ├─ sort_order
 └─ is_visible
```

No migration, model rewrite or renderer replacement is part of Phase 2B.1. The existing Builder and public renderer continue to own legacy sections. A later phase can introduce an explicit adapter or document-backed editing path without forcing every existing website through a database migration.

## Adding an element

1. Add the type and definition to `ElementRegistry`.
2. Define its defaults, allowed parent(s) and lightweight schema.
3. Add registry and validation tests.
4. Implement editor/renderer integration in the later phase dedicated to element UX.

Do not add database queries to the registry and do not use filesystem discovery at request time.

## Adding a section

Register the existing section type in `SectionRegistry`. Do not remove or rewrite its current `SiteSection` storage or renderer implementation as part of registry work.
