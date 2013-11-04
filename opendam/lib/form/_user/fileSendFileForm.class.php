<?php
class FileSendFileForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'file_id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'receivers' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:400px; float:left;",
						"class" => "nc"
					)
				),
				'subject' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:400px; float:left;"
					)
				),
				'message' => new sfWidgetFormTextarea(
					array(),
					array(
						"style" => "width:400px; float:left;"
					)
				),
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'file_id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'receivers' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Please enter receivers emails.")
					)
				),
				'subject' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'message' => new sfValidatorString(
					array(
						'required' => false
					)
				),
			)
		);

		$this->validatorSchema->setPostValidator(new sfValidatorCallback(array("callback" => array($this, "checkReceivers"))));
	}

	public function checkReceivers($validator, $values)
	{
		$receivers = $values["receivers"];

		$emails = explode(",", $receivers);

		foreach($emails as $email) {
			$email = trim($email);
			if(!filter_var($email, FILTER_VALIDATE_EMAIL))
				throw new sfValidatorError($validator, __("Receivers email is invalid."));
		}

		return $values;
	}
}