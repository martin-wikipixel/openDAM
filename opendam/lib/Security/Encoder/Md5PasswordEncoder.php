<?php

/**
 * Md5 password encoder pour garder la comptatibilité avec l'existant.
 *
 */
class  Security_Encoder_Md5PasswordEncoder extends Security_Encoder_BasePasswordEncoder
{
	public function encodePassword($raw, $salt)
	{
		return md5($raw);
	}

	public function isPasswordValid($encoded, $raw, $salt)
	{
		return md5($raw) == $encoded;
	}
}
