<?php

class backendConfiguration extends sfApplicationConfiguration
{
	public function configure()
	{
		assert_options(ASSERT_ACTIVE,   true);
		assert_options(ASSERT_BAIL,     true);
		assert_options(ASSERT_WARNING,  true);
	
		set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/../../../lib'));
	}
}
