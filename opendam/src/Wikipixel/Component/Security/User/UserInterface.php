<?php 
namespace Wikipixel\Component\Security\User;

use Symfony\Component\Security\Core\User\UserInterface as Symfony_UserInterface;

interface UserInterface extends Symfony_UserInterface
{
	public function getPasswordEncoderType();
}