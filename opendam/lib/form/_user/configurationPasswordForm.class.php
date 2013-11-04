<?php
class ConfigurationPasswordForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'password' => new sfWidgetFormInputPassword(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'new_password' => new sfWidgetFormInputPassword(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'verify_password' => new sfWidgetFormInputPassword(
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
				'password' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Current password is required.")
					)
				),
				'new_password' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("New password is required.")
					)
				),
				'verify_password' => new sfValidatorString(
					array(
						'required' => true
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
					new sfValidatorSchemaCompare('new_password', sfValidatorSchemaCompare::EQUAL, 'verify_password',
						array(),
						array(
							'invalid' => __("Passwords do not match, please check and try again.")
						)
					),
					new sfValidatorCallback(
						array(
							"callback" => array($this, "checkPassword")
						)
					),
				)
			)
		);
	}

	public function checkPassword($validator, $values)
	{
		$password = $values["password"];

		$c = new Criteria();
		$c->add(UserPeer::PASSWORD, md5($password));
		$c->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
		$c->add(UserPeer::ID, sfContext::getInstance()->getUser()->getId());

		if(UserPeer::doCount($c) == 0)
			throw new sfValidatorError($validator, __("Please check your current password."));

		return $values;
	}
}