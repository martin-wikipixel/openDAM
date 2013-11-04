<?php
class Backend_Admin_Album_NewForm extends BaseForm
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
					"description" =>new sfValidatorString(
							array(
									'required' => false
							)
					),
			)
		);
	}

	/*________________________________________________________________________________________*/
	public function checkNotExists($validator, $name)
	{
		$album = GroupePeer::retrieveByName($name);

		if ($album) {
			throw new sfValidatorError($validator, __("This album already exists."));
		}

		return $name;
	}
}