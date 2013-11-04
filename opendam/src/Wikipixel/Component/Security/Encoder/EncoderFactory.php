<?php
namespace Wikipixel\Component\Security\Encoder;

use Wikipixel\Component\Security\User\UserInterface;

class EncoderFactory
{
	private $encoders;

	public function __construct(array $encoders)
	{
		$this->encoders = $encoders;
	}

	public function getEncoder(UserInterface $user)
	{
		$encoderType = $user->getPasswordEncoderType();
		
		foreach ($this->encoders as $id => $encoder) {
			if ($encoderType == $id) {
				return $encoder;
			}
		}

		throw new \RuntimeException(sprintf('No encoder has been configured for account "%s".', is_object($user) ? get_class($user) : $user));
	}
}
