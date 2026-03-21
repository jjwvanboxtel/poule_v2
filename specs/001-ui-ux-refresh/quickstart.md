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
