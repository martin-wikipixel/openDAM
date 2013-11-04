<?php

/**
 * ajax actions.
 *
 * @package    jurj
 * @subpackage ajax
 * @author     Ariunbayar, Others
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class searchActions extends sfActions
{  
  public function preExecute()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
  }
  
  protected function prepareUri()
  {
    $uri = "/search/search?tag_title=".($this->getRequestParameter("tag_title") ? $this->getRequestParameter("tag_title") : "")
      .'&view_groups='.$this->getRequestParameter("view_folders", 0)
      .'&view_folders='.$this->getRequestParameter("view_folders", 0)
      .'&view_files='.$this->getRequestParameter("view_files", 0)
      .'&group_id='.$this->getRequestParameter("group_id", 0)
      .'&file_type='.$this->getRequestParameter("file_type", 0)
      .'&file_orientation='.$this->getRequestParameter("file_orientation", 0)
      .'&file_rating='.$this->getRequestParameter("file_rating", 0)
      .'&usage_right='.$this->getRequestParameter("usage_right", 0)
      .'&added_by_me='.$this->getRequestParameter("added_by_me", 0)
      .'&year-from='.$this->getRequestParameter("year-from")
      .'&year-to='.$this->getRequestParameter("year-to")
      .'&size-from='.$this->getRequestParameter("size-from")
      .'&size-to='.$this->getRequestParameter("size-to")
      .'&top_keyword='.$this->getRequestParameter("top_keyword")
      .'&advanced-search='.$this->getRequestParameter("advanced-search")
      .'&iptc_fields='.$this->getRequestParameter("iptc_fields")
      .'&exif_fields='.$this->getRequestParameter("exif_fields")
      .'&color='.$this->getRequestParameter("color")
      .'&type_of_media='.$this->getRequestParameter("type_of_media")
      .'&extension_of_media='.$this->getRequestParameter("extension_of_media")
      .'&size_of_media='.$this->getRequestParameter("size_of_media")
      .'&condition_of_media='.$this->getRequestParameter("condition_of_media")
      .'&uploaded_by_user='.$this->getRequestParameter("uploaded_by_user");

    if(sizeof($this->getRequestParameter("selected_tag_ids"))) $uri .= '&selected_tag_ids[]='.join('&selected_tag_ids[]=', $this->getRequestParameter("selected_tag_ids"));
    
    return $uri;
  }
  
  protected function prepareBreadCrumbs()
  {
    $bread_crumbs = array();
    if($this->getUser()->getAttribute("top_keyword")){
      $bread_crumbs[] = "<a id='bread_selected_tag_id_0' style='z-index:30;' href='javascript:jQuery('#search_form').submit();'>".__("Search")." : ".$this->getUser()->getAttribute("top_keyword")."</a>";
    }
    
    $tags = TagPeer::retrieveByPKs($this->getRequestParameter('selected_tag_ids') ? $this->getRequestParameter('selected_tag_ids') : array());
    $i = 0;
    foreach ($tags as $tag){
      $i++;
      $bread_crumbs[] = "<a id='bread_selected_tag_id_".$tag->getId()."' style='z-index:".strval(30-$i*10).";' href='javascript:jQuery('#search_form').submit();'>".($i==1 ? __("Search by tag")." : " : "").$tag." &nbsp;<em onclick='removeFromSelectedTags(".$tag->getId().")'></em></a>";
    }
    
    return $bread_crumbs;
  } 
 
  # SEARCH ON AUTO COMPLETE
  public function executeSearchTop()
  {
    return sfView::SUCCESS;
  }
  
  public function executeObserve()
  {
    return sfView::SUCCESS;
  }

  public function executeAddExifField()
  {
	if($this->getRequest()->isXmlHttpRequest())
	{
		$this->index = $this->getRequestParameter("index");
		return sfView::SUCCESS;
	}

	$this->redirect404();
  }
  
  public function executeAddIptcField()
  {
	if($this->getRequest()->isXmlHttpRequest())
	{
		$this->index = $this->getRequestParameter("index");
		return sfView::SUCCESS;
	}

	$this->redirect404();
  }

  public function executeGetFotoliaResults()
  {
	if($this->getRequest()->isXmlHttpRequest())
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");

		$return = Array();
		$top_keyword = $this->getRequestParameter("top_keyword");
		$selected_tag_ids = explode("|", $this->getRequestParameter("selected_tag_ids"));
		$item_fotolia = $this->getRequestParameter("item_fotolia");
		$offset_fotolia = $this->getRequestParameter("offset_fotolia");
		$item = $this->getRequestParameter("item");
		$compteur_old = $this->getRequestParameter("compteur");
		$uri = $this->getRequestParameter("uri");
		$draw_line = $this->getRequestParameter("draw_line");

		$results = myTools::getFotoliaResults($top_keyword, $selected_tag_ids, $item_fotolia, $offset_fotolia);

		if(array_key_exists("nb_results", $results))
			$max_item_fotolia = $results["nb_results"];

		$compteur = ($max_item_fotolia - $item_fotolia + $compteur_old);
		$maxPage = ceil($compteur / $item);

		$return["fotolia"] = $this->getPartial("search/fotoliaResults", Array("results" => $results, "draw_line" => $draw_line));
		$return["pagination"] = pager_navigation_search(1, $maxPage, $uri);

		$this->getResponse()->setContentType('application/json');

		return $this->renderText(json_encode($return));
	}

	$this->redirect404();
  }

	public function executeSearch()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");

		$bread_crumbs = Array();
		$bread_crumbs[] = Array("link" => url_for("search/search"), "label" => __("Search"));

		if($this->getRequestParameter("first_call"))
		{
			$this->getUser()->setAttribute("sort", "date_desc");
			$this->getUser()->setAttribute("exclude", Array());
			$this->getUser()->setAttribute("file_orientation", Array());
			$this->getUser()->setAttribute("file_size", Array());
			$this->getUser()->setAttribute("file_dates", Array());
			$this->getUser()->setAttribute("file_licence", Array());
			$this->getUser()->setAttribute("file_use", Array());
			$this->getUser()->setAttribute("file_distribution", Array());
			$this->getUser()->setAttribute("types", $this->getRequestParameter("types", Array("pictures", "videos", "audios", "documents")));
			
		}

		if($this->getRequestParameter("sort"))
			$this->getUser()->setAttribute("sort", $this->getRequestParameter("sort"));

		if($this->getRequestParameter("types"))
			$this->getUser()->setAttribute("types", $this->getRequestParameter("types"));

		$this->types = $this->getUser()->getAttribute("types");

		$this->groups = GroupePeer::searchEngine(
			$this->getRequestParameter("top_keyword"),
			null,
			0,
			$this->getRequestParameter("selected_tag_ids", Array()),
			$this->getRequestParameter("added_by_me", 0),
			Array($this->getRequestParameter("year-from"), $this->getRequestParameter("year-to")),
			$this->getUser()->getAttribute("sort")
		);

		$this->folders = FolderPeer::searchEngine(
			$this->getRequestParameter("top_keyword"),
			null,
			0,
			$this->getRequestParameter("selected_tag_ids", array()),
			$this->getRequestParameter("added_by_me", 0),
			$this->getRequestParameter("group_id", 0),
			Array($this->getRequestParameter("lat", 0), $this->getRequestParameter("lng", 0)),
			Array($this->getRequestParameter("year-from"), $this->getRequestParameter("year-to")),
			$this->getUser()->getAttribute("sort")
		);

		$perPage = 25;

		$this->files = FilePeer::searchEngine(
			$this->getRequestParameter("top_keyword"),
			null,
			$perPage,
			$this->getRequestParameter("selected_tag_ids", Array()),
			$this->getRequestParameter("added_by_me", 0),
			$this->getRequestParameter("group_id", 0),
			$this->getRequestParameter("file_type", 0),
			Array($this->getRequestParameter("lat", 0), $this->getRequestParameter("lng", 0)),
			Array($this->getRequestParameter("year-from"), $this->getRequestParameter("year-to")),
			$this->getUser()->getAttribute("file_size"),
			$this->getUser()->getAttribute("sort"),
			null,
			$this->getUser()->getAttribute("file_distribution"),
			Array(),
			$this->getUser()->getAttribute("file_licence"),
			$this->getUser()->getAttribute("file_use"),
			$this->getRequestParameter("creative_commons_select", null),
			Array($this->getRequestParameter("date-from"), $this->getRequestParameter("date-to")),
			$this->getUser()->getAttribute("file_orientation"),
			$this->getRequestParameter("file_rating", null),
			Array(),
			($perPage * ($this->getRequestParameter("page", 1) - 1)),
			$this->getUser()->getAttribute("exclude"),
			$this->types,
			$this->getUser()->getAttribute("file_dates")
		);

		$this->getResponse()->setSlot("actions",  $this->getPartial("search/breadcrumbActions", Array("countFiles" => $this->files["count"], "countOthers" => (count($this->groups) + count($this->folders)))));

		$patterns = Array("/ AND /", "/ OR /", "/ NOT /");
		$replacements = Array(" ", " ", " ");
		$keywords = explode(" ", preg_replace($patterns, $replacements, $this->getRequestParameter("top_keyword")));
		$this->formatedKeywords = Array();

		foreach($keywords as $index => $keyword)
		{
			$temp = $keywords;
			unset($temp[$index]);

			$this->formatedKeywords[] = Array("link" => url_for("search/search?top_keyword=".implode(" ", $temp)), "label" => $keyword);
		}

		$infos = $this->files["extended"];
		$this->extended = Array(
			FilePeer::__TYPE_PHOTO => Array(
				"count" => 0,
				"type" => Array()
			),
			FilePeer::__TYPE_AUDIO => Array(
				"count" => 0,
				"type" => Array()
			),
			FilePeer::__TYPE_VIDEO => Array(
				"count" => 0,
				"type" => Array()
			),
			FilePeer::__TYPE_DOCUMENT => Array(
				"count" => 0,
				"type" => Array()
			),
		);

		$exclude = Array();
		$paginate = $this->files["count"];

		foreach($infos as $info)
		{
			$this->extended[$info["type"]]["count"] += $info["count"];
			$this->extended[$info["type"]]["type"][$info["extention"]] = $info["count"];

			switch($info["type"])
			{
				case FilePeer::__TYPE_PHOTO:
					if(!in_array("pictures", $this->types))
					{
						$exclude[] = $info["extention"];
						$paginate -= $info["count"];
					}
				break;

				case FilePeer::__TYPE_AUDIO:
					if(!in_array("audios", $this->types))
					{
						$exclude[] = $info["extention"];
						$paginate -= $info["count"];
					}
				break;

				case FilePeer::__TYPE_VIDEO:
					if(!in_array("videos", $this->types))
					{
						$exclude[] = $info["extention"];
						$paginate -= $info["count"];
					}
				break;

				case FilePeer::__TYPE_DOCUMENT:
					if(!in_array("documents", $this->types))
					{
						$exclude[] = $info["extention"];
						$paginate -= $info["count"];
					}
				break;
			}
		}

		$this->licences = Array("count" => 0, "data" => Array());

		foreach($this->files["licence"] as $licence)
		{
			$this->licences["count"] += $licence["count"];
			$this->licences["data"][$licence["licence_id"]] = $licence["count"];
		}

		$this->uses = Array("count" => 0, "data" => Array());

		foreach($this->files["use"] as $use)
		{
			$this->uses["count"] += $use["count"];
			$this->uses["data"][$use["usage_use_id"]] = $use["count"];
		}

		$this->distributions = Array("count" => 0, "data" => Array());

		foreach($this->files["distribution"] as $distribution)
		{
			$this->distributions["count"] += $distribution["count"];
			$this->distributions["data"][$distribution["usage_distribution_id"]] = $distribution["count"];
		}

		$this->countGroups = count($this->groups);
		$this->countFolders = count($this->folders);

		if(!in_array("albums", $this->types))
			$this->groups = Array();

		if(!in_array("folders", $this->types))
			$this->folders = Array();

		$this->getUser()->setAttribute("exclude", $exclude);

		$this->getResponse()->setSlot("bread_crumbs", $bread_crumbs);

		$this->orientations = $this->files["orientation"];
		$this->dates = $this->files["date"];
		$this->sizes = $this->files["size"];
		$this->page = $this->getRequestParameter("page", 1);
		$this->url = url_for("search/search?top_keyword=".$this->getRequestParameter("top_keyword")."&page=".($this->page + 1));
		$this->paginateFiles = $paginate > ($perPage * $this->getRequestParameter("page", 1)) ? true : false;

		return sfView::SUCCESS;
	}

	public function executeFilterSearch()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");

		if($this->getRequest()->isXmlHttpRequest())
		{
			if($this->getRequestParameter("reset"))
				$this->getUser()->setAttribute("exclude", Array());

			switch($this->getRequestParameter("filterType"))
			{
				case "albums":
					if($this->getRequestParameter("sort"))
						$this->getUser()->setAttribute("sort", $this->getRequestParameter("sort"));

					$groups = GroupePeer::searchEngine(
						$this->getRequestParameter("top_keyword"),
						null,
						0,
						$this->getRequestParameter("selected_tag_ids", Array()),
						$this->getRequestParameter("added_by_me", 0),
						Array($this->getRequestParameter("year-from"), $this->getRequestParameter("year-to")),
						$this->getUser()->getAttribute("sort")
					);

					$result = Array("html" => "");

					foreach($groups as $group)
						$result["html"] .= $this->getPartial("search/group", Array("group" => $group));

					$this->getResponse()->setContentType('application/json');
					return $this->renderText(json_encode($result));
				break;

				case "folders":
					if($this->getRequestParameter("sort"))
						$this->getUser()->setAttribute("sort", $this->getRequestParameter("sort"));

					$folders = FolderPeer::searchEngine(
						$this->getRequestParameter("top_keyword"),
						null,
						0,
						$this->getRequestParameter("selected_tag_ids", array()),
						$this->getRequestParameter("added_by_me", 0),
						$this->getRequestParameter("group_id", 0),
						Array($this->getRequestParameter("lat", 0), $this->getRequestParameter("lng", 0)),
						Array($this->getRequestParameter("year-from"), $this->getRequestParameter("year-to")),
						$this->getUser()->getAttribute("sort")
					);

					$result = Array("html" => "");

					foreach($folders as $folder)
						$result["html"] .= $this->getPartial("search/folder", Array("folder" => $folder));

					$this->getResponse()->setContentType('application/json');
					return $this->renderText(json_encode($result));
				break;
				
				case "files":
					if($this->getRequestParameter("sort"))
						$this->getUser()->setAttribute("sort", $this->getRequestParameter("sort"));

					if($this->getRequestParameter("extention"))
					{
						$exclude = $this->getUser()->getAttribute("exclude");
						$extention = $this->getRequestParameter("extention");

						$index = array_search($extention, $exclude);

						if($index === false)
							$exclude[] = $extention;
						else
							unset($exclude[$index]);

						$this->getUser()->setAttribute("exclude", $exclude);
					}

					if($this->getRequestParameter("file_orientation"))
					{
						$exclude = $this->getUser()->getAttribute("file_orientation");
						$orientation = $this->getRequestParameter("file_orientation");

						if(empty($exclude))
							$exclude = Array("portrait", "landscape", "square");

						$index = array_search($orientation, $exclude);

						if($index === false)
							$exclude[] = $orientation;
						else
							unset($exclude[$index]);

						$this->getUser()->setAttribute("file_orientation", $exclude);
					}

					if($this->getRequestParameter("file_size"))
					{
						$exclude = $this->getUser()->getAttribute("file_size");
						$size = $this->getRequestParameter("file_size");

						if(empty($exclude))
							$exclude = Array("-5", "5", "25", "50", "100", "250");

						$index = array_search($size, $exclude);

						if($index === false)
							$exclude[] = $size;
						else
							unset($exclude[$index]);

						$this->getUser()->setAttribute("file_size", $exclude);
					}

					if($this->getRequestParameter("min_date") || $this->getRequestParameter("max_date"))
					{
						$dates = Array();

						if($this->getRequestParameter("min_date"))
							$dates["min"] = $this->getRequestParameter("min_date");

						if($this->getRequestParameter("max_date"))
							$dates["max"] = $this->getRequestParameter("max_date");

						$this->getUser()->setAttribute("file_dates", $dates);
					}

					if($this->getRequestParameter("licence"))
					{
						$exclude = $this->getUser()->getAttribute("file_licence");
						$licence = $this->getRequestParameter("licence");

						$index = array_search($licence, $exclude);

						if($index === false)
							$exclude[] = $licence;
						else
							unset($exclude[$index]);

						$this->getUser()->setAttribute("file_licence", $exclude);
					}

					if($this->getRequestParameter("use"))
					{
						$exclude = $this->getUser()->getAttribute("file_use");
						$use = $this->getRequestParameter("use");

						$index = array_search($use, $exclude);

						if($index === false)
							$exclude[] = $use;
						else
							unset($exclude[$index]);

						$this->getUser()->setAttribute("file_use", $exclude);
					}

					if($this->getRequestParameter("distribution"))
					{
						$exclude = $this->getUser()->getAttribute("file_distribution");
						$distribution = $this->getRequestParameter("distribution");

						$index = array_search($distribution, $exclude);

						if($index === false)
							$exclude[] = $distribution;
						else
							unset($exclude[$index]);

						$this->getUser()->setAttribute("file_distribution", $exclude);
					}

					$perPage = 25;

					$files = FilePeer::searchEngine(
						$this->getRequestParameter("top_keyword"),
						null,
						$perPage,
						$this->getRequestParameter("selected_tag_ids", Array()),
						$this->getRequestParameter("added_by_me", 0),
						$this->getRequestParameter("group_id", 0),
						$this->getRequestParameter("file_type", 0),
						Array($this->getRequestParameter("lat", 0), $this->getRequestParameter("lng", 0)),
						Array($this->getRequestParameter("year-from"), $this->getRequestParameter("year-to")),
						$this->getUser()->getAttribute("file_size"),
						$this->getUser()->getAttribute("sort"),
						null,
						$this->getUser()->getAttribute("file_distribution"),
						Array(),
						$this->getUser()->getAttribute("file_licence"),
						$this->getUser()->getAttribute("file_use"),
						$this->getRequestParameter("creative_commons_select", null),
						Array($this->getRequestParameter("date-from"), $this->getRequestParameter("date-to")),
						$this->getUser()->getAttribute("file_orientation"),
						$this->getRequestParameter("file_rating", null),
						Array(),
						($perPage * ($this->getRequestParameter("page", 1) - 1)),
						$this->getUser()->getAttribute("exclude"),
						null,
						$this->getUser()->getAttribute("file_dates")
					);

					$result = Array("html" => "");

					foreach($files["files"] as $file)
						$result["html"] .= $this->getPartial("search/file", Array("file" => $file));

					$result["showPagination"] = $files["count"] > ($perPage * $this->getRequestParameter("page", 1)) ? true : false;
					$result["urlPagination"] = url_for("search/search?top_keyword=".$this->getRequestParameter("top_keyword")."&page=2");

					$this->getResponse()->setContentType('application/json');
					return $this->renderText(json_encode($result));
				break;
			}
		}
	}
}