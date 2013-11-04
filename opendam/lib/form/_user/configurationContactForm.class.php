<?php
class ConfigurationContactForm extends BaseForm
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
					array(),
					array(
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
				'phone_customer' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'mobile' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'fax' => new sfWidgetFormInputText(
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
				'emailing' => new sfWidgetFormInputCheckbox(
					array(),
					array(
						"style" => "float:left;"
					)
				),
				'emailing_partner' => new sfWidgetFormInputCheckbox(
					array(),
					array(
						"style" => "float:left;"
					)
				)
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
						'required' => true
					),
					array(
						'required' => __("Siret is required.")
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
				'phone_customer' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Customer's phone is required.")
					)
				),
				'mobile' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'fax' => new sfValidatorString(
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
				'emailing' =>new sfValidatorString(
					array(
						'required' => false
					)
				),
				'emailing_partner' =>new sfValidatorString(
					array(
						'required' => false
					)
				)
			)
		);
	}
} ?>