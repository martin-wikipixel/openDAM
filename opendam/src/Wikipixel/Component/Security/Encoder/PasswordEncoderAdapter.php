<?php 
namespace Wikipixel\Component\Security\Encoder;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Wikipixel\Component\Security\User\UserInterface;

class PasswordEncoderAdapter
{
	const TYPE_MD5 = 1;
	const TYPE_BCRYPT = 2;
	
	protected $encoders;
	
	/*________________________________________________________________________________________________________________*/
	private function getEncoder(UserInterface $user)
	{
		$encoderType = $user->getPasswordEncoderType();
		
		foreach ($this->encoders as $id => $encoder) {
			if ($encoderType == $id) {
				return $encoder;
			}
		}

		throw new \RuntimeException(sprintf('No encoder has been configured for account "%s".', is_object($user) ? get_class($user) : $user));
	}
	
	/*________________________________________________________________________________________________________________*/
	public function __construct(array $encoders)
	{
		$this->encoders = $encoders;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function encodePassword(UserInterface $user, $raw)
	{
		if ($user->getPasswordEncoderType() == self::TYPE_MD5) {
			$user->setPasswordEncoderType(self::TYPE_BCRYPT);
		}
		
		return $this->getEncoder($user)->encodePassword($raw, "");
	}

	/*________________________________________________________________________________________________________________*/
	public function isPasswordValid(UserInterface $user, $raw)
	{
		return $this->getEncoder($user)->isPasswordValid($user->getPassword(), $raw, "");
	}
}