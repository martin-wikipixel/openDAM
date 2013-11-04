<?php 

class SecurityUtils
{
	public static function getCsrfToken()
	{
		$secret = sfConfig::get("sf_csrf_secret");
		
		return md5(uniqid($secret.session_id(), true));
	}
	
	public static function checkCsrfToken($name = "csrfToken")
	{
		$request = sfContext::getInstance()->getRequest();
		$suser = sfContext::getInstance()->getUser();
			
		if ($suser->getCsrfToken($name) != $request->getParameter($name)) {
			$suser->removeCsrfToken();
			throw new CsrfException();
		}
	}
}