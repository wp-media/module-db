<?php
namespace  WP_Rocket\Engine\DB\Cache;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket DB
 *
 * @since 1.0.0
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'cache_controller',
		'cache_subscriber',
	];

	/**
	 * Registers the services in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'cache_controller', 'WP_Rocket\Engine\DB\Cache\Controller' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'cache_subscriber', 'WP_Rocket\Engine\DB\Cache\Subscriber' )
			->withArgument( $this->getContainer()->get( 'cache_controller' ) );
	}
}
