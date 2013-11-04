<?php
class UserImportForm extends BaseForm
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
				'users' => new sfWidgetFormInputFile(
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
				'culture' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayCulture
					),
					array(
						"style" => "float:left; width:256px;"
					)
				),
				'role_id' => new sfWidgetFormChoice(
					array(
						'choices'  => array(0=>__("Select"), RolePeer::__ADMIN =>__("Administrator"), RolePeer::__READER =>__("User"))
					),
					array(
						"style" => "float:left; width:256px;"
					)
				),
				'customer' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayCustomer
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
				'users' => new sfValidatorFile(
					array(
						'required' => true,
						'path' => sfConfig::get("app_path_temp_dir")
					)
				),
				'admin' => new sfValidatorString(
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

		if($admin == 1 && $customer <= 0)
			throw new sfValidatorError($validator, __("Customer is required."));

		return $values;
	}
}