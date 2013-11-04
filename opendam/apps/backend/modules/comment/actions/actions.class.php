<?php



/**
 * ajax actions.
 *
 * @package    jurj
 * @subpackage ajax
 * @author     Ariunbayar, Others
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class commentActions extends sfActions
{
	// TODO: access to actions by url
	/*________________________________________________________________________________________________________________*/
	public function executeList()
	{
		$this->file_id = $this->getRequestParameter("file_id");
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSave()
	{
		$this->forward404Unless(UserPeer::isAllowed($this->getRequestParameter("file_id"), "file"));
	
		if (!CommentPeer::getComment($this->getUser()->getId(), $this->getRequestParameter("file_id"), 
				$this->getRequestParameter("comment"))) {
			$comment = new Comment();
			$comment->setUserId($this->getUser()->getId());
			$comment->setFileId($this->getRequestParameter("file_id"));
			
			$formating = new Formating();
			$comment->setContent($formating->force_balance_tags(nl2br($this->getRequestParameter("comment"))));
			$comment->save();
		
			// save log
			LogPeer::setLog($this->getUser()->getId(), $comment->getFileId(), "comment-add", "3");
		}
	
		$this->redirect("@comment_list?file_id=".$this->getRequestParameter("file_id"));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeEdit()
	{
		$this->forward404Unless($comment = CommentPeer::retrieveByPK($this->getRequestParameter("id")));
		$this->forward404Unless(
			($this->getUser()->getId() == $comment->getUserId()) || 
			($this->getUser()->getId() == $comment->getFile()->getUserId()) ||
			$this->getUser()->isAdmin()
		);
		
		$this->forward404Unless(UserPeer::isAllowed($comment->getFileId(), "file"));
		$this->getResponse()->setContentType('application/json');
	
		$formating = new Formating();
		$comment->setContent($formating->force_balance_tags(nl2br($this->getRequestParameter("comment"))));
		$comment->save();
	
		LogPeer::setLog($this->getUser()->getId(), $comment->getFileId(), "comment-update", "3");
	
		return $this->renderText(json_encode(Array("html" => $comment->getContent(), "js" => 
				$this->getRequestParameter("comment"))));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDelete()
	{
		//checks
		$this->forward404Unless($comment = CommentPeer::retrieveByPK($this->getRequestParameter("id")));
		$this->forward404Unless(
			($this->getUser()->getId() == $comment->getUserId()) || 
			($this->getUser()->getId() == $comment->getFile()->getUserId()) ||
			$this->getUser()->isAdmin()
		);

		$file_id = $comment->getFileId();
		$this->forward404Unless(UserPeer::isAllowed($file_id, "file"));

		try {
			$comment->delete();
	
			// set log
			LogPeer::setLog($this->getUser()->getId(), $file_id, "comment-delete", "3");
		}
		catch (Exception $e){}
	
		$this->redirect("@comment_list?file_id=".$file_id);
	}
}