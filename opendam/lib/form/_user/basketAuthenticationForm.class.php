<?php
class BasketAuthenticationForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'password' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
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
						'required' => __("Password is required.")
					)
				),
				'id' => new sfValidatorString(
					array(
						'required' => false
					)
				)
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
		$password = $values["password"];
		$basket_id = $values["id"];

		$basket = BasketPeer::retrieveByPk($basket_id);

		if($basket)
		{
			if($basket->getPassword() != md5($password))
				throw new sfValidatorError($validator, __("Authentication failed."));
		}
		else
			throw new sfValidatorError($validator, __("Authentication failed."));

		return $values;
	}
}