<?php
class BasketDownloadForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'format' => new sfWidgetFormChoice(
					array(
						'choices'  => Array(0 => __("Select format"), BasketPeer::__FORMAT_ORIGINAL => __("Original"), BasketPeer::__FORMAT_WEB => __("Web"))
					),
					array(
						"style" => "float:left; width:257px;",
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
				'format' => new sfValidatorChoice(
					array(
						'choices' => array(BasketPeer::__FORMAT_ORIGINAL, BasketPeer::__FORMAT_WEB)
					),
					array(
						'invalid' => __("Please select format."),
						'required' => __("Please select format.")
					)
				),
			)
		);
	}
}