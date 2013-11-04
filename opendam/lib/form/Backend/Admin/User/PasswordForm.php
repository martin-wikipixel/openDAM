<?php
class Backend_Admin_User_PasswordForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				"id" => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				"password" => new sfWidgetFormInputPassword(
					array(),
					array("autocomplete" => "off")
				),
				"confirm_password" => new sfWidgetFormInputPassword(
					array(),
					array("autocomplete" => "off")
				)
			)
		);

		$this->widgetSchema->setNameFormat("data[%s]");

		$this->setValidators(
			array(
				"id" => new sfValidatorString(
					array(
						"required" => false
					),
					array()
				),
				"password" => new sfValidatorString(
					array(
						"required" => true,
						"min_length" => 6,
						"max_length" => 200,
					),
					array(
						"required" => __("New password is required."),
					)
				),
				"confirm_password" => new sfValidatorString(
					array(
						"required" => false
					),
					array(
						"required" => __("Please verify your new password.")
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorSchemaCompare("password", "==", "confirm_password",
				array(),
				array("invalid" => __("Passwords do not match, please check and try again."))
			)
		);
	}
}