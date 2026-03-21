# Implementation Plan: UI/UX Refresh van de applicatie

**Branch**: `[001-ui-ux-refresh]` | **Date**: 2026-03-21 | **Spec**: `C:\Github\poule_v2\specs\001-ui-ux-refresh\spec.md`
**Input**: Feature specification from `C:\Github\poule_v2\specs\001-ui-ux-refresh\spec.md`

**Note**: This plan is produced by the `/speckit.plan` workflow for the active feature and stops after Phase 2 planning.

## Summary

Vernieuw de volledige server-rendered PHP interface van de applicatie zonder functionele wijzigingen door de bestaande theme-laag en module-markup te moderniseren met Bootstrap-patronen, geïnspireerd door `C:\Github\poule_v2\inapp-1.0.0`. De implementatie richt zich op het gedeelde pagineraamwerk, navigatie, formulieren, meldingen, lijsten/tabellen en standen/predictieschermen, terwijl routes, permissies, businesslogica, queryparameters, POST-velden, berekeningen en data-uitkomsten ongewijzigd blijven.

## Technical Context

**Language/Version**: PHP 8.4.6 target voor de applicatie; server-rendered HTML, CSS en beperkte JavaScript voor Bootstrap-interactie  
**Primary Dependencies**: Bootstrap 5.3.x voor layout/components, bestaande custom `Template`/`Menu`/`Component`-architectuur in `C:\Github\poule_v2\modules`, bestaande MySQL-backed applicatie, `C:\Github\poule_v2\inapp-1.0.0` als visuele referentie  
**Storage**: Bestaande MySQL-database `poule_v2`, sessies in filesystem onder `C:\Github\poule_v2\sessions`, statische templates/assets in repository  
**Testing**: Bestaande SimpleTest suites onder `C:\Github\poule_v2\tests\*_tests.php`, suite runner `C:\Github\poule_v2\tests\all_tests.php`, plus nieuw toe te voegen Playwright smoke/regression journeys voor primaire UI-stromen en responsiviteit  
**Target Platform**: Shared LAMP-hosting, desktop/tablet/mobiele browsers, Windows-based development workflow met deployment naar server-rendered PHP hosting  
**Project Type**: Legacy server-rendered PHP webapplicatie met theming via templatebestanden en module-specifieke `.tpl.php` bestanden  
**Performance Goals**: Geen merkbare regressie in laadtijd of interactie onder shared-hosting; navigatie, formulieren en overzichtsschermen blijven snel scanbaar en bruikbaar op desktop/tablet/mobiel; geen nieuwe build-stap vereist voor runtime  
**Constraints**: Geen functionele wijzigingen; geen database-schemawijzigingen; geen wijziging aan permissies/berekeningen/workflows; Bootstrap en PHP 8.4.6 zijn expliciet gekozen; gebruik `C:\Github\poule_v2\inapp-1.0.0` als designreferentie; compatibel houden met shared hosting en server-side rendering; absolute paden gebruiken in plan-artifacts  
**Scale/Scope**: Alle bestaande gebruikersgerichte schermen die via `C:\Github\poule_v2\index.php` en modules bereikbaar zijn, inclusief gedeelde layout in `C:\Github\poule_v2\templates\orange`, module-templates onder `C:\Github\poule_v2\modules\*\*.tpl.php`, en inline HTML-hotspots in module classes en entrypoint-logica

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### Pre-Phase 0 Gate Review

| Principle | Status | Notes |
|-----------|--------|-------|
| Test-First (NON-NEGOTIABLE) | PASS | Implementatie wordt gepland met tests-first aanpak: bestaande SimpleTest regressies blijven leidend en er wordt vóór UI-refactor aanvullende markup/regression coverage plus Playwright-journeys toegevoegd voor primaire gebruikersstromen. |
| Minimal Shared Hosting Compatibility | PASS | De gekozen aanpak behoudt server-side rendering, gebruikt Bootstrap zonder verplichte build pipeline in productie, en introduceert geen privileged services of extra extensies. |
| Security and Data Protection | PASS | Scope is presentatie-only; server-side validatie, escaping, sessies en bestaande state-changing flows blijven intact en mogen niet functioneel worden aangepast. |
| Documentation-as-Code | PASS | Plan-, research-, data-model- en quickstart-artifacts zijn vastgelegd onder `C:\Github\poule_v2\specs\001-ui-ux-refresh`; er is geen runtime- of migratiewijziging die nu een extra `/docs`-update afdwingt. |
| Simplicity & Performance | PASS | Gekozen is voor incrementele template- en markup-modernisering boven een rewrite of aparte frontend, zodat de oplossing auditbaar en rollback-vriendelijk blijft. |

**Gate Result**: PASS — geen constitutionele violations of open clarifications.

### Post-Phase 1 Design Re-Check

| Principle | Status | Notes |
|-----------|--------|-------|
| Test-First (NON-NEGOTIABLE) | PASS | `C:\Github\poule_v2\specs\001-ui-ux-refresh\quickstart.md` schrijft voor om eerst regressie- en journey-tests op te zetten voordat layout/templates worden aangepast. |
| Minimal Shared Hosting Compatibility | PASS | Het design gebruikt de bestaande `templates\orange` themelaag en module markup; er is geen aparte SPA, build-service of backend wijziging nodig. |
| Security and Data Protection | PASS | Het data model bevat uitsluitend presentatielaag-entiteiten; formulieren behouden hun bestaande action/method/input contracten. |
| Documentation-as-Code | PASS | Phase 0/1 artifacts zijn vastgelegd onder de feature-spec map; er zijn geen extra publieke contracten nodig voor deze interne presentatievernieuwing. |
| Simplicity & Performance | PASS | Het ontwerp kiest voor gefaseerde aanpassing van shared shell -> module templates -> inline HTML hotspots, wat eenvoudiger is dan een volledige herschrijving. |

**Re-Check Result**: PASS — design blijft in lijn met de constitution.

## Project Structure

### Documentation (this feature)

```text
C:\Github\poule_v2\specs\001-ui-ux-refresh\
├── plan.md              # Dit implementatieplan
├── research.md          # Phase 0 onderzoek en technische besluiten
├── data-model.md        # Phase 1 presentatiemodel en relaties
├── quickstart.md        # Phase 1 implementatie- en validatiestappen
└── tasks.md             # Phase 2 output van /speckit.tasks (nog niet aangemaakt)
```

**Contracts Decision**: Geen `contracts\` artifact aangemaakt voor deze feature. De applicatie exposeert voor deze wijziging geen nieuwe of gewijzigde externe API, CLI, schema of protocol; de scope blijft beperkt tot interne server-rendered UI-presentatie.

### Source Code (repository root)

```text
C:\Github\poule_v2\
├── index.php
├── config.cfg.php
├── languages\
├── modules\
│   ├── component.class.php
│   ├── menu.class.php
│   ├── template.class.php
│   ├── competitions\
│   ├── games\
│   ├── participants\
│   ├── predictions\
│   ├── table\
│   ├── usercontrol\
│   └── ... overige domeinmodules met `.tpl.php` schermmarkup
├── templates\
│   ├── orange\
│   │   ├── index.tpl.php
│   │   └── template.css
│   ├── default\
│   └── default1\
├── tests\
│   ├── all_tests.php
│   ├── *_tests.php
│   ├── mock\
│   └── simpletest\
├── sessions\
└── inapp-1.0.0\
    ├── package.json
    └── src\
```

**Structure Decision**: Dit blijft een single-project, server-rendered PHP webapplicatie. De implementatie concentreert wijzigingen in de gedeelde theme-shell (`C:\Github\poule_v2\templates\orange`), module-templatebestanden onder `C:\Github\poule_v2\modules`, en een kleine set inline HTML-genererende classes/entrypoints waar de markup vandaag nog in PHP strings zit. Er wordt geen aparte frontend/backendlijn of nieuw deployable project toegevoegd.

## Implementation Strategy

1. **Shared application shell vernieuwen**  
   Moderniseer `C:\Github\poule_v2\templates\orange\index.tpl.php` en `template.css` zodat topbar, hero, sidebar, content panel en footer consistent aansluiten op de Bootstrap-gebaseerde referentie uit `C:\Github\poule_v2\inapp-1.0.0`.

2. **Navigatie en contextconsistentie borgen**  
   Houd de bestaande `Menu`- en componentrechten intact, maar render navigatie, actieve status, account-acties en contextpanelen uniform over hoofd- en submodules.

3. **Formulieren en meldingen herstructureren zonder gedragswijziging**  
   Werk de bestaande `.tpl.php` formulierpagina's en validatie-/statuspresentatie bij zodat labels, veldgroepering, primaire/secundaire acties en foutmeldingen visueel consistent worden, terwijl form actions, methodes en inputnamen gelijk blijven.

4. **Tabellen, standen en prediction-overzichten beter scanbaar maken**  
   Vervang verouderde presentatietabellen waar mogelijk door Bootstrap-conforme wrappers/patronen met behoud van dezelfde datasets, kolommen, sorteerlogica en actie-links.

5. **Inline HTML-hotspots minimaliseren**  
   Breng alleen daar waar nodig structurele markup vanuit `index.php` of module classes over naar beter beheersbare templateconstructies; businesslogica en data-opbouw blijven op hun huidige plaats zolang dat functioneel veiliger is.

6. **Test-first regressiecontrole toevoegen**  
   Leg eerst journeys en markup-regressies vast voor login/contextnavigatie, formulieren en standen/predicties, zodat UI-vernieuwing aantoonbaar zonder functionele regressie kan worden uitgerold.

## Phase 0 Research Summary

Onderzoek vastgelegd in `C:\Github\poule_v2\specs\001-ui-ux-refresh\research.md` heeft alle technische open vragen opgelost. Belangrijkste uitkomsten:

- Bootstrap 5.3.x via bestaande/lightweight integratie is passend voor shared hosting en sluit aan op de referentietemplate.
- De juiste implementatielaag is de bestaande `orange` theme plus module templates, niet een losstaande frontend of Vite-build uit `inapp-1.0.0`.
- Presentatie-only guardrails moeten expliciet afdwingen dat queryparameters, actions, POST-velden, permissies, berekeningen en databetekenis onveranderd blijven.
- Playwright is nodig voor constitutionele journeydekking; bestaande SimpleTest suites blijven nuttig voor regressies in server-rendered output en businesslogica.

## Phase 1 Design Summary

- `C:\Github\poule_v2\specs\001-ui-ux-refresh\data-model.md` beschrijft de presentatielaag-entiteiten die tijdens implementatie consistent gehouden moeten worden.
- `C:\Github\poule_v2\specs\001-ui-ux-refresh\quickstart.md` beschrijft de uitvoerbare implementatiestappen inclusief test-first volgorde.
- `contracts\` is bewust niet aangemaakt omdat deze feature geen externe interface verandert.

## Phase 2 Planning Outcome

De feature is klaar voor `/speckit.tasks`. Verwachte uitvoeringsvolgorde voor taakgeneratie:

1. Baseline journeys en regressietests vastleggen.
2. Shared shell (`templates\orange`) en navigatie aanpassen.
3. Formulier- en meldingspagina's moderniseren.
4. Overzichten/standen/predictieschermen moderniseren.
5. Inline HTML-hotspots opruimen waar nodig.
6. Responsive en accessibility regressies valideren op desktop/tablet/mobiel.

## Complexity Tracking

Geen constitutionele violations; deze sectie blijft leeg.
