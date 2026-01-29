# INSTRUCTIONS / AGENTS.md

This file contains critical instructions for developers (humans and AI) working on this project. **Strict adherence to these guidelines is required** to ensure code consistency, mutualization, and stability.

## 1. Project Overview & Architecture

*   **Frontend:** Nuxt 3 (Vue 3).
*   **Backend:** Pure PHP (No framework), using `mysqli` for database interactions.
*   **Communication:** All API requests go through `backend/api.php` via the `?action=ACTION_NAME` query parameter.
*   **Design System:** Custom CSS system. **Do NOT use Tailwind CSS**, even if it appears in dependencies. Use the defined CSS variables and component classes.

## 2. Frontend Guidelines (Nuxt 3)

### 2.1 Reusability & Components ("Mutualisation")
*   **Do not duplicate code.** Check `components/elements` and `components/elements/bloc` before creating new components.
*   **Atomic Components:** Use the atomic components found in `components/elements/bloc/` for basic UI elements. Examples:
    *   `gBtn.vue`: Standard button with icon/text support.
    *   `input.vue` / `inputBtn.vue`: Form inputs.
    *   `select.vue`: Dropdowns.
    *   `radio.vue`: Toggle switches/radio buttons.
*   **Complex Components:** Larger business logic components (e.g., `productPart.vue`) aggregate these atoms.
*   **Icons:** Do not import external icon libraries. Use the SVG paths defined in `public/icons.json` and `public/iconsFilled.json`.

### 2.2 Styling (No Tailwind)
*   **Strictly avoid Tailwind classes.**
*   **CSS Variables:** Use the color palette and spacing variables defined in `assets/css/variables.css`.
    *   Examples: `var(--color-rangy)`, `var(--color-whitly)`, `var(--color-darkly)`.
*   **Classes:** Use the global classes from `assets/css/components.css`.
    *   Examples: `.btn1`, `.floating-input`, `.card-style`.
*   **Scoped Styles:** For component-specific layout, use `<style scoped>` with standard CSS (Flexbox/Grid).

### 2.3 State Management
*   Use `v-model` (binding to `modelValue` prop) for form components to ensure data flows back to the parent.
*   Avoid global state where simple props/events suffice.

## 3. Backend Guidelines (PHP)

### 3.1 Routing & API Structure
*   **Entry Point:** `backend/api.php` is the single entry point.
*   **Adding an Endpoint:**
    1.  Create your PHP script in `backend/sql/{type}/{scriptName}.php` (where `{type}` is `get`, `post`, `update`, `delete`, etc.).
    2.  Register the action in the `$routes` array in `backend/api.php`.
    *   Example: `"myNewAction" => "../backend/sql/get/myScript.php"`

### 3.2 Database Interactions
*   **Connection:** Require `dbConfig.php`.
*   **Schema Updates:** The script responsible for a feature should ensure its table/columns exist.
    *   Use `CREATE TABLE IF NOT EXISTS ...` at the top of your script.
    *   Use `ALTER TABLE ... ADD COLUMN IF NOT EXISTS ...` for migrations.
*   **Security:** Always use prepared statements (`$stmt = $mysqli->prepare(...)`) to prevent SQL injection.

### 3.3 Input & Output
*   **Input:** Read JSON payloads via `json_decode(file_get_contents('php://input'), true)`.
*   **Output:** Return data using `echo json_encode([...])`.
*   **Buffering:** `backend/api.php` uses `ob_start()`. **Do not `echo` debug text** directly in your scripts, as it will break the JSON response. Use the `response()` helper function if available, or return a structured JSON error.

## 4. Development Workflow

1.  **Analyze:** Before coding, identify if an existing component or API action can be reused.
2.  **Database:** If you need new data, update the PHP script to handle the schema change automatically.
3.  **Frontend:** Implement the UI using `components/elements/bloc` atoms and `assets/css` variables.
4.  **Verify:** Check the network tab to ensure the API returns valid JSON (no PHP warnings/errors mixed in).

## 5. Common Pitfalls & Fixes

*   **Bug:** API returns `500` or invalid JSON.
    *   *Cause:* PHP syntax error or `echo` output interfering with JSON.
    *   *Fix:* Check error logs, ensure no stray `echo` calls, ensure `dbConfig.php` path is correct.
*   **Bug:** Styles look wrong in Dark Mode.
    *   *Fix:* Ensure you are using the CSS variables (e.g., `var(--color-whitly)`) which change automatically based on the theme, rather than hardcoded hex codes.
*   **Bug:** Components not updating.
    *   *Fix:* Check `v-model` implementation. Ensure you are emitting `update:modelValue`.

---
**Note to AI Agents:** When asked to implement a feature, always scan `components/elements/bloc` first. Do not generate Tailwind code. Follow the PHP pattern of "Check Schema -> Execute Query -> Return JSON".
