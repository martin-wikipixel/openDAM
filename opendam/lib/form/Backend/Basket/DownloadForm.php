<?php
class Backend_Basket_DownloadForm extends BaseForm
{
	public function configure()
	{
		$choices = array(BasketPeer::__FORMAT_ORIGINAL => __("Original"), BasketPeer::__FORMAT_WEB => __("Web"));
		
		$this->setWidgets(
			array(
				"format" => new sfWidgetFormChoice(
					array(
						"choices"  => $choices
					),
					array(
					
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat("data[%s]");

		$this->setValidators(
			array(
				"format" => new sfValidatorChoice(
					array(
						"choices" => array_keys($choices)
					),
					array(
						"required" => __("Please select format.")
					)
				),
			)
		);
	}
}