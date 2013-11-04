<?php
class Backend_Account_EditForm extends BaseForm
{
	public function configure()
	{
		$arrayCountry = FormUtils::buildSelect($this->getOption("countries"), "getName");
		$arrayCulture = CulturePeer::getInArray();

		$sf_user = sfContext::getInstance()->getUser();
		
		$widgets = 
			array(
					"firstname" => new sfWidgetFormInputText(
						array(),
						array()
					),
					"lastname" => new sfWidgetFormInputText(
						array(),
						array()
					),
					"language" => new sfWidgetFormChoice(
						array(
							"choices"  => $arrayCulture
						),
						array()
					),
					'phone_code' => new sfWidgetFormInputText(
							array(),
							array(
									"class" => "phone-code",
									"readonly" => "readonly"
							)
					),
					'phone' => new sfWidgetFormInputText(
							array(),
							array("class" => "phone")
					),
					'country' => new sfWidgetFormChoice(
							array(
									'choices'  => $arrayCountry
							),
							array()
					),
			);
		
		$validators =
			array(
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
					"language" => new sfValidatorChoice(
						array(
							"choices" => array_keys($arrayCulture)
						),
						array(
							"invalid" => __("Please select culture."),
							"required" => __("Please select culture.")
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
			);
		
		if ($sf_user->isAdmin()) {
			
			$widgets["email"] = new sfWidgetFormInputText(
					array(),
					array()
			);
			
			$validators["email"] = new sfValidatorAnd(
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
					);
		}
		
		$this->setWidgets($widgets);
		$this->widgetSchema->setNameFormat("data[%s]");
		$this->setValidators($validators);
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