<?php
class FileDeleteSingleForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'folder_id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
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
				'id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'folder_id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
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