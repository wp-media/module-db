<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AsyncCSS;

use WP_Rocket\Engine\CriticalPath\AsyncCSS;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AsyncCSS::modify_html
 * @uses   \WP_Rocket\Engine\CriticalPath\AsyncCSS::from_html
 * @uses   \WP_Rocket\Engine\DOM\HTMLDocument::query
 * @uses   \WP_Rocket\Engine\DOM\HTMLDocument::get_html
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_current_page_critical_css
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_exclude_async_css
 * @uses   \WP_Rocket\Admin\Options_Data::get
 * @uses   ::rocket_get_constant
 * @uses   ::is_rocket_post_excluded_option
 *
 * @group  CriticalPath
 * @group  AsyncCSS
 * @group  DOM
 */
class Test_ModifyHtml extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/AsyncCSS/modifyHtml.php';

	protected static $container;
	protected static $use_settings_trait = true;

	protected $critical_css;
	protected $instance;
	protected $options;
	protected $test_config = [];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', null );
	}

	public function setUp() {
		parent::setUp();

		set_current_screen( 'front' );

		$this->options      = self::$container->get( 'options' );
		$this->critical_css = self::$container->get( 'critical_css' );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'rocket_exclude_async_css', [ $this, 'rocket_exclude_async_css' ] );
		remove_filter( "pre_get_rocket_option_async_css", [ $this, 'set_option_async_css' ] );

		$this->test_config = [];
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAsyncCss( $html, $expected, $config = [] ) {
		$this->setUpTest( $html, $config, ! is_null( $expected ) );

		if ( is_null( $expected ) ) {
			$this->assertNull( $this->instance );

			return;
		}

		$this->assertInstanceOf( AsyncCSS::class, $this->instance );
		$actual = $this->instance->modify_html( $html );

		$this->assertEquals(
			$this->format_the_html( $expected ),
			$this->format_the_html( $actual )
		);
	}

	protected function setUpTest( $html, array $config = [], $should_create_instance = true ) {
		$this->test_config = $this->initConfig( $config );

		$this->mergeExistingSettingsAndUpdate( $this->test_config ['options'] );

		add_filter( "pre_get_rocket_option_async_css", [ $this, 'set_option_async_css' ] );
		add_filter( 'rocket_exclude_async_css', [ $this, 'rocket_exclude_async_css' ] );

		$this->setUpUrl( $should_create_instance );

		$this->instance = AsyncCSS::from_html( $this->critical_css, $this->options, $html );
	}

	protected function setUpUrl( $should_create_instance ) {
		$post_id = $this->factory->post->create();
		if ( $should_create_instance ) {
			update_option( 'show_on_front', 'page' );
			$this->go_to( home_url() );
		} else {
			$this->go_to( get_permalink( $post_id ) );
		}
	}

	public function set_option_async_css( $value ) {
		if ( isset( $this->test_config['options']['async_css'] ) ) {
			return $this->test_config['options']['async_css'];
		}

		return $value;
	}

	public function rocket_exclude_async_css( $value ) {
		if ( ! isset( $this->test_config['critical_css']['get_exclude_async_css'] ) ) {
			return $value;
		}

		if ( empty( $this->test_config['critical_css']['get_exclude_async_css'] ) ) {
			return $value;
		}

		return $this->test_config['critical_css']['get_exclude_async_css'];
	}

	protected function initConfig( $config ) {
		if ( empty( $config ) ) {
			return $this->config['default_config'];
		}

		if ( isset( $config['use_default'] ) && $config['use_default'] ) {
			unset( $config['use_default'] );

			return array_merge_recursive(
				$this->config['default_config'],
				$config
			);
		}

		return array_merge(
			[
				'options'      => [],
				'critical_css' => [],
				'functions'    => [],
			],
			$config
		);
	}
}
