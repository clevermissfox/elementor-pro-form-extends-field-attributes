=== Elementor Pro Form – Extends Field Attributes ===
Contributors: edico
Tags: elementor, elementor pro, forms, attributes, classes, dynamic tags
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.3.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Append classes/attributes to Elementor Pro Form fields safely and per-field. Also enables Dynamic Tags on the Form Name for descriptive, context-aware labels.

== Description ==

Adds per-field controls to Elementor Pro Forms:

* **Input CSS classes** — append classes to inputs, textareas, selects.
* **Input attributes** — add attributes (e.g., `aria-label|Your label`) safely. Protected keys (`id`, `name`, `type`, etc.) and `on*` handlers are blocked.
* **Wrapper CSS classes** — for checkbox/radio groups, applied to the group wrapper.
* **Dynamic Tags** — supported on all three controls, plus the Form Name.

**Why use this plugin?**

* Extend forms without hacking core.
* Portable and update-safe.
* Uses Elementor Pro’s official Field API.

== Installation ==

1. Upload the plugin folder `elementor-pro-form-extends-field-attributes` to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Edit an Elementor Pro Form:
   * In the editor, open the **Form** widget
   * Select a field in the **Content** tab
   * Switch to the field’s **Advanced** tab
   * Use the new controls

== Usage ==

### Input CSS classes
`visually-hidden border-bottom-md bg-accent`
*(available on `input:not([type=checkbox],[type=radio]), select, textarea`)*

### Input attributes
`aria-label|Your label here`
`inputmode|numeric`
`pattern|\d+`
`data-tracking|lead`
*(available on `input:not([type=checkbox],[type=radio]), select, textarea`)*

### Wrapper CSS classes
`grid grid-cols-2 gap-3`
*(available on checkbox/radio group wrapper `div.elementor-field-type-checkbox`)*

== Notes & Nuances ==

* Most field types: classes/attributes target the **element itself**.
* Checkbox/radio: classes target the **wrapper div**, not individual inputs.
* Protected attributes ignored: `id`, `name`, `type`, `value`, `checked`, `selected`, `multiple`, `form`, `list`, and anything starting with `on` (e.g., `onclick`, `oninput`).
* Dynamic Tags resolve before rendering, so you can mix tags with static values.

== Filtering (Advanced) ==

Developers can customize the denylist of protected attributes:

**Remove an item**:
```PHP
add_filter( 'epfea_disallowed_attr_keys', function( $keys ) {
return array_values( array_diff( $keys, [ 'list' ] ) );
} );
```

**Add an item**:
```PHP
add_filter( 'epfea_disallowed_attr_keys', function( $keys ) {
$keys[] = 'placeholder';
return $keys;
} );
```


== Changelog ==

= 0.3.3 =
* Added denylist enforcement for critical attributes (`id`, `name`, etc.).
* Added `epfea_disallowed_attr_keys` filter.

= 0.3.2 =
* Removed unused info icons.
* Cleaned up control descriptions.

= 0.3.0 – 0.3.1 =
* Added Dynamic Tags to all custom controls.
* Enabled Dynamic Tags on Form Name.
* Added wrapper class option for checkbox/radio groups.

= 0.2.x =
* Initial release with per-field class/attribute controls.

== Compatibility ==
* Requires Elementor Pro (Forms module).
* Tested with Elementor Pro where `Field_Base` is abstract and requires `render()`; this plugin includes a no-op render.
* No core or theme files are modified.
