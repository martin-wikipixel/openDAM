<?php 

class HtmlHelper 
{
	public static function highlight($sentence, $keyword) {
		if (!$keyword) {
			return $sentence;
		}
		
		$keywords = explode(" ", $keyword);
		array_map("trim", $keywords);
		
		$patterns = array();
		
		foreach ($keywords as $word) {
			$patterns[] = $pattern = "/$word/iu";
		}
				
		return preg_replace($patterns, "<span class='search-selected'>\\0</span>", $sentence);
	}

	public static function drawTags($tags) {
		$str = "";
		
		foreach ($tags as $tag) {
			$str .= $tag->getTitle(). ", ";
		}
		
		$str = mb_substr($str, 0, -2);
		
		return $str;
	}
	
	public static function pager_navigation_ajax($page, $maxPage) 
	{
		$uri = "javascript:void(0)";
		 
		if($maxPage > 1)
		{
			if($maxPage > 15)
				$temp = 15;
			else
				$temp = $maxPage;

			if(($page%15 == 0) && $maxPage > 15)
			{
				$i = $page;
				$temp = (($i / 15) + 1) * 15;
			}
			else
				$i = 1;

			$navigation = '<div class="pagination">
							<div class="pagination_inner">';

			//$uri .= (preg_match('/\?/', $uri) ? '&' : '?').'page=';

			if($page != 1)
			{
				$navigation .= '<a data-page="1" href="'.$uri.'"><em><<</em></a>';
				$navigation .= '<a data-page="'.($page - 1).'" href="'.$uri.'"><em><</em></a>';
			}

			for($k = $i; $k <= $temp; $k++)
			{
				if($k == $page)
					$navigation .= '<a data-page="'.$k.'" href="'.$uri.'" class="selected"><em>'.$k."</em></a>";
				else
					$navigation .= '<a data-page="'.$k.'" href="'.$uri.'"><em>'.$k."</em></a>";
			}

			if($page != $maxPage)
			{
				$navigation .= '<a data-page="'.($page + 1).'" href="'.$uri.'"><em>></em></a>';
				$navigation .= '<a data-page="'.$maxPage.'" href="'.$uri.'"><em>>></em></a>';
			}

			$navigation .= '</div>
						</div>';
						
			return $navigation;
		}
		
		return "";
	}
}
