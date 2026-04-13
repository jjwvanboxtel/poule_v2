/**
 * Navigation data: URL builders and route constants for poule_v2 Playwright tests.
 *
 * The application uses query-string routing. All routes go through `index.php`
 * (the application root) with these URL patterns:
 *
 *   Home:               /
 *   Competition:        /?competition=<id>
 *   Module overview:    /?com=<comId>
 *   Module option:      /?com=<comId>&option=<option>
 *   Module add:         /?com=<comId>&option=add
 *   Module edit:        /?com=<comId>&option=edit&id=<recordId>
 *   Competition+module: /?competition=<id>&com=<comId>
 *   Login action:       /?com=<comId>&option=login
 *   Logout action:      /?com=<comId>&option=logout
 *
 * Component IDs (`com`) are stored in the `component` database table. Use the
 * MODULE_NAMES constants below with a helper to look up the com_id at runtime,
 * or pass a known numeric ID in integration tests that target a seeded database.
 */

// ---------------------------------------------------------------------------
// Option values used in query strings
// ---------------------------------------------------------------------------

/** Known values for the `option` query-string parameter. */
export const OPTIONS = {
  /** Display and submit the login form. */
  login: 'login',
  /** Log the current user out. */
  logout: 'logout',
  /** Show the add/create form for the module. */
  add: 'add',
  /** Show the edit form for an existing record. */
  edit: 'edit',
  /** Show a delete confirmation for an existing record. */
  delete: 'delete',
} as const;

export type OptionValue = (typeof OPTIONS)[keyof typeof OPTIONS];

// ---------------------------------------------------------------------------
// Well-known module names
// ---------------------------------------------------------------------------

/**
 * The database `com_name` values for all known application modules.
 * These match the class names loaded by `App::openClass()` in `index.php`.
 */
export const MODULE_NAMES = {
  COMPETITIONS: 'Competitions',
  GAMES: 'Games',
  PARTICIPANTS: 'Participants',
  USERS: 'Users',
  USERGROUPS: 'UserGroups',
  PREDICTIONS: 'Predictions',
  TABLE: 'Table',
  CITIES: 'Cities',
  COUNTRIES: 'Countries',
  FORMS: 'Forms',
  PLAYERS: 'Players',
  POULES: 'Poules',
  QUESTIONS: 'Questions',
  REFEREES: 'Referees',
  ROUNDS: 'Rounds',
  SCORINGS: 'Scorings',
  SECTIONS: 'Sections',
  STATISTICS: 'Statistics',
  SUBLEAGUES: 'Subleagues',
  USERCONTROL: 'UserControl',
} as const;

export type ModuleName = (typeof MODULE_NAMES)[keyof typeof MODULE_NAMES];

// ---------------------------------------------------------------------------
// Route builder functions
// ---------------------------------------------------------------------------

/**
 * Return the route for the application home page.
 *
 * @example homeRoute() // → '/'
 */
export function homeRoute(): string {
  return '/';
}

/**
 * Return the route for a competition detail / context page.
 *
 * @param competitionId  The numeric competition ID from the database.
 * @example competitionRoute(3) // → '/?competition=3'
 */
export function competitionRoute(competitionId: number | string): string {
  return `/?competition=${competitionId}`;
}

/**
 * Return the route for a module overview screen.
 *
 * @param comId  The numeric component ID (`com_id`) from the `component` table.
 * @example moduleRoute(5) // → '/?com=5'
 */
export function moduleRoute(comId: number | string): string {
  return `/?com=${comId}`;
}

/**
 * Return the route for a module option screen (add / edit / login / logout).
 *
 * @param comId   The numeric component ID.
 * @param option  The option value (use the OPTIONS constants).
 * @example moduleOptionRoute(5, OPTIONS.add) // → '/?com=5&option=add'
 */
export function moduleOptionRoute(
  comId: number | string,
  option: string,
): string {
  return `/?com=${comId}&option=${option}`;
}

/**
 * Return the route for the add/create form of a module.
 *
 * @param comId  The numeric component ID.
 * @example moduleAddRoute(5) // → '/?com=5&option=add'
 */
export function moduleAddRoute(comId: number | string): string {
  return moduleOptionRoute(comId, OPTIONS.add);
}

/**
 * Return the route for the edit form of a specific record.
 *
 * @param comId     The numeric component ID.
 * @param recordId  The numeric record ID to edit.
 * @example moduleEditRoute(5, 42) // → '/?com=5&option=edit&id=42'
 */
export function moduleEditRoute(
  comId: number | string,
  recordId: number | string,
): string {
  return `/?com=${comId}&option=${OPTIONS.edit}&id=${recordId}`;
}

/**
 * Return the route for a module scoped to a specific competition context.
 *
 * @param competitionId  The numeric competition ID.
 * @param comId          The numeric component ID.
 * @example competitionModuleRoute(3, 5) // → '/?competition=3&com=5'
 */
export function competitionModuleRoute(
  competitionId: number | string,
  comId: number | string,
): string {
  return `/?competition=${competitionId}&com=${comId}`;
}

/**
 * Return the route for the login form, scoped to an optional competition.
 *
 * @param comId          The component ID of the UserControl module.
 * @param competitionId  Optional competition ID to preserve context after login.
 * @example loginRoute(2)       // → '/?com=2&option=login'
 * @example loginRoute(2, 3)    // → '/?competition=3&com=2&option=login'
 */
export function loginRoute(
  comId: number | string,
  competitionId?: number | string,
): string {
  const prefix = competitionId !== undefined ? `/?competition=${competitionId}&` : '/?';
  return `${prefix}com=${comId}&option=${OPTIONS.login}`;
}

/**
 * Return the route for the logout action, scoped to an optional competition.
 *
 * @param comId          The component ID of the UserControl module.
 * @param competitionId  Optional competition ID to preserve context after logout.
 */
export function logoutRoute(
  comId: number | string,
  competitionId?: number | string,
): string {
  const prefix = competitionId !== undefined ? `/?competition=${competitionId}&` : '/?';
  return `${prefix}com=${comId}&option=${OPTIONS.logout}`;
}
