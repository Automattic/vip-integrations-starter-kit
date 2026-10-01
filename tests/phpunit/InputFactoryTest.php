<?php

namespace ExampleVendor\ExampleIntegration;

use WP_UnitTestCase;

/**
 * @covers \ExampleVendor\ExampleIntegration\InputFactory
 */
class InputFactoryTest extends WP_UnitTestCase {
	/** @var InputFactory */
	private $input_factory;

	public function setUp(): void {
		parent::setUp();
		$this->input_factory = new InputFactory( 'option', [ 'somekey' => 'somevalue' ] );
	}

	public function test_input(): void {
		$output = $this->render( fn () => $this->input_factory->input( [
			'label_for'    => 'somekey',
			'autocomplete' => 'off',
			'help'         => 'Help Text',
		] ) );

		self::assertStringContainsString( '<p class="help">Help Text</p>', $output );
		self::assertStringContainsString( 'autocomplete="off"', $output );
		self::assertStringContainsString( 'id="somekey"', $output );
		self::assertStringContainsString( 'name="option[somekey]"', $output );
	}

	/**
	 * @dataProvider data_checkbox
	 */
	public function test_checkbox( bool $checked ): void {
		$this->input_factory = new InputFactory( 'option', [ 'somekey' => $checked ] );
		$output              = $this->render( fn () => $this->input_factory->checkbox( [ 'label_for' => 'somekey' ] ) );

		self::assertStringNotContainsString( '<p class="help">', $output );
		self::assertStringContainsString( '<input type="hidden" name="option[somekey]" value="0"/>', $output );
		self::assertStringContainsString( 'type="checkbox"', $output );
		self::assertStringContainsString( 'name="option[somekey]" id="somekey" value="1"', $output );

		if ( $checked ) {
			self::assertStringContainsString( "checked='checked'", $output );
		} else {
			self::assertStringNotContainsString( "checked='checked'", $output );
		}
	}

	/**
	 * @return iterable<array-key,array{bool}>
	 */
	public function data_checkbox(): iterable {
		return [
			[ true ],
			[ false ],
		];
	}

	public function test_get_attributes_non_scalar(): void {
		$output = $this->render( fn () => $this->input_factory->input( [
			'label_for'    => 'somekey',
			'autocomplete' => [ 1, 2, 3 ],
		] ) );

		self::assertStringNotContainsString( 'autocomplete', $output );
	}

	public function test_get_attributes_false(): void {
		$output = $this->render( fn () => $this->input_factory->input( [
			'label_for' => 'somekey',
			'required'  => false,
		] ) );

		self::assertStringNotContainsString( 'required', $output );
	}

	public function test_get_attributes_true(): void {
		$output = $this->render( fn () => $this->input_factory->input( [
			'label_for' => 'somekey',
			'required'  => true,
		] ) );

		self::assertStringContainsString( 'required="required', $output );
	}

	private function render( callable $render_field ): string {
		ob_start();
		$render_field();
		$result = ob_get_clean();

		self::assertIsString( $result );
		return $result;
	}
}
