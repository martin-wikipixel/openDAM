<?php 
class CsrfException extends Exception
{
	public function __construct()
	{
		$this->message = "CSRF attack detected";
		$this->code = 0;
	}
}
?>