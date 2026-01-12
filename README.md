# Spent — Core Concept (v1)

## Overview

Spent is a personal expense tracking app that helps users see where their money goes, organized into separate pockets for different contexts.

---

## Pockets

A pocket is a container for expenses. Each pocket has its own members, categories, and metrics.

### Personal Pocket

- Auto-created on registration
- Single member (the user)
- Cannot be deleted
- Purpose: default container for general personal expenses

### Custom Pockets

- User-created, user-named
- Can be solo (just the user) or shared (invite others)
- Can be archived or deleted
- Examples:
    - "Household" — shared with partner
    - "Hobbies" — solo, separating hobby spending from general personal
    - "Spain Trip" — temporary, shared with friends
    - "Car" — solo, isolating vehicle-related costs

---

## Metrics Logic

The dashboard adapts based on pocket membership:

### Solo pocket (1 member)

- Total spent
- Breakdown by category
- Spending over time chart

### Shared pocket (2+ members)

- Total spent (combined)
- Breakdown by category
- Breakdown by member (who paid what)
- Spending over time chart

---

## Navigation

Top-level toggle to switch views:

```
[All]  [Personal]  [Hobbies]  [Household]
```

- **All**: aggregates everything, shows breakdown by pocket
- **Single pocket view**: metrics scoped to that pocket

---

## Dashboard Layouts

### All Pockets View

Two-card layout, no category breakdown at this level.

```
┌─────────────────────────────────────┬───────────────────────────────────┐
│                                     │                                   │
│  Your Spending                      │  By Pocket                        │
│  €1,790.00                          │                                   │
│                                     │  Personal          €650 (27%)     │
│  ┌───────────────────────────────┐  │  Hobbies           €320 (13%)     │
│  │                               │  │  Household       €1,480 (60%)     │
│  │  📈 Spending over time        │  │    You     €820 (55%)             │
│  │                               │  │    Lora    €660 (45%)             │
│  │                               │  │                                   │
│  └───────────────────────────────┘  │                                   │
│                                     │                                   │
└─────────────────────────────────────┴───────────────────────────────────┘
```

- "Your Spending" shows only what the user spent (sum of user's contributions across all pockets)
- Chart shows only user's spending over time (not other members' contributions)
- "By Pocket" shows full pocket totals with member breakdown for shared pockets
- Tapping a pocket navigates to that pocket's detail view
- Solo pockets show just the total (no member breakdown needed)

### Single Pocket View (Solo)

```
┌─────────────────────────────────────┬───────────────────────────────────┐
│                                     │                                   │
│  Spent Total                        │  By Category                      │
│  €650.00                            │                                   │
│                                     │  Photography       €250 (38%)     │
│  ┌───────────────────────────────┐  │  Gaming            €180 (28%)     │
│  │                               │  │  Books             €120 (18%)     │
│  │  📈 Spending over time        │  │  Other             €100 (16%)     │
│  │                               │  │                                   │
│  │                               │  │                                   │
│  └───────────────────────────────┘  │                                   │
│                                     │                                   │
└─────────────────────────────────────┴───────────────────────────────────┘
```

### Single Pocket View (Shared)

```
┌───────────────────────────┬──────────────────────┬──────────────────────┐
│                           │                      │                      │
│  Spent Total              │  By Member           │  By Category         │
│  €1,480.00                │                      │                      │
│                           │  Martin  €820 (55%)  │  Groceries     €420  │
│  ┌─────────────────────┐  │  Lora    €660 (45%)  │  Bills         €380  │
│  │                     │  │                      │  Subscriptions €290  │
│  │  📈 Spending over   │  │                      │  Other         €390  │
│  │     time            │  │                      │                      │
│  │                     │  │                      │                      │
│  └─────────────────────┘  │                      │                      │
│                           │                      │                      │
└───────────────────────────┴──────────────────────┴──────────────────────┘
```

- Three cards: Total + chart, Member breakdown, Category breakdown

---

## Out of Scope (v1)

- Settling up / balance calculations
- Business expense tracking
- Bank integrations
- Budgeting / limits

---

## Future Considerations

- Toggle in "All" view to switch between "Your Spending" and "Total Spending" (includes all members)
- Temporary pockets (auto-prompt to archive after a date)
- Settling up and balance tracking
- Target split ratios
- Bank account integration

## Tech Stack

- **Backend:** Laravel 12, PHP 8.4
- **Frontend:** Livewire 3, Flux UI, Tailwind CSS 4
- **Auth:** Laravel Fortify
- **Testing:** Pest 4

## Todos 
- Add timezone and locale user settings for UI and time localization
- Invite people to a shared group via their email (existing app users)
- Invite people to a shared group via their email (non app users - email invite to register)
- Update/delete expenses
- Group and Category CRUDs (dedicated pages with further metrics)
- Integrate Frankfurter API for exchange rates and store them along with the final calculated amount in group currency
