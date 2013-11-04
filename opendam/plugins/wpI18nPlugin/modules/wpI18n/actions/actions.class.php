<?php

/**
 * i18n_js actions.
 *
 * @package    wikipixel
 * @subpackage i18n_js
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class wpI18nActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	private function getLocale(sfWebRequest $request)
	{
		$locale = $request->getParameter("locale");
		$suser = $this->getUser();
		
		if ($suser->isAuthenticated()) {
			$locale = $suser->getCulture();
		}
		else {
			$locale = $request->getPreferredCulture();
			
			if (strlen($locale) > 2) {
				$locale = substr($locale, 0, strpos($locale, "_"));
			}
		}
		
		return $locale;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function getContent($locale)
	{
		$i18n = sfContext::getInstance()->getI18N();
		$messageSource = $i18n->createMessageSource(sfConfig::get("sf_app_i18n_dir"));
		$catalogues = $messageSource->catalogues();
		
		$cataloguesSelected = array();
		
		foreach ($catalogues as $catalogue) {
			$lang = $catalogue[1];
				
			if ($lang == $locale) {
				$cataloguesSelected[] = $catalogue;
			}
		}
		
		if (!count($cataloguesSelected)) {
			$this->forward404();
		}
		
		$traductions = array();
		
		foreach ($cataloguesSelected as $catalogue) {
			$name = $catalogue[0];
		
			$traductions[$name] = array();
				
			$messageSource->setCulture($locale);
			$messageSource->load($name);
				
			$messages = $messageSource->read();
			$variants = $messageSource->getCatalogueList($name);
				
			foreach ($variants as $variant) {
				if (isset($messages[$variant])) {
					$traductions[$name] = array_merge($traductions[$name], $messages[$variant]);
				}
			}
		}

		return sfAction::getPartial("wpI18n/catalogues", array("locale" => $locale, "traductions" => $traductions));
	}
	
	/*________________________________________________________________________________________________________________*/
	public function writeInCache($pathname, $content)
	{
		$dir = dirname($pathname);
		
		if (!file_exists($dir)) {
			//mkdir($dir, 0777, true);// fct pas !!
			mkdir($dir);
			chmod($dir, 0777);
		}
		
		file_put_contents($pathname, $content);
	}
	
	/*________________________________________________________________________________________________________________*/
	public function readInCache($response, $pathname)
	{
		if (!file_exists($pathname)) {
			$response->setStatusCode(404);
		}
		else {
			$response->setContent(file_get_contents($pathname));
			/*
			return;
			$buffer = 4096;
			$fd = fopen($pathname, "rb");
	
			while (!feof($fd)) {
				$bytes = fread($fd, $buffer);
	
				if ($bytes !== false) {
					$response->setContent($bytes);
					$response->send();
				}
			}
	
			fclose($fd);
			*/
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function executeCatalogues(sfWebRequest $request)
	{
		$useCache = sfConfig::get("app_wpI18n_use_cache");
		$locale = $this->getLocale($request);

		$cacheDir = sfConfig::get("sf_cache_dir").DIRECTORY_SEPARATOR.
			$this->getContext()->getConfiguration()->getApplication().DIRECTORY_SEPARATOR.
			$this->getContext()->getConfiguration()->getEnvironment().DIRECTORY_SEPARATOR."wpI18n";

		$response = $this->getResponse();
		$response->setContentType("application/javascript");

		$response->addCacheControlHttpHeader("private");
		
		if ($useCache) {
			$pathnameInCache = $cacheDir.DIRECTORY_SEPARATOR."messages.".$locale.".js";

			if (!file_exists($pathnameInCache)) {
				$this->writeInCache($pathnameInCache, $this->getContent($locale));
			}
			else {
				$mtime = filemtime($pathnameInCache);
				$response->setHttpHeader("Last-Modified", $response->getDate(filemtime($pathnameInCache)));
				
				if ($request->getHttpHeader("If-Modified-Since")) {
					$modifiedSince = HttpUtils::parseDateToTimestamp($request->getHttpHeader("If-Modified-Since"));
						
					if ($mtime <= $modifiedSince) {
						$response->setStatusCode(304);// not modified
						return sfView::NONE;
					}
				}
			}

			$this->readInCache($response, $pathnameInCache);
		}
		else {
			$response->setContent($this->getContent($locale));
		}

		return sfView::NONE;
	}
}
