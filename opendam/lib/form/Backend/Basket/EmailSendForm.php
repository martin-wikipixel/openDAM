<?php
class Backend_Basket_EmailSendForm extends BaseForm
{
	public function configure()
	{
		$arrayGroups = FormUtils::buildSelect($this->getOption("groups"), "getTitle");

		$this->setWidgets(
			array(
				"receivers" => new sfWidgetFormInputText(
					array(),
					array()
				),
				"groups" => new sfWidgetFormChoice(
						array(
								"choices" => $arrayGroups
						),
						array()
				),
				"subject" => new sfWidgetFormInputText(
					array(),
					array()
				),
				"message" => new sfWidgetFormTextarea(
					array(),
					array()
				)
			)
		);

		$this->widgetSchema->setNameFormat("data[%s]");

		$this->setValidators(
			array(
					"receivers" => new sfValidatorString(
						array(
							"required" => false
						)
					),
					"groups" => new sfValidatorString(
						array(
							"required" => false
						)
					),
					"subject" => new sfValidatorString(
						array(
							"required" => false
						)
					),
					"message" => new sfValidatorString(
						array(
							"required" => false
						)
					),
			)
		);

		$this->validatorSchema->setPostValidator(
				new sfValidatorCallback(
						array(
								"callback" => array($this, "checkAllReceivers"),
								"arguments" => array("arrayGroups" => array_keys($arrayGroups))
						)
				)
		);
	}

	/*________________________________________________________________________________________________________________*/
	public function checkAllReceivers($validator, $values, $arguments)
	{
		$receivers = $values["receivers"];
		$groups = $values["groups"];
		$arrayGroups = $arguments["arrayGroups"];
		$error = null;

		if (!$groups) {
			if ($receivers) {
				$emails = explode(",", $receivers);

				foreach ($emails as $email) {
					$email = trim($email);

					if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$error = new sfValidatorError($validator, __("Receivers email is invalid."));
					}
				}
			}
			else {
				$error = new sfValidatorError($validator, __("Please enter receivers emails."));
			}

			if ($error) {
				throw new sfValidatorErrorSchema($validator, array("receivers" => $error));
			}
		}
		else {
			if (!in_array($groups, $arrayGroups)) {
				$error = new sfValidatorError($validator, __("Group is invalid."));

				throw new sfValidatorErrorSchema($validator, array("groups" => $error));
			}
		}

		return $values;
	}
}