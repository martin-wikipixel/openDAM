<?php
class ApiLoginFotoliaForm extends BaseForm
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

		$api = new Fotolia_Api(sfConfig::get("app_fotolia_key"));
		try {
			$api->loginUser($username, $password);
		} catch(Fotolia_Api_Exception $e) {
			switch($e->getCode())
			{
				case "001":
				case "002":
				case "010":
				case "011":
				case "031":
				case "032":
					throw new sfValidatorError($validator, __("Communication error with the Fotolia API. Code")." ".$e->getCode().".");
				break;

				case "4000":
				case "4001":
				case "4002":
					throw new sfValidatorError($validator, __("Authentication failed."));
				break;
			}
		}

		return $values;
	}
}