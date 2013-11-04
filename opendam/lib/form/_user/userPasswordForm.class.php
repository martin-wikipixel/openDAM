<?php
class UserPasswordForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'referer' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'email' => new sfWidgetFormInputText(
					array(),
					array(
						"class" => "input-block-level",
						"placeholder" => __("Email"),
						"required" => true
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'referer' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'email' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Email is required.")
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkEmail")
				)
			)
		);
	}

	public function checkEmail($validator, $values)
	{
		$email = $values["email"];

		if (!UserPeer::retrieveByEmail($email)) {
			throw new sfValidatorError($validator, __("No user found with this email."));
		}
		
		return $values;
	}
}