<?php

/**
 * i18n_js actions.
 *
 * @package    wikipixel
 * @subpackage i18n_js
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class wpLessActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function getContent($realPath, array $args)
	{
		$cmd = "lessc";

		if (isset($args["minify"]) && $args["minify"]) {
			$cmd .= " --yui-compress";
		}

		$out = shell_exec("$cmd $realPath");

		if ($out === null) {
			throw new Exception("Error while executing commande: $cmd $realPath");
		}
		
		return $out;
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
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function executeIndex(sfWebRequest $request)
	{
		$useCache = sfConfig::get("app_wpLess_use_cache", true);
		$minify = sfConfig::get("app_wpLess_minify", true);
		
		$args = array("minify" => $minify);
		
		$cacheDir = sfConfig::get("sf_cache_dir").DIRECTORY_SEPARATOR.
			$this->getContext()->getConfiguration()->getApplication().DIRECTORY_SEPARATOR.
			$this->getContext()->getConfiguration()->getEnvironment().DIRECTORY_SEPARATOR."wpLess";

		$response = $this->getResponse();
		$response->setContentType("text/css");
		
		$response->addCacheControlHttpHeader("private");
		
		$src = $request->getParameter("src");
		$realPath = sfConfig::get("sf_web_dir")."/less/".$src;

		if ($useCache) {
			$pathnameInCache = $cacheDir.DIRECTORY_SEPARATOR."style.css";

			if (!file_exists($pathnameInCache)) {
				$this->writeInCache($pathnameInCache, $this->getContent($realPath, $args));
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
			$response->setContent($this->getContent($realPath, $args));
		}

		return sfView::NONE;
	}
}
