<?php
class GuestAcceptForm extends BaseForm
{
	public function configure()
	{
		$arrayCountry = CountryPeer::getInArray();

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'code' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'company' => new sfWidgetFormInputText(
					array(),
					array()
				),
				'email' => new sfWidgetFormInputText(
					array(),
					array(
						"class" => "disabled",
						"readonly" => "readonly"
					)
				),
				'password' => new sfWidgetFormInputPassword(
					array(),
					array()
				),
				'lastname' => new sfWidgetFormInputText(
					array(),
					array()
				),
				'firstname' => new sfWidgetFormInputText(
					array(),
					array()
				),
				'phone_code' => new sfWidgetFormInputText(
					array(),
					array(
						"readonly" => "readonly"
					)
				),
				'phone' => new sfWidgetFormInputText(
					array(),
					array()
				),
				'country' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayCountry
					),
					array()
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'code' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'company' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Company is required.")
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
				'password' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Password is required.")
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
				'firstname' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("First name is required.")
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
	}
} ?>