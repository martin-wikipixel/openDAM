<?php
class PresetUpdateForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'name' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "margin-top: 0px; margin-bottom: 0px;"
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
				'name' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Name is required.")
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkName")
				)
			)
		);
	}

	public function checkName($validator, $values)
	{
		$name = $values["name"];
		$id = $values["id"];

		if($preset = PresetPeer::retrieveByNameAndCustomerId($name, sfContext::getInstance()->getUser()->getCustomerId())) {
			if(empty($id) || $id != $preset->getId())
				throw new sfValidatorError($validator, __("Name already exists."));
		}

		return $values;
	}
}