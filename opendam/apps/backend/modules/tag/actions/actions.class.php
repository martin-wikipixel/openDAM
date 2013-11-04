<?php

/**
 * ajax actions.
 *
 * @package    jurj
 * @subpackage ajax
 * @author     Ariunbayar, Others
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class tagActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeHome()
	{
		$this->getResponse()->setSlot('sidebar', "public/sidebar");
	
		if($this->getRequestParameter("tag_home_item_page"))
			$this->getUser()->setAttribute("tag_home_item_page", $this->getRequestParameter("tag_home_item_page"));
	
		if($this->getRequestParameter("page"))
			$this->getUser()->setAttribute("tag_home_page", $this->getRequestParameter("page"));
	}

	/*________________________________________________________________________________________________________________*/
	// used in group, folder, file edit select tags
	public function executeFetchByKeyword()
	{
		$this->tags = TagPeer::fetchByKeyword($this->getRequestParameter('keyword'));
	}

	/*________________________________________________________________________________________________________________*/
	// tag/_filter
	public function executeFilterTags()
	{
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	// used in file/show _details-sidebar tag autocomplete
	public function executeFetchTags()
	{
		$this->getResponse()->setContentType('application/json');
		$tags = TagPeer::fetchByKeyword($this->getRequestParameter('term'));
		$words = LexiconPeer::find($this->getRequestParameter('term').'%');
	
		$duplicate = Array();
		$results = array();
	
		foreach($tags as $tag)
		{
			$temp = Array();
			$temp["id"] = $tag->getTitle();
			$temp["value"] = $tag->getTitle();
			$temp["label"] = $tag->getTitle();
	
			$duplicate[] = $tag->getTitle();
			array_push($results, $temp);
		}
	
		foreach($words as $word)
		{
			if(!in_array($word->getTitle(), $duplicate))
			{
				$temp = Array();
				$temp["id"] = $word->getTitle();
				$temp["value"] = $word->getTitle();
				$temp["label"] = $word->getTitle();
	
				array_push($results, $temp);
			}
		}
	
		sort($results);
	
		return $this->renderText(json_encode($results));
	}

	/*________________________________________________________________________________________________________________*/
	// group/step1
	public function executeAddNewTag()
	{
		if((strtolower($this->getRequestParameter("tag_title")) != __("filter tags ...")) && 
			(!$tag = TagPeer::retrieveByTitle($this->getRequestParameter("tag_title"))))
		{
			$tag = new Tag();
			$tag->setTitle($this->getRequestParameter("tag_title"));
			$tag->setCustomerId($this->getUser()->getCustomerId());
			$tag->save();
	
			$this->tag = $tag;
	
			LogPeer::setLog($this->getUser()->getId(), $tag->getId(), "tag-create", "9");
		}
	}

	/*________________________________________________________________________________________________________________*/
	// file/_sidebar
	public function executeAddByUser()
	{
		$this->forward404Unless($file = FilePeer::retrieveByPK($this->getRequestParameter("file_id")));
		$this->forward404Unless(UserPeer::isAllowed($this->getRequestParameter('file_id'), "file"));
	
		$tag_titles = $this->getRequestParameter("tag_title");
	
		foreach($tag_titles as $tag_title)
		{
			if(!$tag = TagPeer::retrieveByTitle($tag_title))
			{
				$tag = new Tag();
				$tag->setTitle($tag_title);
				$tag->setCustomerId($this->getUser()->getCustomerId());
				$tag->save();
	
				LogPeer::setLog($this->getUser()->getId(), $tag->getId(), "tag-create", "9");
			}
	
			if(!FileTagPeer::getFileTag(3, $file->getId(), $tag->getId()))
			{
				$file_tag = new FileTag();
				$file_tag->setFileId($file->getId());
				$file_tag->setTagId($tag->getId());
				$file_tag->setType(3);
				$file_tag->save();
	
				myTools::addTags($file, false);
			}
		}
	
		$this->file = $file;
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	// tag/
	public function executeRemoveByUser()
	{
		$fileId = $this->getRequestParameter("file_id");
		$fileTagId = $this->getRequestParameter("id");

		$file = FilePeer::retrieveByPK($fileId);

		$this->forward404Unless($file);

		$folder = FolderPeer::retrieveByPK($file->getFolderId());

		$this->forward404Unless($folder);

		$album = GroupePeer::retrieveByPK($folder->getGroupeId());

		$this->forward404Unless($album);

		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404Unless($this->getUser()->getRole($album->getId(), $folder->getId()));

		$isAllowed = false;

		if ($roleAlbum < RolePeer::__ADMIN) {
			$isAllowed = true;
		}
		elseif ($roleAlbum == RolePeer::__ADMIN) {
			if ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$isAllowed = true;
			}
			elseif ($file->getUserId() == $this->getUser()->getId()) {
				$isAllowed = true;
			}
		}
		elseif ($roleAlbum == RolePeer::__CONTRIB)
		{
			if ($this->getUser()->getConstraint($folder->getGroupeId(),
					ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$isAllowed = true;
			}
			elseif ($file->getUserId() == $this->getUser()->getId()) {
				$isAllowed = true;
			}
		}

		$this->forward404Unless($isAllowed);

		$fileTag = FileTagPeer::retrieveByPK($fileTagId);

		if ($fileTag) {
			$fileTag->delete();
		}

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeAddThesaurus()
	{
		$this->forward404Unless($file = FilePeer::retrieveByPK($this->getRequestParameter("file_id")));
		$this->forward404Unless(UserPeer::isAllowed($this->getRequestParameter('file_id'), "file"));
		$this->forward404Unless($thesaurus = ThesaurusPeer::retrieveByPk($this->getRequestParameter("id")));
	
		if(!$tag = TagPeer::retrieveByTitle($thesaurus->getTitle()))
		{
			$tag = new Tag();
			$tag->setTitle($thesaurus->getTitle());
			$tag->setCustomerId($this->getUser()->getCustomerId());
			$tag->save();
	
			LogPeer::setLog($this->getUser()->getId(), $tag->getId(), "tag-create", "9");
		}
	
		if(!FileTagPeer::getFileTag(3, $file->getId(), $tag->getId()))
		{
			$file_tag = new FileTag();
			$file_tag->setFileId($file->getId());
			$file_tag->setTagId($tag->getId());
			$file_tag->setType(3);
			$file_tag->save();
	
			myTools::addTags($file, false);
		}
	
		$this->file = $file;
	
		$this->setTemplate("addByUser");
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFetchLexicon()
	{
		$this->getResponse()->setContentType('application/json');
		$words = LexiconPeer::find($this->getRequestParameter('term').'%');
	
		$results = array();
	
		foreach($words as $word)
		{
			$temp = Array();
			$temp["id"] = $word->getTitle();
			$temp["value"] = $word->getTitle();
			$temp["label"] = $word->getTitle();
	
			array_push($results, $temp);
		}
	
		sort($results);
	
		return $this->renderText(json_encode($results));
	}
}