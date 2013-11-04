<?php
class Backend_Admin_Tag_NewForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				"name" => new sfWidgetFormInputText(
					array(),
					array()
				),
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
			)
		);
	}

	/*________________________________________________________________________________________*/
	public function checkNotExists($validator, $name)
	{
		//$customerId = $this->getOption("customerId");
		//Assert::ok($customerId > 0);
		
		$tag = TagPeer::retrieveByTitle($name);
		
		if ($tag) {
			throw new sfValidatorError($validator, __("The tag is already exists. Please enter another name."));
		}

		return $name;
	}
}