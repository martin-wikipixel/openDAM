<?php
class Backend_Basket_EditForm extends BaseForm
{
	public function configure()
	{
		$choices = array(BasketPeer::__FORMAT_ORIGINAL => __("Original"), BasketPeer::__FORMAT_WEB => __("Web"));
		
		$this->setWidgets(
			array(
					"name" => new sfWidgetFormInputText(
						array(),
						array("class" => "span3")
					),
					"description" => new sfWidgetFormTextarea(
						array(),
						array("class" => "span3")
					),
			)
		);

		$this->widgetSchema->setNameFormat("data[%s]");

		$this->setValidators(
			array(
					"name" => new sfValidatorString(
							array(
									"required" => true
							),
							array(
									"required" => __("Name is required.")
							)
					),
					"description" => new sfValidatorString(
							array(
									"required" => false
							),
							array(
							)
					),
			)
		);
	}
}