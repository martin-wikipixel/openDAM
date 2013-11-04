<?php

/**
 * explorer actions.
 *
 * @package    wikipixel
 * @subpackage explorer
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class explorerActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function executeShow(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$orderBy = array(
				GroupePeer::NAME => "asc"
		);

		if ($this->getUser()->isAdmin()) {
			$params = array(
					"customerId"	=> $this->getUser()->getCustomerId()
			);

			$criteria = GroupePeer::doCriteria($params, $orderBy);
		}
		else {
			$params = array(
					"userId"		=> $this->getUser()->getId(),
					"customerId"	=> $this->getUser()->getCustomerId()
			);

			$criteria = GroupePeer::getAlbumsHaveAccessForUserCriteria($params, $orderBy);
		}

		$albums = GroupePeer::doSelect($criteria);

		$this->albums = $albums;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeList(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$albumId = $request->getParameter("albumId");
		$folderId = $request->getParameter("folderId");
		$user = $this->getUser()->getInstance();

		$album = GroupePeer::retrieveByPK($albumId);

		$this->forward404Unless($album);

		if ($folderId) {
			$folder = FolderPeer::retrieveByPK($folderId);

			$this->forward404Unless($folder);

			if (!$this->getUser()->isAdmin()) {
				$accessFolder = RightUtils::getAccessForFolderAndUser($folder, $user);
				$access = $accessFolder["access"];
			}
			else {
				$access = true;
			}
		}
		else {
			$folder = null;
			$access = true;
		}

		if ($access) {
			$foldersInAlbum= FolderPeer::getFoldersInGroup(
					$album->getId(),
					array(),
					null,
					0,
					array(),
					"name_asc",
					"N",
					($folder ? $folder->getId() : null),
					null,
					null
			);

			if (!$this->getUser()->isAdmin()) {
				$folders = array();

				foreach ($foldersInAlbum["folders"] as $folder) {
					$accessFolder = RightUtils::getAccessForFolderAndUser($folder, $user);

					if ($accessFolder["access"]) {
						array_push($folders, $folder);
					}
				}
			}
			else {
				$folders = $foldersInAlbum["folders"];
			}
		}
		else {
			$folders = array();
		}

		$this->folders = $folders;

		return sfView::SUCCESS;
	}
}
