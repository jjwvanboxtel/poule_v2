# Backlog (grouped by epic)

This backlog is derived from the implemented features and the identified gaps. Ordering reflects typical value/risk: security + correctness first, then core pool flows, then nice-to-haves.

## Epic E1: Authentication & Accounts
- **S-001** Login
- **S-003** Password reset
- **S-004** Registration
- **S-002** Logout

## Epic E2: Competition Administration
- **S-010** Create competition (including initial section/scoring setup)
- **S-011** Edit competition settings (deadline/header)
- **S-012** Delete competition
- **S-013** Select competition context

## Epic E3: Tournament Content Setup
- **S-020** Manage countries & flags
- **S-021** Manage cities
- **S-022** Manage poules/groups
- **S-023** Manage games & results
- **S-024** Manage rounds & advancing countries
- **S-025** Manage questions
- **S-026** Set question answers
- **S-027** Manage referees
- **S-028** Manage players
- **S-029** Bulk-import players

## Epic E4: Prediction Entry & Submission
- **S-030** View predictions
- **S-031** Edit predictions before deadline
- **S-032** Predict cards (if enabled)
- **S-033** Predict knockout rounds
- **S-034** Answer prediction questions
- **S-035** Subscribe/finalize predictions
- **S-036** Admin view/edit participant predictions

## Epic E5: Payments & Visibility
- **S-040** Mark participant paid
- **S-041** Show payment link
- **S-042** Apply visibility rules

## Epic E6: Scoring & Rankings
- **S-050** Enable/disable prediction sections
- **S-051** Configure scoring rules & points
- **S-052** Recalculate rankings
- **S-053** View leaderboard
- **S-054** Show leaderboard movement

## Epic E7: Subleagues
- **S-060** View subleagues
- **S-061** Create/manage subleagues
- **S-062** Manage subleague membership

## Epic E8: Statistics & Downloads
- **S-071** Generate statistics charts
- **S-070** View statistics
- **S-073** Upload/manage forms
- **S-072** View/download forms

## Epic E9: Access Control
- **S-080** Manage user groups and rights

## Technical / Gap backlog
- **G-001** Restrict statistics generation to authorized users.
- **G-002** Decide and implement ranking refresh behavior after section/scoring config changes (auto-recalc or explicit prompt).
- **G-003** Clarify/extend payment workflow (optional: self-service payment confirmation).
