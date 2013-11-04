<?php
class UserEditForm extends BaseForm
{
	public function configure()
	{
		$arrayCountry = CountryPeer::getInArray();

		$arrayCulture = CulturePeer::getInArray();

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
				'firstname' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:200px; float: left;"
					)
				),
				'lastname' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:200px; float: left;"
					)
				),
				'email' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:200px; float: left;"
					)
				),
				'position' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:200px; float: left;"
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
						"style" => "width:144px; float: left;"
					)
				),
				'country' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayCountry
					),
					array(
						"style" => "float:left; width:206px;"
					)
				),
				'culture' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayCulture
					),
					array(
						"style" => "float:left; width:206px;"
					)
				),
				'role_id' => new sfWidgetFormChoice(
					array(
						'choices'  => array(0=>__("Select role"), RolePeer::__ADMIN =>__("Administrator"), RolePeer::__READER =>__("User"))
					),
					array(
						"style" => "width:206px; float: left;"
					)
				),
				'customer' => new sfWidgetFormInputHidden(
					array(),
					array()
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
				'email' => 	new sfValidatorEmail(
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
		$id = $values["id"];
		$customer = $values["customer"];
		$email = $values["email"];

		if($user = UserPeer::retrieveByEmail($email, $customer))
		{
			if($user->getId() != $id)
				throw new sfValidatorError($validator, __("Email already exists."));
		}

		return $values;
	}
}