<?php

namespace WP_Rocket\Tests\Integration;

define( 'DB_MODULE_ROOT', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_PLUGIN_ROOT', DB_MODULE_ROOT );
define( 'DB_MODULE_TESTS_FIXTURES_DIR', dirname( __DIR__ ) . '/Fixtures' );
define( 'WP_ROCKET_TESTS_FIXTURES_DIR', DB_MODULE_TESTS_FIXTURES_DIR );
define( 'DB_MODULE_TESTS_DIR', __DIR__ );
define( 'WP_ROCKET_TESTS_DIR', __DIR__ );

// Manually load the plugin being tested.
tests_add_filter(
	'muplugins_loaded',
	function() {
		// bootstrap the module here.
	}
);
