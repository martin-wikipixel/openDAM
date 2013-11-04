<?php
class ConfigurationPersonnalForm extends BaseForm
{
	public function configure()
	{
		$arrayCountry = CountryPeer::getInArray();
		$arrayCulture = CulturePeer::getInArray();

		$this->setWidgets(
			array(
				'fullname' => new sfWidgetFormInputText(
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
				'email' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'country' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayCountry
					),
					array(
						"style" => "float:left; width:256px;"
					)
				),
				'language' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayCulture
					),
					array(
						"style" => "float:left; width:256px;"
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'fullname' => new sfValidatorString(
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
				'position' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
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
					),
					array()
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
				'country' => new sfValidatorChoice(
					array(
						'choices' => array_keys($arrayCountry)
					),
					array(
						'invalid' => __("Please select country."),
						'required' => __("Please select country.")
					)
				),
				'language' => new sfValidatorChoice(
					array(
						'choices' => array_keys($arrayCulture)
					),
					array(
						'invalid' => __("Please select culture."),
						'required' => __("Please select culture.")
					)
				)
			)
		);
	}
}