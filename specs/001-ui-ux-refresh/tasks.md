# Tasks: UI/UX Refresh van de applicatie

**Input**: Design documents from `C:\Github\poule_v2\specs\001-ui-ux-refresh\`
**Prerequisites**: `C:\Github\poule_v2\specs\001-ui-ux-refresh\plan.md`, `C:\Github\poule_v2\specs\001-ui-ux-refresh\spec.md`, `C:\Github\poule_v2\specs\001-ui-ux-refresh\research.md`, `C:\Github\poule_v2\specs\001-ui-ux-refresh\data-model.md`, `C:\Github\poule_v2\specs\001-ui-ux-refresh\quickstart.md`

**Tests**: Tests are required for this feature because `spec.md`, `plan.md` and `quickstart.md` explicitly require test-first UI regression coverage with existing SimpleTest suites plus new Playwright journeys.

**Organization**: Tasks are grouped by user story so each UI refresh slice can be implemented and validated independently without changing existing business logic, routes, permissions, calculations, querystrings, form actions or POST field names.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel when the referenced files do not overlap with incomplete tasks
- **[Story]**: Maps a task to a specific user story from `C:\Github\poule_v2\specs\001-ui-ux-refresh\spec.md`
- Every task below includes exact repository file paths

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the test and helper scaffolding needed to execute the UI/UX refresh safely in this legacy PHP codebase.

- [x] T001 Create the Playwright workspace for UI smoke tests in `C:\Github\poule_v2\tests\playwright\package.json` and `C:\Github\poule_v2\tests\playwright\playwright.config.ts`
- [x] T002 [P] Create shared Playwright helper fixtures and viewport utilities in `C:\Github\poule_v2\tests\playwright\tests\helpers\app-fixtures.ts` and `C:\Github\poule_v2\tests\playwright\tests\helpers\viewports.ts`
- [x] T003 Create a dedicated SimpleTest UI regression bootstrap in `C:\Github\poule_v2\tests\ui\ui_testcase.php` and register the UI regression suites in `C:\Github\poule_v2\tests\all_tests.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Put the shared selectors, assertions and visual utility layer in place before changing any user-story-specific markup.

**⚠️ CRITICAL**: No user story work should begin until this phase is complete.

- [x] T004 [P] Capture reusable module-route and screen-selector fixtures for navigation, forms and overviews in `C:\Github\poule_v2\tests\playwright\tests\helpers\navigation-data.ts` and `C:\Github\poule_v2\tests\playwright\tests\helpers\module-routes.ts`
- [x] T005 [P] Add shared server-rendered UI assertions for title panels, alerts, forms and list tables in `C:\Github\poule_v2\tests\ui\ui_shell_assertions.php` and `C:\Github\poule_v2\tests\ui\ui_markup_assertions.php`
- [x] T006 Create the shared Bootstrap-aligned utility layer for cards, alerts, buttons, forms and list tables in `C:\Github\poule_v2\templates\orange\template.css`

**Checkpoint**: Foundation ready — shell, form and overview work can now proceed with shared test support and CSS utilities in place.

---

## Phase 3: User Story 1 - Consistente navigatie en schermopbouw (Priority: P1) 🎯 MVP

**Goal**: Deliver one recognizable application shell with consistent top navigation, hero/context blocks and sidebar orientation across the existing screens.

**Independent Test**: Open representative home, competition and deep-linked module screens and confirm the same navigation patterns, page hierarchy, active location and contextual sidebar are visible without any changed actions or outcomes.

### Tests for User Story 1

- [x] T007 [P] [US1] Add the navigation/orientation browser regression journey in `C:\Github\poule_v2\tests\playwright\tests\navigation-shell.spec.ts`
- [x] T008 [P] [US1] Add shared shell render regressions for placeholders, hero context and sidebar output in `C:\Github\poule_v2\tests\ui\ui_shell_render_tests.php`

### Implementation for User Story 1

- [x] T009 [US1] Refactor the shared shell markup and responsive content containers in `C:\Github\poule_v2\templates\orange\index.tpl.php`
- [x] T010 [P] [US1] Modernize primary and contextual navigation rendering while preserving existing links and active-state logic in `C:\Github\poule_v2\modules\menu.class.php` and `C:\Github\poule_v2\index.php`
- [x] T011 [US1] Normalize home, competition and deep-link hero/sidebar context blocks without changing route behavior in `C:\Github\poule_v2\index.php` and `C:\Github\poule_v2\templates\orange\template.css`

**Checkpoint**: User Story 1 is complete when the shared shell is consistent and independently testable across the main application entry paths.

---

## Phase 4: User Story 2 - Duidelijke invoer- en beheerpagina's (Priority: P2)

**Goal**: Make the legacy form and beheer screens easier to scan by standardizing labels, grouping, messages and primary/secondary actions while preserving every existing submit contract.

**Independent Test**: Open representative login, add/edit and beheer screens and confirm that fields, labels, required information, warnings and action buttons are clearer while form actions, methods, hidden inputs and outcomes remain unchanged.

### Tests for User Story 2

- [x] T012 [P] [US2] Add the form-management browser regression journey in `C:\Github\poule_v2\tests\playwright\tests\form-management.spec.ts`
- [x] T013 [P] [US2] Add form markup regressions for grouped fields, action rows and validation/status states in `C:\Github\poule_v2\tests\ui\ui_form_render_tests.php`

### Implementation for User Story 2

- [x] T014 [US2] Restyle the authentication and account-related forms in `C:\Github\poule_v2\modules\usercontrol\loginscreen.tpl.php`, `C:\Github\poule_v2\modules\usercontrol\login_lost.tpl.php` and `C:\Github\poule_v2\modules\usercontrol\confirmation.tpl.php`
- [ ] T015 [P] [US2] Restyle the primary administration forms in `C:\Github\poule_v2\modules\competitions\competition_add.tpl.php`, `C:\Github\poule_v2\modules\games\game_add.tpl.php`, `C:\Github\poule_v2\modules\participants\participant_add.tpl.php` and `C:\Github\poule_v2\modules\users\user_add.tpl.php`
- [ ] T016 [P] [US2] Restyle the remaining maintenance forms in `C:\Github\poule_v2\modules\cities\city_add.tpl.php`, `C:\Github\poule_v2\modules\countries\country_add.tpl.php`, `C:\Github\poule_v2\modules\forms\form_add.tpl.php`, `C:\Github\poule_v2\modules\players\player_add.tpl.php`, `C:\Github\poule_v2\modules\poules\poule_add.tpl.php`, `C:\Github\poule_v2\modules\questions\question_add.tpl.php`, `C:\Github\poule_v2\modules\referees\referee_add.tpl.php`, `C:\Github\poule_v2\modules\rounds\round_add.tpl.php`, `C:\Github\poule_v2\modules\scorings\scoring_add.tpl.php`, `C:\Github\poule_v2\modules\sections\section_add.tpl.php`, `C:\Github\poule_v2\modules\subleagues\subleague_add.tpl.php`, `C:\Github\poule_v2\modules\subleagues\subleague_participant_add.tpl.php` and `C:\Github\poule_v2\modules\usergroups\usergroup_add.tpl.php`
- [ ] T017 [US2] Unify form feedback, confirmation and action-row styling in `C:\Github\poule_v2\modules\predictions\subscribe_confirmation.tpl.php` and `C:\Github\poule_v2\templates\orange\template.css`

**Checkpoint**: User Story 2 is complete when the representative form and beheer flows are independently testable with clearer grouping and unchanged submission behavior.

---

## Phase 5: User Story 3 - Beter leesbare gegevensoverzichten en standen (Priority: P3)

**Goal**: Improve scanability of standings, predictions, lists and detail overviews by applying consistent hierarchy, table wrappers and action areas without changing data meaning.

**Independent Test**: Open existing standings, prediction screens and list/detail overviews and confirm that key columns, summaries, statuses and actions are easier to scan on desktop, tablet and mobile while the underlying data and results remain identical.

### Tests for User Story 3

- [ ] T018 [P] [US3] Add the overview-readability browser regression journey in `C:\Github\poule_v2\tests\playwright\tests\overview-readability.spec.ts`
- [ ] T019 [P] [US3] Add overview markup regressions for tables, summaries and action regions in `C:\Github\poule_v2\tests\ui\ui_overview_render_tests.php`

### Implementation for User Story 3

- [ ] T020 [US3] Modernize the standings and prediction overview templates in `C:\Github\poule_v2\modules\table\table.tpl.php`, `C:\Github\poule_v2\modules\predictions\prediction.tpl.php`, `C:\Github\poule_v2\modules\predictions\user.tpl.php` and `C:\Github\poule_v2\modules\subleagues\subleague_table.tpl.php`
- [ ] T021 [P] [US3] Modernize the highest-traffic list/detail screens in `C:\Github\poule_v2\modules\competitions\competition.tpl.php`, `C:\Github\poule_v2\modules\games\game.tpl.php`, `C:\Github\poule_v2\modules\participants\participant.tpl.php`, `C:\Github\poule_v2\modules\users\user.tpl.php` and `C:\Github\poule_v2\modules\usergroups\usergroup.tpl.php`
- [ ] T022 [P] [US3] Modernize the remaining list/detail screens in `C:\Github\poule_v2\modules\cities\city.tpl.php`, `C:\Github\poule_v2\modules\countries\country.tpl.php`, `C:\Github\poule_v2\modules\forms\form.tpl.php`, `C:\Github\poule_v2\modules\players\player.tpl.php`, `C:\Github\poule_v2\modules\poules\poule.tpl.php`, `C:\Github\poule_v2\modules\questions\question.tpl.php`, `C:\Github\poule_v2\modules\referees\referee.tpl.php`, `C:\Github\poule_v2\modules\rounds\round.tpl.php`, `C:\Github\poule_v2\modules\scorings\scoring.tpl.php`, `C:\Github\poule_v2\modules\sections\section.tpl.php`, `C:\Github\poule_v2\modules\statistics\statistic.tpl.php` and `C:\Github\poule_v2\modules\subleagues\subleague.tpl.php`
- [ ] T023 [US3] Replace inline table and button string builders with shared overview wrappers in `C:\Github\poule_v2\modules\predictions\predictions.class.php`, `C:\Github\poule_v2\modules\table\table.class.php`, `C:\Github\poule_v2\modules\users\users.class.php` and `C:\Github\poule_v2\modules\participants\participants.class.php`

**Checkpoint**: User Story 3 is complete when overview-heavy screens are independently testable with improved readability and preserved data interpretation.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Finish cross-story edge cases, reduce duplication and document final verification.

- [ ] T024 [P] Tune empty, error and success presentation consistency across `C:\Github\poule_v2\index.php`, `C:\Github\poule_v2\templates\orange\template.css`, `C:\Github\poule_v2\modules\usercontrol\loginscreen.tpl.php` and `C:\Github\poule_v2\modules\table\table.tpl.php`
- [ ] T025 [P] Remove leftover legacy spacing and duplicated table/button helper styling in `C:\Github\poule_v2\templates\orange\template.css`, `C:\Github\poule_v2\modules\predictions\predictions.class.php` and `C:\Github\poule_v2\modules\table\table.class.php`
- [ ] T026 Update the final validation checklist and rollout notes in `C:\Github\poule_v2\specs\001-ui-ux-refresh\quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1: Setup** — no dependencies, start immediately
- **Phase 2: Foundational** — depends on Phase 1 and blocks all story work
- **Phase 3: User Story 1 (P1)** — starts after Phase 2 and delivers the MVP shared shell
- **Phase 4: User Story 2 (P2)** — starts after Phase 2; benefits from US1 shell classes but remains independently testable on form pages
- **Phase 5: User Story 3 (P3)** — starts after Phase 2; benefits from US1 shell classes but remains independently testable on overview pages
- **Phase 6: Polish** — starts after the desired user stories are complete

### User Story Dependency Graph

```text
Setup -> Foundational -> US1 -> Polish
                    \-> US2 -> Polish
                    \-> US3 -> Polish
```

### Within Each User Story

- Run the existing SimpleTest regressions first by running `./tests/all_tests.php`
- Write the listed Playwright and SimpleTest regressions first and confirm they fail against the old presentation before implementation
- Update shared CSS utilities before heavily repeating local overrides
- Finish template changes before refactoring inline string builders that depend on the new wrappers
- Validate each story independently before moving on to the next priority slice

### Parallel Opportunities

- **Setup**: `T002` can run while `T001` establishes the Playwright workspace
- **Foundational**: `T004` and `T005` can run in parallel after `T003`
- **US1**: `T007` and `T008` can run in parallel; `T010` can run in parallel with `T009` once `T006` is done
- **US2**: `T012` and `T013` can run in parallel; `T015` and `T016` can run in parallel after `T014` confirms the form pattern direction
- **US3**: `T018` and `T019` can run in parallel; `T021` and `T022` can run in parallel after `T020` establishes the overview pattern
- **Polish**: `T024` and `T025` can run in parallel before `T026`

---

## Parallel Example: User Story 1

```text
Task T007: Add the navigation/orientation browser regression in C:\Github\poule_v2\tests\playwright\tests\navigation-shell.spec.ts
Task T008: Add shared shell render regressions in C:\Github\poule_v2\tests\ui\ui_shell_render_tests.php

Task T009: Refactor the shared shell markup in C:\Github\poule_v2\templates\orange\index.tpl.php
Task T010: Modernize navigation rendering in C:\Github\poule_v2\modules\menu.class.php and C:\Github\poule_v2\index.php
```

## Parallel Example: User Story 2

```text
Task T012: Add the form-management browser regression in C:\Github\poule_v2\tests\playwright\tests\form-management.spec.ts
Task T013: Add form markup regressions in C:\Github\poule_v2\tests\ui\ui_form_render_tests.php

Task T015: Restyle primary administration forms in C:\Github\poule_v2\modules\competitions\competition_add.tpl.php, C:\Github\poule_v2\modules\games\game_add.tpl.php, C:\Github\poule_v2\modules\participants\participant_add.tpl.php and C:\Github\poule_v2\modules\users\user_add.tpl.php
Task T016: Restyle remaining maintenance forms in the other *_add.tpl.php files under C:\Github\poule_v2\modules\
```

## Parallel Example: User Story 3

```text
Task T018: Add the overview-readability browser regression in C:\Github\poule_v2\tests\playwright\tests\overview-readability.spec.ts
Task T019: Add overview markup regressions in C:\Github\poule_v2\tests\ui\ui_overview_render_tests.php

Task T021: Modernize highest-traffic list/detail screens in C:\Github\poule_v2\modules\competitions\competition.tpl.php, C:\Github\poule_v2\modules\games\game.tpl.php, C:\Github\poule_v2\modules\participants\participant.tpl.php, C:\Github\poule_v2\modules\users\user.tpl.php and C:\Github\poule_v2\modules\usergroups\usergroup.tpl.php
Task T022: Modernize remaining list/detail screens in the other detail/list .tpl.php files under C:\Github\poule_v2\modules\
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1
4. Validate navigation, shell consistency and responsive orientation independently
5. Demo the refreshed shared shell before broad module restyling

### Incremental Delivery

1. Build the regression harness and shared CSS utility layer first
2. Deliver the shared shell and navigation refresh (US1)
3. Deliver form and beheer clarity improvements (US2)
4. Deliver standings and overview readability improvements (US3)
5. Finish cross-cutting polish and validation

### Parallel Team Strategy

1. One developer sets up Playwright/SimpleTest scaffolding while another prepares shared CSS utilities
2. After Phase 2, one developer can own US1 while others prepare US2/US3 tests
3. After the shell pattern stabilizes, form and overview template updates can proceed in parallel on non-overlapping files

---

## Notes

- No `contracts\` tasks are included because `C:\Github\poule_v2\specs\001-ui-ux-refresh\plan.md` explicitly states that this feature has no external contract changes
- All tasks preserve the UI-only scope: no business logic, permissions, routes, calculations or data semantics may change
- Every story remains independently testable through the dedicated Playwright and SimpleTest tasks listed above
