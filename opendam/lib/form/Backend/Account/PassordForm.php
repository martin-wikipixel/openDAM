<?php
class Backend_Account_PasswordForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				"password" => new sfWidgetFormInputPassword(
					array(),
					array("autocomplete" => "off")
				),
				"new_password" => new sfWidgetFormInputPassword(
					array(),
					array("autocomplete" => "off")
				),
				"verify_password" => new sfWidgetFormInputPassword(
					array(),
					array("autocomplete" => "off")
				),
			)
		);

		$this->widgetSchema->setNameFormat("data[%s]");

		$this->setValidators(
			array(
				"password" => new sfValidatorCallback(
						array(
							"required" => true,
							"callback" => array($this, "checkCurrentPassword")
						)
					),
				"new_password" => new sfValidatorString(
					array(
						"required" => true,
						"min_length" => 6,
						"max_length" => 200,
					),
					array(
						"required" => __("New password is required.")
					)
				),
				"verify_password" => new sfValidatorString(
					array(
						"required" => true
					),
					array(
						"required" => __("Please verify your new password.")
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorSchemaCompare("new_password", sfValidatorSchemaCompare::EQUAL, "verify_password",
				array(),
				array(
					"invalid" => __("Passwords do not match, please check and try again.")
				)
			)
		);
	}

	/*________________________________________________________________________________________________________________*/
	public function checkCurrentPassword($validator, $password)
	{
		$user = sfContext::getInstance()->getUser()->getInstance();
		$encoder = Factory::getPasswordEncoder();
		
		$encoded = $encoder->encodePassword($password, "");
		
		if ($encoded != $user->getPassword()) {
			throw new sfValidatorError($validator, __("Please check your current password."));
		}
		
		return $password;
	}
}