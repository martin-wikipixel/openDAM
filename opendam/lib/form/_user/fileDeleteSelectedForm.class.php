<?php
class FileDeleteSelectedForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'reason' => new sfWidgetFormTextarea(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'reason' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Please enter reason for removal.")
					)
				)
			)
		);
	}
}