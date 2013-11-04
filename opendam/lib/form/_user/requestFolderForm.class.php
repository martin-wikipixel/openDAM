<?php
class RequestSendFolderForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'folder_id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'message' => new sfWidgetFormTextarea(
					array(),
					array(
						"style" => "float:left; width:400px; height:100px;"
					)
				),
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'folder_id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'message' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Message is required.")
					)
				)
			)
		);
	}
}