<?php
class PublicInfoEditForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'content' => new sfWidgetFormTextareaTinyMCE(
					array(
						"width" => 600,
						"height" => 400,
						"language" => sfContext::getInstance()->getUser()->getCulture(),
					),
					array()
				),
				'is_active' => new sfWidgetFormInputCheckbox(
					array(),
					array()
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'content' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'is_active' => new sfValidatorString(
					array(
						'required' => false
					)
				)
			)
		);
	}
}