# Research: UI/UX Refresh van de applicatie

## Context

Deze feature vernieuwt uitsluitend de presentatie van de bestaande server-rendered PHP-applicatie in `C:\Github\poule_v2` en gebruikt `C:\Github\poule_v2\inapp-1.0.0` als visuele referentie. De gebruiker heeft expliciet gekozen voor Bootstrap en PHP 8.4.6, terwijl de constitution shared-hosting-compatibiliteit, server-side rendering en een test-first werkwijze afdwingt.

## Decision 1: Gebruik de bestaande `templates\orange` themelaag als implementatiebasis

**Decision**: Implementeer de UI-refresh bovenop `C:\Github\poule_v2\templates\orange\index.tpl.php` en `C:\Github\poule_v2\templates\orange\template.css`, en map de layoutpatronen uit `C:\Github\poule_v2\inapp-1.0.0` naar de bestaande placeholders `{MENU}`, `{LOGIN}`, `{INFORMATION}`, `{CONTENT}`, `{LOGO}` en `{SUB_TITLE}`.

**Rationale**: De actieve configuratie verwijst al naar template `orange`, deze template laadt al Bootstrap en vormt de veiligste plek om een consistente shell voor topbar, hero, sidebar en content te creëren. Deze aanpak minimaliseert de blast radius en houdt routing, permissies en modulelogica buiten schot.

**Alternatives considered**:
- Een volledig nieuwe template naast `orange`: verworpen omdat dit meer configuratie- en migratierisico introduceert.
- Een losse frontend bouwen vanuit `inapp-1.0.0`: verworpen omdat dit niet past bij de server-rendered architectuur en shared-hosting constraints.

## Decision 2: Gebruik Bootstrap 5.3.x op een shared-hosting-vriendelijke manier

**Decision**: Gebruik Bootstrap 5.3.x als runtime UI-framework via de bestaande lichte integratie, met lokale template-overrides voor applicatiespecifieke stijl, en houd de feature compatibel met PHP 8.4.6 zonder verplichte build-pipeline voor productie.

**Rationale**: Dit sluit direct aan op de gebruikersinstructie, op de dependencyversie van de referentietemplate en op de constitutionele eis om shared-hosting-compatibel te blijven. Het voorkomt dat een presentatie-update afhankelijk wordt van Node/Vite tijdens deployment.

**Alternatives considered**:
- Bootstrap assets volledig vanuit een nieuw buildproces genereren: verworpen wegens onnodige complexiteit voor deze legacy webapp.
- Alleen custom CSS zonder Bootstrap: verworpen omdat dit minder consistent aansluit op de gevraagde referentiestijl en minder herbruikbare componentpatronen oplevert.

## Decision 3: Voer de modernisering incrementeel uit: shared shell -> module templates -> inline HTML hotspots

**Decision**: Verdeel de implementatie in drie lagen: eerst de gedeelde shell (`templates\orange`), daarna module `.tpl.php` bestanden voor formulieren en overzichten, en pas als laatste de inline HTML-fragmenten in `index.php` en specifieke module classes waar dat nodig is voor consistente markup.

**Rationale**: HTML leeft in deze codebase verspreid over templates en PHP string builders. Een gefaseerde aanpak maakt het mogelijk om zichtbare winst te leveren zonder een risicovolle wholesale rewrite van oudere modules en houdt terugdraaien eenvoudig.

**Alternatives considered**:
- Alles in één keer herschrijven: verworpen wegens te hoog regressierisico.
- Alleen CSS aanpassen zonder markup-opruiming: verworpen omdat formulieren, tabellen en navigatie dan onvoldoende consistent en responsive worden.

## Decision 4: Handhaaf strikte presentatie-only guardrails

**Decision**: Tijdens implementatie blijven queryparameters, form actions, HTTP-methods, inputnamen, permissies, businessregels, berekeningen, validatiebetekenis, navigatiedoelen en databasegebruik exact functioneel gelijk.

**Rationale**: De featurebeschrijving, assumptions en constitution laten geen functionele wijziging toe. De veiligste manier om dat af te dwingen is de UI-refresh expliciet te modelleren als markup-, layout- en stylingwerk bovenop bestaand gedrag.

**Alternatives considered**:
- Gebruikersstromen vereenvoudigen of herschikken: verworpen omdat dit functionele verandering zou zijn.
- Navigatie-items of acties semantisch herdefiniëren: verworpen omdat de scope uitsluitend presentatie betreft.

## Decision 5: Gebruik test-first regressiecontrole met SimpleTest én Playwright

**Decision**: Behoud de bestaande SimpleTest suites voor businesslogica- en server-rendered regressies en voeg vóór de UI-wijzigingen journeygerichte Playwright-dekking toe voor primaire gebruikerspaden zoals navigatie/oriëntatie, formulieren/beheeracties en standen/predictie-overzichten op meerdere schermgroottes.

**Rationale**: De constitution vereist expliciet test-first en Playwright-validatie voor primaire web-UI journeys. Omdat de codebase al SimpleTest kent, is de combinatie van bestaande regressies plus nieuwe browsertests de meest pragmatische safety net.

**Alternatives considered**:
- Alleen handmatige QA: verworpen omdat dit niet aan de constitution voldoet.
- Alleen SimpleTest markup assertions: verworpen omdat responsiviteit, visuele oriëntatie en primaire journeys dan onvoldoende gedekt zijn.

## Decision 6: Modelleer formulieren, tabellen, navigatie en meldingen als herbruikbare presentatietypen

**Decision**: Gebruik in de implementatie vaste UI-patronen voor vier dominante schermtypen: navigatiestructuur, formulier-/bewerkscherm, gegevensoverzicht/standen en statusmelding. Map bestaande modulepagina's naar deze patronen in plaats van per scherm een unieke stijloplossing te maken.

**Rationale**: De feature draait om consistentie en scanbaarheid over veel bestaande modules heen. Herbruikbare patronen verlagen implementatiekosten, verbeteren onderhoudbaarheid en maken regressietests concreter.

**Alternatives considered**:
- Elk scherm los restylen: verworpen omdat dit inconsistentie in stand houdt.
- Alleen de homepage/hero vernieuwen: verworpen omdat de spec alle bestaande gebruikersgerichte schermen in scope plaatst.

## Decision 7: Geen externe contracts-artifacts voor deze feature

**Decision**: Maak geen `contracts\` artifact voor deze plan-run.

**Rationale**: Deze feature wijzigt geen publieke API, geen CLI-commando's, geen integratieprotocol en geen extern schema. De interface die verandert is uitsluitend de interne server-rendered presentatie van bestaande pagina's.

**Alternatives considered**:
- DOM-contracten documenteren als publieke interface: verworpen omdat dit voor deze interne refactor geen stabiele externe contractlaag is.
- Een leeg contracts-directory aanmaken: verworpen omdat dit geen waarde toevoegt en de plan-uitkomst minder scherp maakt.

## Decision 8: Los alle technische clarifications af binnen het plan

**Decision**: Er blijven geen `NEEDS CLARIFICATION`-items open. Belangrijke technische keuzes zijn vastgezet op: PHP 8.4.6, Bootstrap 5.3.x, shared server-rendered PHP, `orange` als implementatietheme, `inapp-1.0.0` als referentie, en test-first met SimpleTest + Playwright.

**Rationale**: De gebruiker heeft de belangrijkste stackkeuzes al expliciet vastgelegd en codebase-analyse bevestigt de juiste integratiepunten. Hierdoor kan Phase 1 design direct concreet worden zonder open technische blockers.

**Alternatives considered**:
- Stackkeuzes uitstellen tot implementatie: verworpen omdat dat takenplanning vaag en risicovol maakt.
- Extra architectuurspike voor een nieuwe frontend-laag: verworpen omdat die niet nodig is voor deze feature.
