# Spent

A personal expense tracking application. Organize your spending by groups (e.g., Personal, Family, Work) and categories (e.g., Food, Gas, Utilities). Track amounts in multiple currencies with powerful filtering and insights.

## Features

### Dashboard
- Summary stats cards with sparkline charts
- Spending by category, group, and total
- Sortable expense table with pagination
- Date range picker with presets (today, this week, this month, YTD, etc.)
- Multi-group filtering

### Expense Management
- Quick expense entry via modal
- Organize by groups and categories
- Multi-currency support (EUR, USD)
- Optional notes for each expense
- Amounts stored in cents for precision

### User Onboarding
- Default "Personal" group created on registration
- Pre-seeded categories for the "Personal" expenses group

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
- Quick add new group from create expense modal
- Quick add new category from create expense modal
- Group CRUD
- Group Categories CRUD
- Pie chart with spendings per category in a group
