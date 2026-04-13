# Data Model: UI/UX Refresh van de applicatie

## Scope Note

Deze feature introduceert geen nieuwe persistente domeinmodellen of database-tabellen. Het onderstaande model beschrijft de presentatielaag-entiteiten die consistent moeten blijven tijdens de UI/UX refresh.

## Entity: ScreenTemplate

**Beschrijving**: Een gedeelde pagineraamwerkdefinitie voor schermen die via `C:\Github\poule_v2\index.php` worden gerenderd.

**Fields**
- `templateName` (string) — actieve themenaam, initieel `orange`
- `pageTitle` (string) — hoofdschermtitel
- `subTitle` (string|null) — contextuele subtitel zoals competitienaam
- `heroRegion` (boolean) — bepaalt of hero/header-context zichtbaar is
- `sidebarRegion` (boolean) — bepaalt of informatierail zichtbaar is
- `contentRegion` (string) — hoofdcontent placeholder
- `footerText` (string) — copyright/footertekst
- `responsiveBehavior` (enum: desktop, tablet, mobile)

**Validation Rules**
- Moet de bestaande placeholders `{TITLE}`, `{SUB_TITLE}`, `{LOGO}`, `{INFORMATION}`, `{CONTENT}` en `{COPYRIGHT}` blijven ondersteunen.
- Mag geen functionele routing- of permissielogica bevatten.

## Entity: NavigationItem

**Beschrijving**: Een zichtbaar hoofd- of subnavigatie-item dat uit de bestaande menu/component-structuur wordt opgebouwd.

**Fields**
- `componentId` (int|string)
- `label` (string)
- `href` (string)
- `isActive` (boolean)
- `isVisible` (boolean)
- `group` (enum: primary, secondary, account, contextual)
- `requiresCompetitionContext` (boolean)

**Validation Rules**
- `href` moet bestaande queryparameter-structuren intact laten.
- Slechts de visuele presentatie mag wijzigen; zichtbaarheid blijft afhankelijk van bestaande rechten/context.
- Een actief item moet duidelijk herkenbaar zijn zonder het navigatiedoel te veranderen.

## Entity: FormScreen

**Beschrijving**: Een bestaand invoer- of bewerkscherm dat nu vaak table-based markup gebruikt.

**Fields**
- `moduleName` (string)
- `actionUrl` (string)
- `method` (string)
- `fieldGroups` (collection of FieldGroup)
- `primaryActionLabel` (string)
- `secondaryActions` (collection of ActionLink)
- `statusMessages` (collection of StatusMessage)

**Validation Rules**
- `actionUrl`, `method`, inputnamen en hidden fields blijven ongewijzigd.
- Verplichte velden, validatiemeldingen en bestaande submit-acties moeten zichtbaar blijven.
- Primaire actie moet altijd visueel herkenbaar blijven op desktop, tablet en mobiel.

## Entity: FieldGroup

**Beschrijving**: Een logische groep invoervelden binnen een FormScreen.

**Fields**
- `legend` (string|null)
- `fields` (collection of Field)
- `layoutVariant` (enum: single-column, two-column, full-width)

**Validation Rules**
- Groepering mag scanbaarheid verbeteren, maar niet de invoervolgorde of gegevensbetekenis wijzigen.
- Velden met bestaande foutcontext moeten dicht bij hun melding blijven.

## Entity: Field

**Beschrijving**: Een bestaand inputcontrol binnen een formulier.

**Fields**
- `name` (string)
- `label` (string)
- `controlType` (enum: text, textarea, select, checkbox, radio, hidden, file, date, number, custom)
- `isRequired` (boolean)
- `helpText` (string|null)
- `errorState` (boolean)
- `currentValue` (mixed render value)

**Validation Rules**
- `name` en server-side interpretatie blijven exact gelijk.
- Presentatie van error/required states moet consistenter worden, niet inhoudelijk anders.

## Entity: DataOverview

**Beschrijving**: Een lijst-, tabel-, stand- of predictie-overzichtsscherm.

**Fields**
- `moduleName` (string)
- `title` (string)
- `columns` (collection of OverviewColumn)
- `rows` (collection of OverviewRow)
- `summaryBlocks` (collection of SummaryBlock)
- `rowActions` (collection of ActionLink)
- `emptyState` (StatusMessage|null)

**Validation Rules**
- Kolomvolgorde en betekenis mogen alleen veranderen als de functionele interpretatie identiek blijft.
- Kerninformatie moet op kleinere schermen zichtbaar blijven zonder noodzakelijke acties te verwijderen.

## Entity: StatusMessage

**Beschrijving**: Een fout-, waarschuwing-, bevestigings- of lege toestand in de UI.

**Fields**
- `type` (enum: error, warning, success, info, empty)
- `messageText` (string)
- `contextRegion` (enum: global, form, table, sidebar, hero)
- `isDismissible` (boolean)
- `source` (enum: server-rendered, validation, workflow, empty-state)

**Validation Rules**
- Bestaande boodschapinhoud en betekenis blijven gelijk.
- Visuele stijl moet typeherkenning verbeteren en mag statusinterpretatie niet veranderen.

## Entity: VisualTokenSet

**Beschrijving**: De gedeelde visuele ontwerpkeuzes die de refresh consistent maken.

**Fields**
- `colorPalette` (primary, surface, border, text, muted)
- `spacingScale` (xs, sm, md, lg, xl)
- `typographyScale` (page title, section title, body, helper)
- `radiusScale` (small, medium, large)
- `shadowScale` (none, subtle, raised)
- `breakpoints` (mobile, tablet, desktop)

**Validation Rules**
- Tokens moeten consistent toepasbaar zijn over templates en modulepagina's.
- Contrast en leesbaarheid moeten verbeteren ten opzichte van de huidige situatie.

## Supporting Types

### OverviewColumn
- `key` (string)
- `label` (string)
- `priority` (enum: primary, secondary)
- `isNumeric` (boolean)

### OverviewRow
- `cells` (ordered collection)
- `rowState` (enum: normal, highlighted, warning, disabled)
- `actions` (collection of ActionLink)

### SummaryBlock
- `label` (string)
- `value` (string)
- `emphasis` (enum: low, medium, high)

### ActionLink
- `label` (string)
- `href` (string)
- `styleVariant` (enum: primary, secondary, tertiary, destructive, inline)
- `icon` (string|null)

## Relationships

- `ScreenTemplate` contains many `NavigationItem` records.
- `ScreenTemplate` renders either one or more `FormScreen` and/or `DataOverview` regions.
- `FormScreen` contains many `FieldGroup` objects.
- `FieldGroup` contains many `Field` objects.
- `FormScreen` and `DataOverview` can both surface `StatusMessage` objects.
- `ScreenTemplate`, `FormScreen` and `DataOverview` all consume one shared `VisualTokenSet`.

## State Transitions

### NavigationItem
- `inactive` -> `active` wanneer huidige route/context overeenkomt
- `active` -> `inactive` wanneer gebruiker naar ander scherm navigeert
- `hidden` -> `visible` alleen via bestaande permissie- of competitielogica

### StatusMessage
- `not-rendered` -> `rendered`
- `rendered` -> `acknowledged` of `persisted`
- De refresh verandert alleen de visuele representatie van deze states, niet de triggerlogica

### Responsive Presentation
- `desktop layout` -> `tablet layout` -> `mobile layout`
- In elke state moeten primaire actie, context en kerninformatie zichtbaar blijven
