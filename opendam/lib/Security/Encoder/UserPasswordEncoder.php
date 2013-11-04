<?php

interface UserPasswordValidatorInterface
{
	public function isPasswordValid(User $user, $raw);
}
