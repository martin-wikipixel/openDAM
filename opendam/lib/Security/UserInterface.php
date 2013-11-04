<?php 
/**
 * User interface basé sur symfony 2.
 */
interface Security_UserInterface {
	public function getRoles();
	public function getPassword();
	public function getSalt();
	public function getUsername();
	public function eraseCredentials();
}
?>