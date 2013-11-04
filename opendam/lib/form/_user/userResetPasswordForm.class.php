<?php
class UserResetPasswordForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'h' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'v' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'password' => new sfWidgetFormInputPassword(
					array(),
					array(
						"class" => "input-block-level",
						"placeholder" => __("New password"),
						"required" => true
					)
				),
				'confirm_password' => new sfWidgetFormInputPassword(
					array(),
					array(
						"class" => "input-block-level",
						"placeholder" => __("Confirm password"),
						"required" => true
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'h' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'v' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'password' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("New password is required.")
					)
				),
				'confirm_password' => new sfValidatorString(
					array(
						'required' => false
					),
					array(
						'required' => __("Please verify your new password.")
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorAnd(
				array(
					new sfValidatorSchemaCompare('password', sfValidatorSchemaCompare::EQUAL, 'confirm_password',
						array(),
						array(
							'invalid' => __("Passwords do not match, please check and try again.")
						)
					)
				)
			)
		);
	}
}