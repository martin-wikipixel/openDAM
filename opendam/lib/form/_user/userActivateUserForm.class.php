<?php
class UserActivateUserForm extends BaseForm
{
	public function configure()
	{
		$arrayCountry = CountryPeer::getInArray();

		$this->setWidgets(
			array(
				'h' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'v' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'company' => new sfWidgetFormInputText(
					array(),
					array(
						"class" => "disabled",
						"readonly" => "readonly"
					)
				),
				'email' => new sfWidgetFormInputText(
					array(),
					array(
						"class" => "disabled",
						"readonly" => "readonly"
					)
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
				),
				'name' => new sfWidgetFormInputText(
					array(),
					array()
				),
				'first_name' => new sfWidgetFormInputText(
					array(),
					array()
				),
				'password' => new sfWidgetFormInputPassword(
					array(),
					array()
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'h' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'v' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'company' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'email' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'phone_code' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'phone' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
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
				'password' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Password is required.")
					)
				)
			)
		);
	}
}