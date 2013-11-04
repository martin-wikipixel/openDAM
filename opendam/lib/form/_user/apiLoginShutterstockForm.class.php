<?php
class ApiLoginShutterstockForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'size_selected' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'username' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'password' => new sfWidgetFormInputPassword(
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
				'size_selected' => new sfValidatorString(
					array(
						'required' => false
					)
				),
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

		$api = new Shutterstock_api();
		$api->setAuthentication(sfConfig::get("app_shutterstock_user"), sfConfig::get("app_shutterstock_key"));
		$response = $api->loginUser($username, $password);

		if(!empty($response))
			return $values;

		throw new sfValidatorError($validator, __("Authentication failed."));
	}
}