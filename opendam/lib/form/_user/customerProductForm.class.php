<?php
class CustomerProductForm extends BaseForm
{
	public function configure()
	{
		$productsArray = ProductPeer::getProductsInArray();
		$productsArray[0] = __('Select product');
		ksort($productsArray);

		switch(sfContext::getInstance()->getUser()->getCulture())
		{
			case "fr": $date_format = "%day%/%month%/%year%"; break;
			case "en": $date_format = "%month%/%day%/%year%"; break;
		}

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'unlimited' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'expiration' => new sfWidgetFormDate(
					array(
						"format" => $date_format
					),
					array()
				),
				'product' => new sfWidgetFormChoice(
					array(
						'choices'  => $productsArray
					),
					array(
						"style" => "float:left; width:257px;",
						"onchange" => "displayWarnProduct();"
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'unlimited' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'expiration' => new sfValidatorDate(
					array(
						'required' => false
					)
				),
				'product' => new sfValidatorChoice(
					array(
						'choices' => array_keys(ProductPeer::getProductsInArray())
					),
					array(
						'invalid' => __("Product is required."),
						'required' => __("Product is required.")
					)
				)
			)
		);
	}
}