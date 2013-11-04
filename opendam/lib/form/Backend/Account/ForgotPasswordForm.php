<?php
class Backend_Account_ForgotPasswordForm extends BaseForm
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
				'email' => new sfValidatorEmail(
					array(
						'required' => true
					),
					array(
						'invalid' => __("Email is invalid."),
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

		$user = UserPeer::retrieveByEmail($email);
		
		if (!$user) {
			throw new sfValidatorError($validator, __("No user found with this email."));
		}
		
		if ($user->getState() != UserPeer::__STATE_ACTIVE) {
			throw new sfValidatorError($validator, __("This account is disabled."));
		}
		
		return $values;
	}
}