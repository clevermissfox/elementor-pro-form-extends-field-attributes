# Elementor Pro Form – Extends Field Attributes

Append classes/attributes to **Elementor Pro** Form fields — safely and per-field.
Also enables **Dynamic Tags** on the **Form Name** so you can build descriptive, context-aware labels.

## What it does

### For input / textarea / select

- **Input CSS classes** — space-separated classes appended to the element (append-only; never overwrites existing classes).
- **Input attributes** — one per line as `key|value`. If the key already exists, your value **overwrites** it.
  - **Protected keys are ignored:** `id`, `name`, `type`, `value`, `checked`, `selected`, `multiple`, `form`, `list`, and any `on*` event handlers. Use the filter to add or remove items from this array.
  - `class` is also protected (can only be changed via the classes control).

### For checkbox / radio groups

- **Wrapper CSS classes** — appended to the **field wrapper** (the group `<div>`), not to individual option inputs.
  Useful for layout (columns, spacing, borders) without touching each option.

### Dynamic Tags

- **Form Name** now accepts Dynamic Tags (e.g., Post Title) including **Before/After** in the tag UI.
- The three custom controls (**Input CSS classes**, **Input attributes**, **Wrapper CSS classes**) also support Dynamic Tags.

## Installation

1. Copy the folder: `wp-content/plugins/elementor-pro-form-extends-field-attributes/`
   containing:

- `elementor-pro-form-extends-field-attributes.php`

2. Activate **Elementor Pro Form – Extends Field Attributes**.

3. Edit a **Form**:

- In the editor: open the **Form** widget → select an eligible field in the **Content** tab → then go to the field’s **Advanced** tab → use the new controls.

## Usage examples

**Input CSS classes**
`visually-hidden border-bottom-md bg-accent`
(available on `input:not([type=checkbox], [type=radio]), select, textarea`)

**Input attributes**
`aria-label|Your label here`
`inputmode|numeric`
`pattern|\d+`
`data-tracking|lead`
(available on `input:not([type=checkbox], [type=radio]), select, textarea`)

**Wrapper CSS classes**
`grid grid-cols-2 gap-3`
(available on the wrapper for checkbox and radio groups `div.elementor-field-type-checkbox`, not the inputs themselves)

## Notes & Nuances

- This addon targets the **element itself** (input/textarea/select) — not the wrapper — for most field types.
- For **checkbox/radio**, the control targets the **wrapper** because those fields render multiple inputs; styling the group is usually more practical.
- **Protected attributes** are ignored to prevent breaking labels, submissions, or validation:
  - `id`, `name`, `type`, `value`, `checked`, `selected`, `multiple`, `form`, `list`, any attribute starting with `on` (e.g., `onclick`, `oninput`).
- **Dynamic Tags** are resolved by Elementor before rendering, so you can safely combine tags (e.g., class names with post slugs).

## Filtering (advanced)

You can customize the protected keys (denylist)

**Remove an item from the denylist** (allow it):

```php
add_filter( 'epfea_disallowed_attr_keys', function( $keys ) {
return array_values( array_diff( $keys, [ 'list' ] ) );
} );
```

**Add an item to the denylist**:

```php
add_filter( 'epfea_disallowed_attr_keys', function( $keys ) {
$keys[] = 'placeholder';
return $keys;
} );
```

## Compatibility

- Requires Elementor Pro (Forms module).
- Tested across Elementor Pro versions where `Field_Base` is abstract and requires `render()`; this plugin includes a no-op render for compatibility.
- No changes are made to core or theme files.

## Changelog

### 0.3.3
- Added denylist enforcement to prevent overriding critical attributes (`id`, `name`, `type`, etc.).
- Added filter `epfea_disallowed_attr_keys` so developers can customize the denylist.

### 0.3.2
- Removed unused info icons to simplify UI.
- Cleaned up descriptions for clarity.

### 0.3.0 – 0.3.1
- Added Dynamic Tags support for Input CSS classes, Input attributes, and Wrapper CSS classes.
- Enabled Dynamic Tags on Form Name.
- Introduced wrapper class option for checkbox/radio groups.

### 0.2.x
- Initial release with basic per-field class/attribute controls.
