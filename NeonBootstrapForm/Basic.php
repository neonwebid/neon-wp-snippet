<?php

namespace NeonBootstrapForm;

/**
 * Bootstrap Form Builder.
 *
 * @package    NeonBootstrapForm
 * @version    1.0.0
 * @requires   PHP 7.4.0
 * @license    MIT License
 * @link       https://neon.web.id/bootstrap-form-builder
 * @author     neonwebid <https://neon.web.id>
 * @contact    support@neon.web.id or neonwpdev@gmail.com
 */
class Basic {

	private static ?Basic $create = null;

	private string $id;

	private array $config;

	private array $base_config;

	public function __construct( string $id, array $config ) {
		$this->id     = $id;
		$this->config = $config;
	}

	public static function create( string $id, array $config ) {
		if ( ! self::$create instanceof self ) {
			self::$create = new self( $id, $config );
		}

		echo self::$create->render();
	}

	/**
	 * @param $field_args array
	 *
	 * @return string
	 */
	private function parseAttributes( array $field_args ): string {
		$output = '';

		$attributes = [
			'placeholder' => ''
		];

		if ( ! empty( $field_args['attributes'] ) ) {
			$attributes = array_merge( $attributes, $field_args['attributes'] );
		}

		$placeholder_only_on = [
			'email',
			'number',
			'password',
			'search',
			'tel',
			'text',
			'url',
			'textarea'
		];

		// remove placeholder
		if ( ! in_array( $field_args['type'], $placeholder_only_on ) ) {
			unset( $attributes['placeholder'] );
		}

		foreach ( $attributes as $property => $value ) {
			$output .= " {$property}=\"{$value}\"";
		}

		// tooltip help
		$output .= ! empty( $field_args['help'] )
			? ' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="' . $field_args['help'] . '"'
			: '';

		if ( ! empty( $field_args['dependency'] ) && is_array( $field_args['dependency'] ) ) {
			$dependencies = [];

			foreach ($field_args['dependency'] as $field_id => $match_value ) {
				$dependencies[ "{$this->id}_{$field_id}" ] = $match_value;
			}

			$output .= " data-dependency='" . json_encode( $dependencies ) . "'";
		}


		return $output;
	}

	/**
	 * @param $field_arg_desc string
	 *
	 * @return string
	 */
	private function desc( string $field_arg_desc ): string {
		return $field_arg_desc ? '<div class="desc fst-italic text-muted fs-6">' . $field_arg_desc . '</div>' : '';
	}

	/**
	 * @param string $title
	 * @param string $for
	 * @param string $classes
	 *
	 * @return string
	 */
	private function label( string $title, string $for, string $classes ): string {
		$label = '<label for="' . $for . '" class="' . $classes . '"';

		return $label . '>' . $title . '</label>';
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	private function invalidMessage( string $message ): string {
		return $this->base_config['needs_validate'] && $message ? ' <div class="invalid-feedback">' . $message . '</div>' : '';
	}

	private function field( array $field_args ): string {
		$value = [
			'title'       => '',
			'type'        => '',
			'value'       => '',
			'desc'        => '',
			'help'        => '',
			'class'       => '',
			'invalid_msg' => '',
			'attributes'  => [],
			'dependency'  => [],
		];

		$field_args = array_merge( $value, $field_args );

		if ( ! isset( $field_args['id'] ) || ! isset( $field_args['type'] ) ) {
			return '';
		}

		$field_args['field_id'] = "{$this->id}_{$field_args['id']}";
		$field                  = '';

		switch ( $field_args['type'] ) {
			case 'text':
			case 'email':
			case 'password':
			case 'number':
			case 'date':
			case 'hidden':
				$field .= $this->input( $field_args );
				break;

			case 'select':
				$field .= $this->select( $field_args );
				break;

			case 'checkbox':
			case 'radio':
				$field .= $this->checkboxOrRadio( $field_args );
				break;

			case 'switch':
				$field .= $this->switch( $field_args );
				break;

			case 'range':
				$field .= $this->range( $field_args );
				break;

			case 'textarea':
				$field .= $this->textarea( $field_args );
				break;

			case 'color-picker':
				$field .= $this->colorPicker( $field_args );
				break;

			case 'button':
				$field .= $this->button( $field_args );
				break;

			case 'group':
				$field .= $this->group( $field_args );
				break;

			case 'info':
				$field .= $this->info( $field_args );
				break;
		}

		return $field;
	}


	private function input( array $field_args ): string {

		$input = sprintf( '<input type="%s" class="%s" id="%s" name="%s"',
			$field_args['type'],
			trim( $field_args['class'] . ' form-control' ),
			$field_args['field_id'],
			$field_args['field_id']
		);

		if ( ! empty( $field_args['value'] ) ) {
			$input .= " value=\"{$field_args['value']}\"";
		}

		$input .= $this->parseAttributes( $field_args );

		$input = trim( $input ) . '>';

		return '<div class="form-floating mb-3" id="form-element-'. $field_args['field_id'] .'">'
		       . $input
		       . $this->invalidMessage( $field_args['invalid_msg'] )
		       . $this->label( $field_args['title'], $field_args['field_id'], 'form-label' )
		       . $this->desc( $field_args['desc'] )
		       . '</div>';

	}

	private function select( array $field_args ): string {

		$select = sprintf( '<select class="%s" name="%s" id="%s" ',
			trim( $field_args['class'] . ' form-select' ),
			$field_args['field_id'],
			$field_args['field_id']
		);

		$select .= $this->parseAttributes( $field_args );

		$select = trim( $select ) . '>';

		$options = isset( $field_args['options'] ) && is_array( $field_args['options'] )
			? $field_args['options']
			: [];

		if ( $options ) {
			$placeholder = "Select {$field_args['title']}";
			if ( ! empty( $field_args['attributes']['placeholder'] ) ) {
				$placeholder = $field_args['attributes']['placeholder'];
			}

			$select .= "<option>{$placeholder}</option>";
		}

		foreach ( $options as $value => $label_option ) {
			$select .= sprintf( '<option value="%s">%s</option>', $value, $label_option );
		}

		$select .= '</select>';

		return '<div class="form-floating mb-3" id="form-element-'. $field_args['field_id'] .'">'
		       . $select
		       . $this->label( $field_args['title'], $field_args['field_id'], 'form-label' )
		       . $this->desc( $field_args['desc'] )
		       . $this->invalidMessage( $field_args['invalid_msg'] )
		       . '</div>';
	}

	private function checkboxOrRadio( array $field_args ): string {

		$checkbox_or_radio = sprintf(
			'<input class="form-check-input" type="%s" value="%s" id="%s"',
			$field_args['type'],
			$field_args['value'],
			$field_args['field_id']
		);


		$checkbox_or_radio .= $this->parseAttributes( $field_args );

		$checkbox_or_radio .= '/>';

		return '<div class="form-floating mb-3" id="form-element-'. $field_args['field_id'] .'"><div class="form-check">'
		       . $checkbox_or_radio
		       . $this->label( $field_args['title'], $field_args['field_id'], 'form-check-label' )
		       . '</div>' . $this->desc( $field_args['desc'] ) . '</div>';
	}

	private function switch( array $field_args ): string {

		$switch = sprintf(
			'<input class="form-check-input" role="switch" type="checkbox" value="%s" id="%s"',
			$field_args['value'],
			$field_args['field_id']
		);


		$switch .= $this->parseAttributes( $field_args );

		$switch .= '/>';

		return '<div class="form-floating mb-3" id="form-element-'. $field_args['field_id'] .'"><div class="form-check form-switch">'
		       . $switch
		       . $this->label( $field_args['title'], $field_args['field_id'], 'form-label' )
		       . $this->invalidMessage( $field_args['invalid_msg'] )
		       . '</div>' . $this->desc( $field_args['desc'] ) . '</div>';
	}

	private function range( array $field_args ): string {

		$range = sprintf(
			'<input class="form-range" type="range" value="%s" id="%s"',
			$field_args['value'],
			$field_args['field_id']
		);

		$range .= $this->parseAttributes( $field_args );

		$range .= '/>';

		return '<div class="mb-3" id="form-element-'. $field_args['field_id'] .'">'
		       . $this->label( $field_args['title'], $field_args['field_id'], 'form-label' )
		       . $range
		       . $this->invalidMessage( $field_args['invalid_msg'] )
		       . $this->desc( $field_args['desc'] ) . '</div>';
	}

	private function textarea( array $field_args ): string {

		$textarea = sprintf(
			'<textarea class="%s" id="%s" name="%s"',
			trim( $field_args['class'] . ' form-control' ),
			$field_args['field_id'],
			$field_args['field_id']
		);

		$textarea .= $this->parseAttributes( $field_args );
		$textarea .= ">{$field_args['value']}</textarea>";

		return '<div class="form-floating mb-3" id="form-element-'. $field_args['field_id'] .'">'
		       . $textarea
		       . $this->invalidMessage( $field_args['invalid_msg'] )
		       . $this->label( $field_args['title'], $field_args['field_id'], 'form-label' )
		       . $this->desc( $field_args['desc'] ) . '</div>';
	}

	private function colorPicker( array $field_args ): string {

		$color = sprintf(
			'<input class="form-control form-control-color" type="color" value="%s" id="%s" title="%s"',
			$field_args['value'],
			$field_args['field_id'],
			$field_args['title']
		);


		$color .= $this->parseAttributes( $field_args );

		$color .= '/>';

		return '<div class="mb-3" id="form-element-'. $field_args['field_id'] .'">'
		       . $this->label( $field_args['title'], $field_args['field_id'], 'form-label' )
		       . $color
		       . $this->desc( $field_args['desc'] )
		       . '</div>';
	}

	private function button( array $field_args ): string {
		$button_types = [
			'button',
			'submit',
		];

		$button = '';

		if ( isset( $field_args['button-group'] ) && is_array( $field_args['button-group'] ) ) {
			foreach ( $field_args['button-group'] as $button_group ) {
				$button_type = ! empty( $button_types[ $button_group['button-type'] ] )
					? $button_types[ $button_group['button-type'] ] : 'button';

				$button_class = ! empty( $button_group['button-class'] )
					? implode( ' ', $button_group['button-class'] )
					: '';

				$button .= sprintf( '<button type="%s" class="btn %s" %s>%s</button>',
					$button_type,
					$button_class,
					$this->parseAttributes( $button_group ),
					$button_group['title']
				);
			}

			$button = '<div class="btn-group">' . $button . '</div>';

		} else {

			$button_type = in_array( $field_args['button-type'], $button_types )
				? $field_args['button-type'] : 'button';

			$button_class = ! empty( $field_args['button-class'] )
				? implode( ' ', $field_args['button-class'] )
				: '';

			$button = sprintf( '<button type="%s" class="btn %s" %s>%s</button>',
				$button_type,
				$button_class,
				$this->parseAttributes( $field_args ),
				$field_args['title']
			);
		}


		return '<div class="mb-3" id="form-element-'. $field_args['field_id'] .'">' . $button . '</div>';
	}

	private function info( array $field_args ): string {
		$info_types = [
			'heading'    => 'fs-1',
			'subheading' => 'fs-2',
			'monospace'  => 'font-monospace'
		];

		$info_style_types = [
			'normal'  => 'text-normal',
			'success' => 'text-success',
			'info'    => 'text-info',
			'warning' => 'text-warning',
			'danger'  => 'text-danger'
		];

		$content = sprintf( '<div class="%s">%s</div>',
			$info_style_types[ $field_args['info-style'] ],
			$field_args['content']
		);

		$info = sprintf( '<div class="%s">%s</div>',
			$info_types[ $field_args['info-type'] ],
			$content
		);

		return '<div class="mb-3" id="form-element-'. $field_args['field_id'] .'">' . $info . '</div>';
	}

	private function group( array $field_args ): string {

		$group = '';
		if ( is_array( $field_args['fields'] ) ) {
			foreach ( $field_args['fields'] as $field_args ) {
				$group .= $this->field( $field_args );
			}
		}

		return $group;
	}

	private function render(): string {
		$value = [
			'title'          => '',
			'action'         => '',
			'method'         => 'get',
			'enctype'        => 'application/x-www-form-urlencoded',
			'class'          => 'neon-from',
			'style'          => 'form-floating', // normal, form-floating, inline
			'needs_validate' => false,
			'attributes'     => []
		];

		$this->base_config = $base_config = array_merge( $value, $this->config );
		$title             = $this->base_config['title'];
		$style             = $this->base_config['style'];
		$attributes        = $this->base_config['attributes'];

		unset( $base_config['title'], $base_config['style'], $base_config['attributes'] );

		if ( empty( $this->id ) || ! isset( $base_config['fields'] ) ) {
			return '';
		}


		$fields = $base_config['fields'];
		unset( $base_config['fields'] );


		$form = "<form id='{$this->id}' ";

		$validate_js_handler = "";
		if ( $base_config['needs_validate'] ) {
			$form                 .= "novalidate ";
			$base_config['class'] = $base_config['class'] . ' needs-validation';
			$validate_js_handler  = "
			<script>(() => {
			  'use strict'
			  const forms = document.querySelectorAll('.needs-validation')
			  
			  Array.from(forms).forEach(form => {
			    form.addEventListener('submit', event => {
			      if (!form.checkValidity()) {
			        event.preventDefault()
			        event.stopPropagation()
			      }
			
			      form.classList.add('was-validated')
			    }, false)
			  })
			})()
			</script>
			";
		}

		unset( $base_config['needs_validate'] );

		foreach ( $base_config as $property => $value ) {
			$form .= "{$property}='{$value}' ";
		}

		if ( is_array( $attributes ) ) {
			foreach ( $attributes as $property => $value ) {
				$form .= "{$property}='{$value}' ";
			}
		}


		$form = trim( $form ) . '>';

		// prevent duplicate id
		$id_has_been_used = [];
		if ( is_array( $fields ) ) {
			foreach ( $fields as $field_args ) {
				if ( ! in_array($field_args['id'], $id_has_been_used) ) {
					$form .= $this->field( $field_args );
					$id_has_been_used[] = $field_args['id'];
				}
			}
		}

		return $form . '</form>' . $validate_js_handler;
	}
}

/**
 * How to use
 *
 * MUST $added https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css before </head>
 * MUST $added https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js before </body>
 *
 * For Tooltips and dependency
 * <script>
 * const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
 * const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
 * function dependencyHandler() {
 * let elements = document.querySelectorAll('[data-dependency]');
 * elements.forEach(function(element) {
 * let dependencyAttribute = element.getAttribute('data-dependency');
 * let dependencyData = JSON.parse(dependencyAttribute);
 *
 * let dependentIsValue = [];
 * for (let key in dependencyData) {
 * if (dependencyData.hasOwnProperty(key)) {
 * let value = dependencyData[key];
 *
 * let dependencyElement = document.getElementById(key);
 * dependentIsValue.push( ( dependencyElement.checked || dependencyElement.value === value ) );
 *
 * }
 * }
 *
 * let dependentIsValid = dependentIsValue.every(function(element) {
 * return element === true;
 * });
 *
 * if ( dependentIsValid ) {
 * element.classList.remove('d-none');
 * element.closest( '#form-element-' + element.getAttribute('id') ).classList.remove('d-none');
 * } else {
 * element.classList.add('d-none');
 * element.closest( '#form-element-' + element.getAttribute('id') ).classList.add('d-none');
 * }
 * });
 * }
 *
 * function elementDependencyActions(element) {
 * element.addEventListener('change', function(event) {
 * dependencyHandler();
 * });
 * }
 *
 * dependencyHandler();
 *
 * let radioOnChange    = document.querySelectorAll('input[type="radio"]');
 * let checkboxOnChange = document.querySelectorAll('input[type="checkbox"]');
 * let selectOnChange   = document.querySelectorAll('select');
 *
 * radioOnChange.forEach(elementDependencyActions);
 * checkboxOnChange.forEach(elementDependencyActions);
 * selectOnChange.forEach(elementDependencyActions);
 * </script>
 */
//Basic::create( 'form-login', [
//	'title'  => 'Form Login',
//	'fields' => [
//		[
//			'id'         => '',
//			'title'      => '',
//			'type'       => '',
//			'value'    => '',
//			'desc'       => '',
//			'help'       => '',
//			'class'      => '',
//			'attributes' => '',
//			'dependency' => '',
//		],
//	]
//] );

