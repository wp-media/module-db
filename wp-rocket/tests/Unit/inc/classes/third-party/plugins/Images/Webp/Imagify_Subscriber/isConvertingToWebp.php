<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::is_converting_to_webp
 * @group  ThirdParty
 * @group  Webp
 */
class Test_IsConvertingToWebp extends TestCase {

	public function testShouldReturnFalseWhenImagifyOptionNotEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		$this->assertFalse( $subscriber->is_converting_to_webp() );

		Functions\expect( 'get_imagify_option' )
			->once()
			->andReturn( false );

		$this->assertFalse( $subscriber->is_converting_to_webp() );
	}

	public function testShouldReturnTrueWhenImagifyOptionIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Functions\expect( 'get_imagify_option' )
			->once()
			->andReturn( true );

		$this->assertTrue( $subscriber->is_converting_to_webp() );
	}
}
