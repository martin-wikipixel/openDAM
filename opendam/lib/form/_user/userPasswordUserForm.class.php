<?php
class UserPasswordUserForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'admin' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'password' => new sfWidgetFormInputPassword(
					array(),
					array(
						"style" => "width:200px; float: left;"
					)
				),
				'confirm_password' => new sfWidgetFormInputPassword(
					array(),
					array(
						"style" => "width:200px; float: left;"
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'admin' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'id' => new sfValidatorString(
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
			new sfValidatorSchemaCompare('password', '==', 'confirm_password',
				array(),
				array('invalid' => __("Passwords do not match, please check and try again."))
			)
		);
	}
}