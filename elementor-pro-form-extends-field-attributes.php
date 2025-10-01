<?php
/**
 * Plugin Name: Elementor Pro Form - Extends Field Attributes
 * Description: Adds per-field controls to Elementor Pro Forms to append classes/attributes to input/textarea/select; and wrapper classes for checkbox/radio groups. Also enables Dynamic Tags on Form Name.
 * Version: 0.3.3
 * Author: EDICO Designs
 * Text Domain: elementor-pro-form-extends-field-attributes
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Simple requirement check: Elementor Pro must be active.
 */
add_action( 'admin_init', function () {
	if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
		if ( ! class_exists( '\ElementorPro\Plugin' ) || ! class_exists( '\ElementorPro\Modules\Forms\Fields\Field_Base' ) ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error"><p><strong>Elementor Pro Form - Extends Field Attributes</strong> requires Elementor Pro (Forms module) to be active.</p></div>';
			} );
		}
	}
} );

/**
 * Register our helper "field" to inject controls + apply at render time.
 */
add_action( 'elementor_pro/forms/fields/register', function( $registrar ) {
	if ( ! class_exists( '\ElementorPro\Modules\Forms\Fields\Field_Base' ) ) {
		return;
	}

	if ( ! class_exists( 'Elementor_Pro_Form_Extends_Field_Attributes' ) ) :
		class Elementor_Pro_Form_Extends_Field_Attributes extends \ElementorPro\Modules\Forms\Fields\Field_Base {

			public function __construct() {
				if ( method_exists( 'ElementorPro\Modules\Forms\Fields\Field_Base', '__construct' ) ) {
					parent::__construct();
				}
				// Apply our classes/attributes on render for each field item.
				add_filter( 'elementor_pro/forms/render/item', [ $this, 'apply_input_extras' ], 10, 3 );
			}

			public function get_type() {
				return 'elementor-pro-form-extends-field-attributes-helper';
			}

			public function get_name() {
				return __( 'Input Extras (helper)', 'elementor-pro-form-extends-field-attributes' );
			}

			/**
			 * REQUIRED by Field_Base (abstract). We don't render anything because
			 * this helper isn't a real field type.
			 */
			public function render( $item, $item_index, $form ) {
				// No output — helper only.
			}

			/**
			 * Inject new controls into the Form's fields repeater (Advanced tab).
			 */
			public function update_controls( $widget ) {
				if ( ! class_exists( '\ElementorPro\Plugin' ) ) {
					return;
				}
				$elementor = \ElementorPro\Plugin::elementor();
				if ( ! $elementor || ! isset( $elementor->controls_manager ) ) {
					return;
				}

				$controlData = $elementor->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );
				if ( is_wp_error( $controlData ) || empty( $controlData['fields'] ) || ! is_array( $controlData['fields'] ) ) {
					return;
				}

				// Conditions: inputs vs groups
				$for_inputs_condition = [ 'field_type!' => [ 'checkbox', 'radio' ] ];
				$for_groups_condition = [ 'field_type'  => [ 'checkbox', 'radio' ] ];

				$newControls = [
					// INPUT/TEXTAREA/SELECT
					'extends_input_custom_classes' => [
						'name'         => 'extends_input_custom_classes',
						'label'        => __( 'Input CSS classes', 'elementor-pro-form-extends-field-attributes' ),
						'type'         => \Elementor\Controls_Manager::TEXT,
						'description'  => __( 'Space-separated classes to append to the *input* element classlist.', 'elementor-pro-form-extends-field-attributes' ),
						'tab'          => 'advanced',
						'inner_tab'    => 'form_fields_advanced_tab',
						'tabs_wrapper' => 'form_fields_tabs',
						'condition'    => $for_inputs_condition,
						'dynamic'      => [ 'active' => true ], // enable Dynamic Tags
					],
					'extends_input_custom_attrs' => [
						'name'         => 'extends_input_custom_attrs',
						'label'        => __( 'Input attributes', 'elementor-pro-form-extends-field-attributes' ),
						'type'         => \Elementor\Controls_Manager::TEXTAREA,
						'placeholder'  => "aria-label|Your label\nmax|10\ninputmode|numeric",
						'description'  => __( 'Each line: key|value. If the attribute key already exists on the input, your custom attributes will overwrite the value. Protected keys (id,type,name,etc) and event handlers are ignored.', 'elementor-pro-form-extends-field-attributes' ),
						'tab'          => 'advanced',
						'inner_tab'    => 'form_fields_advanced_tab',
						'tabs_wrapper' => 'form_fields_tabs',
						'condition'    => $for_inputs_condition,
						'dynamic'      => [ 'active' => true ], // enable Dynamic Tags
					],

					// CHECKBOX/RADIO GROUPS
					'extends_wrapper_custom_classes' => [
						'name'         => 'extends_wrapper_custom_classes',
						'label'        => __( 'Wrapper CSS classes', 'elementor-pro-form-extends-field-attributes' ),
						'type'         => \Elementor\Controls_Manager::TEXT,
						'description'  => __( 'For checkbox/radio fields, these classes are appended to the field wrapper (div.elementor-field-type-checkbox), not the individual inputs.', 'elementor-pro-form-extends-field-attributes' ),
						'tab'          => 'advanced',
						'inner_tab'    => 'form_fields_advanced_tab',
						'tabs_wrapper' => 'form_fields_tabs',
						'condition'    => $for_groups_condition,
						'dynamic'      => [ 'active' => true ], // optional: dynamic for wrapper classes
					],
				];

				$controlData['fields'] = $this->extends_form_fields_merge_field_controls( $controlData['fields'], $newControls );
				$widget->update_control( 'form_fields', $controlData );
			}

			/**
			 * Apply classes/attributes to the correct element at render time.
			 */
			public function apply_input_extras( $item, $i, $form ) {
				$field_type     = isset( $item['field_type'] ) ? $item['field_type'] : '';
				$wrapper_handle = 'field-group' . $i;

				// Checkbox / Radio wrapper
				if ( in_array( $field_type, [ 'checkbox', 'radio' ], true ) ) {
					if ( ! empty( $item['extends_wrapper_custom_classes'] ) ) {
						$wclasses = preg_split( '/\s+/', trim( (string) $item['extends_wrapper_custom_classes'] ) );
						if ( $wclasses ) {
							$form->add_render_attribute( $wrapper_handle, 'class', array_filter( $wclasses ) );
						}
					}
					return $item;
				}

				// Inputs / Textarea / Select mapping to element handle
				$type_to_handle = [
					'text'            => 'input',
					'email'           => 'input',
					'url'             => 'input',
					'tel'             => 'input',
					'number'          => 'input',
					'password'        => 'input',
					'search'          => 'input',
					'date'            => 'input',
					'time'            => 'input',
					'datetime-local'  => 'input',
					'hidden'          => 'input',
					'upload'          => 'input',
					'textarea'        => 'textarea',
					'select'          => 'select',
				];

				if ( isset( $type_to_handle[ $field_type ] ) ) {
					$handle = $type_to_handle[ $field_type ] . $i;

					// 1) Append classes
					if ( ! empty( $item['extends_input_custom_classes'] ) ) {
						$classes = preg_split( '/\s+/', trim( (string) $item['extends_input_custom_classes'] ) );
						if ( $classes ) {
							$form->add_render_attribute( $handle, 'class', array_filter( $classes ) );
						}
					}

					// 2) Attributes (key|value) with a denylist
					if ( ! empty( $item['extends_input_custom_attrs'] ) ) {
						$lines = preg_split( '/\r\n|\r|\n/', (string) $item['extends_input_custom_attrs'] );
						$deny  = $this->epfea_get_disallowed_attr_keys();

						foreach ( $lines as $line ) {
							if ( strpos( $line, '|' ) === false ) { continue; }
							$parts = explode( '|', $line, 2 );
							$key   = isset( $parts[0] ) ? trim( $parts[0] ) : '';
							$val   = isset( $parts[1] ) ? trim( $parts[1] ) : '';
							if ( $key === '' ) { continue; }

							$lk = strtolower( $key );

							// Never allow via this UI
							if ( $lk === 'class' ) { continue; }
							// Block event handlers (onclick, oninput, etc.) and structural keys
							if ( preg_match( '/^on[a-z]+$/', $lk ) || in_array( $lk, $deny, true ) ) { continue; }

							if ( method_exists( $form, 'remove_render_attribute' ) ) {
								$form->remove_render_attribute( $handle, $key );
							}
							$form->add_render_attribute( $handle, $key, $val );
						}
					}
				}

				return $item;
			}

			/**
			 * Attributes we will NOT allow to be overridden via the UI.
			 * Filterable via 'epfea_disallowed_attr_keys'.
			 *
			 * @return array<string>
			 */
			protected function epfea_get_disallowed_attr_keys() {
				$defaults = [
					'id', 'name', 'type', 'value',
					'checked', 'selected', 'multiple',
					'form', 'list',
				];
				return apply_filters( 'epfea_disallowed_attr_keys', $defaults );
			}

			protected function extends_form_fields_merge_field_controls( array $existing, array $new ) {
				foreach ( $new as $key => $def ) {
					$existing[ $key ] = $def;
				}
				return $existing;
			}
		}
	endif;

	$registrar->register( new \Elementor_Pro_Form_Extends_Field_Attributes() );
} );

/**
 * Enable Dynamic Tags for the Form Name control.
 */
add_action(
	'elementor/element/form/section_form_fields/before_section_end',
	function( $widget ) {
		if ( ! method_exists( $widget, 'update_control' ) ) {
			return;
		}
		$widget->update_control( 'form_name', [
			'dynamic' => [ 'active' => true ],
		] );
	},
	10
);
