<?php
class WebCacheTest extends PHPUnit\Framework\TestCase {

	public function provideKeySegments() {
		return [
			[ 'foo-2bar', 'foo-2bar' ],
			[ 'foo/bar/', 'foo_2Fbar_2F' ],
		];
	}

	/**
	 * @dataProvider provideKeySegments
	 */
	public function testEscapeKeySegment($input, $escaped) {
		$this->assertEquals(
			$escaped,
			WebCache::escapeKeySegment($input)
		);
		$this->assertTrue(WebCache::validKey($escaped));
	}
}
