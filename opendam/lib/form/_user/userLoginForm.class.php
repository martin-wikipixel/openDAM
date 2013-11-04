<?php
class UserLoginForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'username' => new sfWidgetFormInputText(
					array(),
					array(
							"type" => "email",
							"placeholder" => __("Email"),
							"class" => "input-block-level",
							"required" => "required"
					)
				),
				'password' => new sfWidgetFormInputPassword(
					array(),
					array(
							"placeholder" => __("Password"),
							"class" => "input-block-level",
							"required" => "required"
					)
				),
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'username' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Username is required.")
					)
				),
				'password' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Password is required.")
					)
				),
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkLogin")
				)
			)
		);
	}

	public function checkLogin($validator, $values)
	{
		$username = $values["username"];
		$password = $values["password"];
		$user = UserPeer::retrieveByLogin($username);

		if($user)
		{
			if(md5($password) != $user->getPassword())
				throw new sfValidatorError($validator, __("Authentication failed."));
		}
		elseif(!$user)
		{
			$user = UserPeer::retrieveByEmail($username, null);

			if($user)
			{
				if(md5($password) != $user->getPassword())
					throw new sfValidatorError($validator, __("Authentication failed."));
			}
			else
				throw new sfValidatorError($validator, __("Authentication failed."));
		}
		else
			throw new sfValidatorError($validator, __("Authentication failed."));

		$this->setOption("user", $user);
		
		return $values;
	}
}