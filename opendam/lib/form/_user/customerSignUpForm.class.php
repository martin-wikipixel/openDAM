<?php
class CustomerSignUpForm extends BaseForm
{
	public function configure()
	{
		$arrayCountry = CountryPeer::getInArray();

		$this->setWidgets(
			array(
				'company' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'address' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'address_bis' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'zip' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'city' => new sfWidgetFormInputText(
					array(), array(
						"style" => "width:250px; float:left;"
					)
				),
				'siret' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'ape' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'tva' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'name' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'first_name' => new sfWidgetFormInputText(
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
						'choices'  => $arrayCountry
					),
					array(
						"style" => "float:left; width:257px;"
					)
				),
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'company' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Company is required.")
					)
				),
				'address' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Address is required.")
					)
				),
				'address_bis' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'zip' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Zip code is required.")
					)
				),
				'city' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("City is required.")
					)
				),
				'siret' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'ape' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'tva' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'name' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Name is required.")
					)
				),
				'first_name' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("First name is required.")
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
						'required' => true
					),
					array(
						'required' => __("Phone is required.")
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
				)
			)
		);

		$this->validatorSchema->setPostValidator(new sfValidatorCallback(array("callback" => array($this, "checkEmail"))));
	}

	public function checkEmail($validator, $values)
	{
		$email = $values["email"];

		if(UserPeer::retrieveByEmail($email))
			throw new sfValidatorError($validator, __("Email already exists."));

		return $values;
	}
}