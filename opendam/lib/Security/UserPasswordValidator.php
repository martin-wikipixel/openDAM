<?php

class UserPasswordValidator
{
	/**
	 * Renvoi vrai si le mot de passe est valide.
	 * 
	 * @param User $user
	 * @param unknown $raw Le mot de passe en claire.
	 */
	public function isPasswordValid(Security_UserInterface $user, $raw)
	{
		$encoder = new Security_Encoder_Md5PasswordEncoder();
		
		return $encoder->isPasswordValid($user->getPassword(), $raw, $user->getSalt());
	}
}
