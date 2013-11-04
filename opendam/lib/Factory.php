<?php 

use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

use Wikipixel\Component\Security\Encoder\PasswordEncoderAdapter;

class Factory {
	public static function getPasswordEncoder()
	{
		return new Security_Encoder_Md5PasswordEncoder();
	}
	
	public static function getUserPasswordValidator()
	{
		return new UserPasswordValidator();
	}
	
	public static function getPasswordAdapter()
	{
		$md5Encoder = new MessageDigestPasswordEncoder('md5', false, 1);
		$bCryptEncoder = new BCryptPasswordEncoder(12);
		
		$encoders = array(
				PasswordEncoderAdapter::TYPE_MD5 => $md5Encoder,
				PasswordEncoderAdapter::TYPE_BCRYPT => $bCryptEncoder,
		);
		
		
		return new PasswordEncoderAdapter($encoders);
	}
}
?>