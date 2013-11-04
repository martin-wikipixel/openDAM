<?php
class CustomerUpdateForm extends BaseForm
{
	public function configure()
	{
		$arrayCountry = CountryPeer::getInArray();

		$arrayActivity = ActivityPeer::getInArray();
		$arrayActivity[0] = __("Select");
		ksort($arrayActivity);

		$arraySource = SourcePeer::getInArray();
		$arraySource[0] = __("Select");
		ksort($arraySource);

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
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
				'test' => new sfWidgetFormInputCheckbox(
					array(),
					array(
						"value" => 1
					)
				),
				'cname' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'source' => new sfWidgetFormChoice(
					array(
						'choices'  => $arraySource
					),
					array(
						"style" => "float:left; width:257px;"
					)
				),
				'activity' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayActivity
					),
					array(
						"style" => "float:left; width:257px;"
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
				'id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'company' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'address' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'address_bis' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'zip' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'city' => new sfValidatorString(
					array(
						'required' => false
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
						'required' => false
					)
				),
				'first_name' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'email' => new sfValidatorEmail(
					array(
						'required' => false
					),
					array(
						'invalid' => __("Email address is invalid.")
					)
				),
				'phone_code' => new sfValidatorString(
					array(
						'required' => false
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
				'test' =>new sfValidatorString(
					array(
						'required' => false
					)
				),
				'cname' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'source' => new sfValidatorChoice(
					array(
						'required' => false,
						'choices' => array_keys($arraySource)
					),
					array(
						'invalid' => __("Please select source."),
						'required' => __("Please select source.")
					)
				),
				'activity' => new sfValidatorChoice(
					array(
						'required' => false,
						'choices' => array_keys($arrayActivity)
					),
					array(
						'invalid' => __("Please select activity."),
						'required' => __("Please select activity.")
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
}