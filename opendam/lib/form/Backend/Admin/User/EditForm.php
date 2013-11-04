<?php
class Backend_Admin_User_EditForm extends BaseForm
{
	public function configure()
	{
		$arrayCountry = FormUtils::buildSelect($this->getOption("countries"), "getName");
		$arrayCulture = CulturePeer::getInArray();

		$this->setWidgets(
			array(
				"id" => new sfWidgetFormInputHidden(
					array(),
					array()
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
					array()
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
				"comment" => new sfWidgetFormTextarea(
						array(),
						array()
				)
				/*"customer" => new sfWidgetFormChoice(
					array(
						"choices" => $arrayCustomer
					),
					array()
				),*/
			)
		);

		$this->widgetSchema->setNameFormat("data[%s]");

		$this->setValidators(
			array(
				"id" => new sfValidatorString(
						array(
								'required' => false
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
				"comment" => new sfValidatorString(
						array(
								"required" => false
						)
				)
				/*"customer" => new sfValidatorString(
					array(
						"required" => false
					),
					array()
				),*/
			)
		);

		/*
		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkRight")
				)
			)
		);*/
	}

	/*________________________________________________________________________________________*/
	public function checkNotExists($validator, $email)
	{
		$user = $this->getOption("user");
		
		Assert::ok(!is_null($user));
		
		// si le email a changé, on vérifie que le nouveau email n'existe pas
		if ($email != $user->getEmail()) {
			$user = UserPeer::retrieveByEmail($email);

			if ($user) {
				throw new sfValidatorError($validator, __("Email already exists."));
			}
		}

		return $email;
	}
}
