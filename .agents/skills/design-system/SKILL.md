---
name: design-system
description: "Apply this skill to ensure adherence to UI/UX design principles, typography hierarchy, and color systems across the GoalPilot application. Triggers when working on frontend components, page layouts, forms, buttons, and typography styling."
---

# Design System & Project Conventions

To maintain a consistent and professional UI across the application, all pages and components must follow these design standards.

## Typography Hierarchy

All text headings should be implemented using Flux UI heading components to maintain uniform sizing:

### 1. Page Titles (H1)
- **Component**: `<flux:heading size="xl" level="1">`
- **Usage**: Only one per page, at the very top. Represents the main context of the page (e.g., "My Goals", "Weekly Planning", "Time Log").
- **Subheading**: `<flux:subheading size="lg">` (optional) to provide page-level description.

### 2. Section / Form Titles (H2)
- **Component**: `<flux:heading size="lg">`
- **Usage**: Used for major sections within a page or main forms (e.g., "Create New Goal", "Start New Week").
- **Subheading**: `<flux:subheading size="md">` (optional) for section descriptions.

### 3. Card / Subsection Titles (H3)
- **Component**: `<flux:heading size="md">`
- **Usage**: Used for grouping lists, secondary forms, or widget titles (e.g., "Logged Entries this Week", "Goal Priority Allocation").

### 4. Item Titles
- **Markup**: `<h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">`
- **Usage**: Individual items inside lists or cards (e.g., Goal names in a list).

### 5. Callouts (Alerts)
- **Component**: `<flux:callout>`
- **Usage**: Always use the `heading` attribute instead of passing text as a slot.
  **Correct**: `<flux:callout variant="danger" icon="exclamation-triangle" class="mb-4" heading="{{ $errorMessage }}" />`
  **Incorrect**: `<flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">{{ $errorMessage }}</flux:callout>`

## Buttons and Colors

Buttons should have clear affordances and follow consistent color coding depending on the action they represent:

- **Primary Actions (Save, Add, Create)**: `variant="primary"` (Default brand color, often indigo/blue).
- **Secondary Actions (Cancel, Back)**: `variant="ghost"` or `variant="outline"`.
- **Destructive Actions (Delete, Remove)**: `variant="danger"`.
- **Contextual Actions (Edit, Archive)**:
  - Inside lists or tables, prefer `variant="primary"` with specific `color` properties if strong affordance is needed (e.g., `color="indigo"` for Edit, `color="amber"` for Archive, `color="emerald"` for Unarchive). 
  - Ensure colors contrast correctly with the overall application theme.
