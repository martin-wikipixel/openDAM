<?php

/**
 * thesaurus actions.
 *
 * @package    wikipixel
 * @subpackage thesaurus
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class thesaurusActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeTree()
	{
		$this->forward404Unless($this->getUser()->haveAccessModule(ModulePeer::__MOD_THESAURUS));

		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($culture = CulturePeer::retrieveByCode($this->getRequestParameter("culture")));
			$root = $this->getRequestParameter('root');
	
			if(empty($root) || $root == 'source')
				$root = null;
			else
				$this->forward404Unless(ThesaurusPeer::retrieveByPk($root));
	
			$this->getResponse()->setContentType('application/json');
	
			$c = new Criteria();
			$c->add(ThesaurusPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(ThesaurusPeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(ThesaurusPeer::PARENT_ID, $root);
			$c->add(ThesaurusPeer::CULTURE_ID, $culture->getId());
			$c->addAscendingOrderByColumn(ThesaurusPeer::TITLE);
	
			$thesaurus_ = ThesaurusPeer::doSelect($c);
	
			$thesaurus_array = Array();
	
			foreach($thesaurus_ as $thesaurus)
			{
				$temp = Array();
				$temp["text"] = $thesaurus->getTitle();
				$temp["expanded"] = false;
				$temp["id"] = $thesaurus->getId();
				$temp["hasChildren"] = $thesaurus->getType() == ThesaurusPeer::__TYPE_CLASS ? true : false;
				$temp["classes"] = "li_tag";
	
				array_push($thesaurus_array, $temp);
			}
	
			return $this->renderText(json_encode($thesaurus_array));
		}
	}



	/*________________________________________________________________________________________________________________*/
	public function executeRandomTags()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			return $this->renderComponent("thesaurus", "randomTags");
		}
	
		$this->redirect404();
	}
}
