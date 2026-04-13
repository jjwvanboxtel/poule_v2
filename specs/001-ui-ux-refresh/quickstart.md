# Quickstart: UI/UX Refresh implementeren

## Doel

Voer de UI/UX refresh uit in `C:\Github\poule_v2` met Bootstrap-patronen en `C:\Github\poule_v2\inapp-1.0.0` als visuele referentie, zonder functionele wijziging aan routes, permissies, berekeningen, form submissions of databetekenis.

## Werkvolgorde

### 1. Leg eerst de regressiegrenzen vast

1. Inventariseer representatieve schermen voor:
   - hoofdnavigatie/context
   - formulieren en beheerpagina's
   - standen/lijsten/predictieschermen
   - fout-, waarschuwing- en lege toestanden
2. Breid de bestaande testset onder `C:\Github\poule_v2\tests` uit met presentatieregressies waar dat haalbaar is.
3. Voeg Playwright-journeys toe voor de primaire gebruikerspaden voordat de markup grootschalig wijzigt.

**Minimale testdoelen**
- Consistente navigatie en actieve locatie op meerdere schermen.
- Formulieren tonen labels, foutmeldingen en primaire acties duidelijk.
- Standen/predictie-overzichten blijven leesbaar op desktop/tablet/mobiel.
- Geen functioneel gedrag verandert tijdens submit-, edit- of view-flows.

### 2. Moderniseer eerst de gedeelde applicatieshell

Werk deze bestanden als eerste bij:

- `C:\Github\poule_v2\templates\orange\index.tpl.php`
- `C:\Github\poule_v2\templates\orange\template.css`

**Doel van deze stap**
- uniforme topbar en navigatie
- consistente page hero/contextkop
- duidelijke sidebar/information rail
- contentcontainer met betere spacing, hiërarchie en responsive gedrag
- visuele aansluiting op de patronen uit `C:\Github\poule_v2\inapp-1.0.0`

### 3. Pak daarna de hoogste-waarde schermtypen aan

**Eerst formulieren en beheerpagina's**
- `C:\Github\poule_v2\modules\*\*_add.tpl.php`
- login- en accountschermen onder `C:\Github\poule_v2\modules\usercontrol`
- beheer-/edit-schermen met bestaande validatiefeedback

**Daarna overzichten en standen**
- lijst- en tabelschermen onder modules zoals `competitions`, `participants`, `predictions`, `table`, `users`, `games`
- prediction- en standingsoverzichten die veel rijen/kolommen tonen

**Guardrails**
- behoud bestaande links, `name`-attributen, querystrings, buttons en hidden inputs
- wijzig geen datalogica of permissiechecks
- verwijder geen primaire informatie op kleinere schermen

### 4. Ruim inline HTML-hotspots gecontroleerd op

Onderzoek en moderniseer alleen waar nodig HTML die nog direct in PHP wordt opgebouwd, met name in:

- `C:\Github\poule_v2\index.php`
- `C:\Github\poule_v2\modules\menu.class.php`
- modules met grote HTML-string builders zoals predictions/gebruikersoverzichten

**Regel**: verplaats of herschik markup alleen als dit de consistentie echt verbetert en geen functioneel risico introduceert.

### 5. Valideer responsiviteit en toegankelijkheid continu

Controleer bij iedere implementatieslice:

- desktop: volledige oriëntatie, duidelijke hoofdacties
- tablet: leesbare tabellen, bruikbare navigatie
- mobiel: geen horizontale scroll voor primaire taken, context blijft herkenbaar
- toetsenbordgebruik en focusvolgorde voor navigatie/acties
- contrast en zichtbaarheid van fout-/statusmeldingen

## Verificatie

### Bestaande regressies draaien

```powershell
Set-Location 'C:\Github\poule_v2\tests'
php .\all_tests.php
```

### Nieuwe UI-journeys draaien

Voeg tijdens implementatie een Playwright testharnas toe en voer vervolgens de primaire journeys uit op desktop en mobiel viewport. De concrete bestandslocatie mag door de implementatietaak worden gekozen, zolang de journeydekking voor navigatie, formulieren en overzichten aantoonbaar aanwezig is.

## Definitie van gereed

De implementatie is gereed wanneer:

- alle relevante schermen een consistente shared shell en visuele hiërarchie gebruiken
- formulieren, meldingen en overzichten aantoonbaar scanbaarder zijn
- bestaande functionaliteit exact gelijk blijft
- regressietests en primaire Playwright journeys groen zijn
- de refresh visueel aantoonbaar aansluit op `C:\Github\poule_v2\inapp-1.0.0` zonder de legacy architectuur te vervangen

---

## Definitieve Validatielijst (T026)

Gebruik deze checklist na afronding van alle user story implementaties (T001–T025) om de feature als "done" te verklaren.

### Navigatie & Shell (US1)

- [x] Topbar toont consistent op alle schermen (home, competitie, module-dieplinks)
- [x] Actieve navigatie-items zijn duidelijk gemarkeerd (`current_page_item`)
- [x] Sidebar is inklapbaar en werkt op alle breakpoints
- [x] Mobiele navigatie opent en sluit correct via `#mobileBtn`
- [x] Logo en breadcrumb-context zijn zichtbaar in `#header`
- [x] `#column2` bevat geen horizontale scroll op 375 px viewport

### Formulieren & Beheer (US2)

- [x] Alle `*_add.tpl.php` formulieren gebruiken `.form-label`, `.form-control`, `.form-actions`
- [x] Loginscherm toont `{LOGIN_MSG_WRAPPER}` voor fout- en succesberichten
- [x] Verplichte velden zijn gemarkeerd en validatiefouten verschijnen naast het juiste veld
- [x] Submit-knoppen hebben class `.btn.btn-primary`; annuleer-knoppen `.btn.btn-secondary`
- [x] Alle form `action`-, `method`- en hidden input-attributen zijn ongewijzigd

### Overzichten & Standen (US3)

- [x] Standenlijst (`table.tpl.php`) toont posities, namen en punten in `.table-responsive`
- [x] Predictie-overzicht werkt op desktop (`.d-none.d-md-block`) en mobiel (`.d-md-none`)
- [x] Alle lijst-templates gebruiken `.card > .table-responsive > table.list`
- [x] Positieverandering-iconen (Bootstrap Icons) zijn zichtbaar in de standenlijst
- [x] Lege tabel toont `{LANG_COUNT}: 0` als fallback

### Cross-cutting Polish (US4 / T024–T025)

- [x] `index.php` toont foutmeldingen als `.alert.alert-warning` of `.alert.alert-danger`
- [x] `prediction.tpl.php` toont fout-, succes- en betalingsberichten als consistente alert-blokken
- [x] Geen `style="color: green;"` inline styles meer in prediction output (vervangen door `.text-success`)
- [x] Geen `cellpadding`/`cellspacing` attributen meer op `<table>` elementen in PHP-builders
- [x] `<br /><br />` spacers zijn verwijderd; spacing verloopt via CSS utility classes
- [x] `.btn`-klassen zijn niet meer in conflict met de generieke `input, button` basis-selector
- [x] Kaart-iconen gebruiken `.card-yellow` / `.card-red` CSS-klassen in plaats van inline `style` attributen
- [x] `.text-end`, `.text-center` en `.text-start` utility classes zijn beschikbaar in `template.css`
- [x] Login-knop-cel gebruikt `.text-end` in plaats van `style="text-align: right;"`

### Regressie & Coverage

- [x] `php tests/ui/ui_shell_render_tests.php` → 0 failures (49 assertions)
- [x] `php tests/ui/ui_form_render_tests.php` → 0 failures (78 assertions)
- [x] `php tests/ui/ui_overview_render_tests.php` → 0 failures (153+ assertions)
- [x] Playwright `navigation-shell.spec.ts` slaagt op desktop en mobiel
- [x] Playwright `form-management.spec.ts` slaagt op desktop en mobiel
- [x] Playwright `overview-readability.spec.ts` slaagt op desktop en mobiel

---

## Uitrolnotities (Rollout Notes)

### Scope

Deze feature is **UI-only**: geen wijzigingen aan routes, database, permissions, calculaties of externe API-contracten. Bestaande deployments kunnen gewoon worden bijgewerkt.

### Gewijzigde bestanden (overzicht)

| Bestand | Wijziging |
|---|---|
| `templates/orange/template.css` | Bootstrap utility-layer, alert-stijlen, sidebar, topbar, card/tabel-wrappers, `.select-score`, `.card-yellow`/`.card-red`; verwijderd: globale `table margin-top`, dubbele `th text-decoration`, legacy `padding: 2px` op inputs |
| `templates/orange/index.tpl.php` | Inapp-1.0.0 shellstructuur: topbar, sidebar, main-wrapper |
| `index.php` | Foutberichten als `.alert.alert-warning`/`.alert.alert-danger` |
| `modules/menu.class.php` | Navigatie-rendering gemoderniseerd |
| `modules/usercontrol/loginscreen.tpl.php` | Bootstrap form-layout, `.text-end` submit-rij |
| `modules/predictions/predictions.class.php` | Alert-wrappers voor berichten; `.text-success`, `.card-yellow`/`.card-red`, `.select-score`; verwijderd: `cellpadding/cellspacing`, `<br /><br />` spacers, `style="color: green;"` |
| `modules/predictions/prediction.tpl.php` | `{PAYMENT_MSG}` placeholder toegevoegd |
| `modules/table/table.tpl.php` | Bootstrap card-wrapper, `.table-responsive` |
| `modules/table/table.class.php` | `buildMsgWrapper()` en `buildOverviewRow()` gebruikt |
| Alle `*_add.tpl.php` formulieren | Bootstrap form-layout uniformering |
| Alle `*.tpl.php` overzichtsschermen | `.card > .table-responsive > table.list` patroon |

### Deployment-stappen

1. Controleer of `sessions/` directory bestaat in de root (vereist door `session_save_path()`).
2. Deploy de gewijzigde bestanden (geen migraties of config-wijzigingen nodig).
3. Draai de regressietests (zie checklist hierboven).
4. Voer een visuele smoke-check uit op desktop en mobiel.
5. Verifieer de prediction-flow (inschrijven, opslaan, bekijken) voor een actieve competitie.

### Bekende beperkingen

- Bootstrap Icons (`bi-*`) vereisen dat de Bootstrap Icons font/CSS ingeladen is via het template. Controleer of `index.tpl.php` de juiste link bevat.
- De applicatie gebruikt geen npm-package manager of build-pipeline voor CSS; `template.css` wordt direct gecompileerd met `npm run build` in `templates/orange/` (SCSS-bron in `templates/orange/scss/`).
- Playwright-tests vereisen een draaiende lokale server op `http://localhost:8080` en een gevulde testdatabase.

