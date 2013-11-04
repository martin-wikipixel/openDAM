<?php
class Backend_Admin_User_NewForm extends BaseForm
{
	public function configure()
	{
		$arrayCountry = FormUtils::buildSelect($this->getOption("countries"), "getName");
		$arrayCulture = CulturePeer::getInArray();

		$this->setWidgets(
			array(
				"password" => new sfWidgetFormInputPassword(
					array(),
					array("autocomplete" => "off")
				),
				"confirm_password" => new sfWidgetFormInputPassword(
					array(),
					array("autocomplete" => "off")
				),
				"firstname" => new sfWidgetFormInputText(
					array(),
					array()
				),
				"lastname" => new sfWidgetFormInputText(
					array(),
					array()
				),
				"email" => new sfWidgetFormInputText(
					array(),
					array("autocomplete" => "off")
				),
				"position" => new sfWidgetFormInputText(
					array(),
					array()
				),
				"phone_code" => new sfWidgetFormInputText(
					array(),
					array(
						"readonly" => "readonly",
						"class" => "phone-code"
					)
				),
				"phone" => new sfWidgetFormInputText(
					array(),
					array(
						"class" => "phone"
					)
				),
				"country" => new sfWidgetFormChoice(
					array(
						"choices" => $arrayCountry
					),
					array()
				),
				"culture" => new sfWidgetFormChoice(
					array(
						"choices" => $arrayCulture
					),
					array()
				),
				"role_id" => new sfWidgetFormChoice(
					array(
						"choices" => array(0=>__("Select"), RolePeer::__ADMIN =>__("Administrator"), RolePeer::__READER =>__("User"))
					),
					array()
				),
				"send_username" => new sfWidgetFormInputCheckbox(
					array(),
					array()
				),
				"comment" => new sfWidgetFormTextarea(
						array(),
						array()
				)
			)
		);

		$this->widgetSchema->setNameFormat("data[%s]");

		$this->setValidators(
			array(
				"password" => new sfValidatorString(
					array(
						"required" => true,
						"min_length" => 6,
						"max_length" => 200,
					),
					array(
						"required" => __("New password is required."),
					)
				),
				"confirm_password" => new sfValidatorString(
					array(
						"required" => false
					),
					array(
						"required" => __("Please verify your new password.")
					)
				),
				"firstname" => new sfValidatorString(
					array(
						"required" => true
					),
					array(
						"required" => __("First name is required.")
					)
				),
				"lastname" => new sfValidatorString(
					array(
						"required" => true
					),
					array(
						"required" => __("Last name is required.")
					)
				),
				"email" => new sfValidatorAnd(
						array(
							new sfValidatorEmail(
								array("required" => true),
								array("required" => __("Email is required."),"invalid" => 
									__("Email address is invalid."))
							),
							new sfValidatorCallback(
								array("callback" => array($this, "checkNotExists"))
							)
						)
				),
				"position" => new sfValidatorString(
					array(
						"required" => false
					)
				),
				"phone_code" => new sfValidatorString(
					array(
						"required" => true
					),
					array(
						"required" => __("Phone code is required.")
					)
				),
				"phone" => new sfValidatorString(
					array(
						"required" => false
					)
				),
				"country" => new sfValidatorChoice(
					array(
						"choices" => array_keys($arrayCountry)
					),
					array(
						"invalid" => __("Please select country."),
						"required" => __("Please select country.")
					)
				),
				"culture" => new sfValidatorChoice(
					array(
						"choices" => array_keys($arrayCulture)
					),
					array(
						"invalid" => __("Please select culture."),
						"required" => __("Please select culture.")
					)
				),
				"role_id" => new sfValidatorChoice(
					array(
						"choices" => array(RolePeer::__ADMIN, RolePeer::__READER)
					),
					array(
						"invalid" => __("Select user role."),
						"required" => __("Select user role.")
					)
				),
				"send_username" => new sfValidatorBoolean(
					array(
						"true_values" => array(true, false),
						"required" => false
					),
					array()
				),
				"comment" => new sfValidatorString(
						array(
								"required" => false
						)
				)
			)
		);

		$this->validatorSchema->setPostValidator(
				new sfValidatorSchemaCompare("password", "==", "confirm_password",
						array(),
						array("invalid" => __("Passwords do not match, please check and try again."))
				)
		);
	}

	/*________________________________________________________________________________________*/
	public function checkNotExists($validator, $email)
	{
		$user = UserPeer::retrieveByEmail($email);
		
		if ($user) {
			throw new sfValidatorError($validator, __("Email already exists."));
		}
		
		return $email;
	}
}