<?php 
class Assert 
{
	private static function isActive()
	{
		return assert_options(ASSERT_ACTIVE);
	}
	
	public static function ok($test)
	{
		if (!self::isActive()) {
			return;
		}

		if (!$test) {
			throw new AssertionException("Assertion failed", 0);
		}
	}
}
?>