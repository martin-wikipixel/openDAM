<?php
class Backend_Admin_Group_NewForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				"name" => new sfWidgetFormInputText(
					array(),
					array()
				),
					
				"description" => new sfWidgetFormTextarea(
					array(),
					array()
				)
			)
		);

		$this->widgetSchema->setNameFormat("data[%s]");

		$this->setValidators(
			array(
					"name" => new sfValidatorAnd(
							array(
									new sfValidatorString(
											array("required" => true)
									),
									new sfValidatorCallback(
											array("callback" => array($this, "checkNotExists"))
									)
							)
					),
					
					"description" => new sfValidatorAnd(
							array(
									new sfValidatorString(
											array("required" => false)
									)
							)
					)
			)
		);
	}

	/*________________________________________________________________________________________*/
	public function checkNotExists($validator, $name)
	{
		$customerId = $this->getOption("customerId");
		Assert::ok($customerId > 0);
		
		$unit = UnitPeer::retrieveByName($name, $customerId);
	
		if ($unit) {
			throw new sfValidatorError($validator, __("Unit already exists."));
		}

		return $name;
	}
}