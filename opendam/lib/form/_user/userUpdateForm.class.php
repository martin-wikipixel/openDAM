<?php
class UserUpdateForm extends BaseForm
{
	public function configure()
	{
		$arrayCustomer = CustomerPeer::getInArray();

		$arrayCountry = CountryPeer::getInArray();

		$arrayCulture = CulturePeer::getInArray();

		$this->setWidgets(
			array(
				'admin' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'password' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;",
						"rel" => "my_pass"
					)
				),
				'firstname' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'lastname' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'email' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'position' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'phone_code' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:40px; float:left; margin-right: 10px;",
						"readonly" => "readonly"
					)
				),
				'phone' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:194px; float:left;"
					)
				),
				'country' => new sfWidgetFormChoice(
					array(
						'choices' => $arrayCountry
					),
					array(
						"style" => "float:left; width:256px;"
					)
				),
				'culture' => new sfWidgetFormChoice(
					array(
						'choices' => $arrayCulture
					),
					array(
						"style" => "float:left; width:256px;"
					)
				),
				'role_id' => new sfWidgetFormChoice(
					array(
						'choices' => array(0=>__("Select"), RolePeer::__ADMIN =>__("Administrator"), RolePeer::__READER =>__("User"))
					),
					array(
						"style" => "float:left; width:256px;"
					)
				),
				'customer' => new sfWidgetFormChoice(
					array(
						'choices' => $arrayCustomer
					),
					array(
						"style" => "float:left; width:256px;"
					)
				),
				'send_username' => new sfWidgetFormInputCheckbox(
					array(),
					array(
						"style" => "margin: 0px; padding: 0px;"
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
				'password' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Password is required.")
					)
				),
				'firstname' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("First name is required.")
					)
				),
				'lastname' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Last name is required.")
					)
				),
				'email' => new sfValidatorEmail(
					array(
						'required' => true
					),
					array(
						'required' => __("Email is required."),
						'invalid' => __("Email address is invalid.")
					)
				),
				'position' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'phone_code' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Phone code is required.")
					)
				),
				'phone' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'country' => new sfValidatorChoice(
					array(
						'choices' => array_keys($arrayCountry)
					),
					array(
						'invalid' => __("Please select country."),
						'required' => __("Please select country.")
					)
				),
				'culture' => new sfValidatorChoice(
					array(
						'choices' => array_keys($arrayCulture)
					),
					array(
						'invalid' => __("Please select culture."),
						'required' => __("Please select culture.")
					)
				),
				'role_id' => new sfValidatorChoice(
					array(
						'choices' => array(RolePeer::__ADMIN, RolePeer::__READER)
					),
					array(
						'invalid' => __("Select user role."),
						'required' => __("Select user role.")
					)
				),
				'customer' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'send_username' => new sfValidatorBoolean(
					array(
						'true_values' => array(true, false),
						'required' => false
					),
					array()
				)
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkRight")
				)
			)
		);
	}

	public function checkRight($validator, $values)
	{
		$admin = $values["admin"];
		$customer = $values["customer"];
		$email = $values["email"];

		if(UserPeer::retrieveByEmail($email, null, false))
			throw new sfValidatorError($validator, __("Email already exists."));

		if($admin == 1 && $customer <= 0)
			throw new sfValidatorError($validator, __("Customer is required."));

		return $values;
	}
}