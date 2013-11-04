<?php

class selectionComponents extends sfComponents
{
	/*________________________________________________________________________________________________________________*/
	public function executeMyBasket()
	{
		$this->basket = $this->getUser()->getBasket();
	
		if ($this->basket) {
			$this->contents = BasketHasContentPeer::getContents($this->basket->getId());
		}
		else
			$this->contents = Array();
	
		if (!empty($this->contents))
			$this->getResponse()->setSlot("body-class", "selection");
	}

	/*________________________________________________________________________________________________________________*/
	public function executePublicComment()
	{
		$this->comments = BasketHasCommentPeer::retrieveByBasketId($this->basket->getId());
	}
}