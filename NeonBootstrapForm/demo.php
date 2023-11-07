<?php

use NeonBootstrapForm\Basic;

require_once 'Basic.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <h1>Hello, world!</h1>
	<?php

	Basic::create( 'login', [
		'title'  => 'Form Login',
		'action' => 'index.php',
        'needs_validate' => true,
		'method' => 'post',
		'fields' => [
			[
				'id'         => 'username',
				'title'      => 'Username',
				'type'       => 'text',
				'desc'       => 'masukkan username anda',
				'help'       => 'username',
				'class'      => 'username-field',
				'attributes' => [
					'placeholder' => 'john lenon',
                    'required' => 'required'
				],
                'invalid_msg' => 'wajib do isi',
				'dependency' => '',
			],
			[
				'id'         => 'password',
				'title'      => 'Password',
				'type'       => 'password',
				'desc'       => 'masukkan username anda',
				'class'      => 'password-field',
				'attributes' => [
					'placeholder' => 'john lenon'
				],
				'dependency' => '',
			],
            [
				'id'         => 'password',
				'title'      => 'Email',
				'type'       => 'email',
				'desc'       => 'masukkan username anda',
				'class'      => 'email-field',
				'attributes' => [
					'placeholder' => 'john lenon'
				],
				'dependency' => '',
			],
			[
				'id'         => 'remember_me',
				'title'      => 'Remember Me',
				'type'       => 'switch',
				'value'      => '1',
				'desc'       => 'masukkan username anda',
				'dependency' => '',
			],
			[
				'id'         => 'aktif_me',
				'title'      => 'Aktif Me',
				'type'       => 'switch',
				'value'      => '1',
				'desc'       => 'masukkan username anda',
				'dependency' => '',
			],
			[
				'id'         => 'color_picker',
				'title'      => 'Pilih Warna',
				'type'       => 'color-picker',
				'value'      => '#000000',
				'dependency' => [
					'remember_me' => true,
                    'aktif_me' => true
				],
			],
			[
				'id'         => 'info',
				'title'      => 'Info',
				'type'       => 'info',
				'info-style' => 'warning',
				'info-type'  => 'monospace',
				'content'    => 'lorem ipsum dolor',
			],
			[
				'id'         => 'textarea',
				'title'      => 'Textarea',
				'type'       => 'textarea',
				'attributes' => [
					'placeholder' => ''
				]
			],
			[
				'id'      => 'roles',
				'title'   => 'User Role',
				'type'    => 'select',
				'options' => [
					'index' => '0',
					'1'     => '1',
				],
			],
			[
				'id'         => 'range',
				'title'      => 'User Role',
				'type'       => 'range',
                'value'      => 1,
				'attributes' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 5
				]
			],
			[
				'id'           => 'save',
				'title'        => 'Save',
				'type'         => 'button',
				'button-class' => [ 'btn-primary' ],
				'button-type'  => 'submit',
			],
			[
				'id'           => 'group',
				'title'        => 'Group',
				'type'         => 'button',
				'button-group' => [
					[
						'id'           => 'cancel',
						'title'        => 'Cancel',
						'type'         => 'button',
						'button-class' => [ 'btn-secondary' ],
						'button-type'  => 'submit',
					],
					[
						'id'           => 'cancel',
						'title'        => 'Info',
						'type'         => 'button',
						'button-class' => [ 'btn-dark' ],
						'button-type'  => 'submit',
					]
				]
			],
		]
	] );

	?>

</div>

<script src="//cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3"
        crossorigin="anonymous"></script>
<script src="//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    function dependencyHandler() {
        let elements = document.querySelectorAll('[data-dependency]');
        elements.forEach(function(element) {
            let dependencyAttribute = element.getAttribute('data-dependency');
            let dependencyData = JSON.parse(dependencyAttribute);

            let dependentIsValue = [];
            for (let key in dependencyData) {
                if (dependencyData.hasOwnProperty(key)) {
                    let value = dependencyData[key];

                    let dependencyElement = document.getElementById(key);
                    dependentIsValue.push( ( dependencyElement.checked || dependencyElement.value === value ) );

                }
            }

            let dependentIsValid = dependentIsValue.every(function(element) {
                return element === true;
            });

            if ( dependentIsValid ) {
                element.classList.remove('d-none');
                element.closest( '#form-element-' + element.getAttribute('id') ).classList.remove('d-none');
            } else {
                element.classList.add('d-none');
                element.closest( '#form-element-' + element.getAttribute('id') ).classList.add('d-none');
            }
        });
    }

    function elementDependencyActions(element) {
        element.addEventListener('change', function(event) {
            dependencyHandler();
        });
    }

    dependencyHandler();

    let radioOnChange    = document.querySelectorAll('input[type="radio"]');
    let checkboxOnChange = document.querySelectorAll('input[type="checkbox"]');
    let selectOnChange   = document.querySelectorAll('select');

    radioOnChange.forEach(elementDependencyActions);
    checkboxOnChange.forEach(elementDependencyActions);
    selectOnChange.forEach(elementDependencyActions);

</script>
</body>
</html>
