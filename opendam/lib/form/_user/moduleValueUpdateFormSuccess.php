<?php
class ModuleValueUpdateForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'form_value' => new sfWidgetFormInputHidden(
					array(),
					array(
						"value" => 1
					)
				),
				'label' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'value' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
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
				'form_value' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'label' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Value's label is required.")
					)
				),
				'value' =>new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Value is required.")
					)
				),
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
		$label = $values["label"];
		$id = $values["id"];

		if($module = ModuleValuePeer::retrieveByModuleAndName($id, $label))
			throw new sfValidatorError($validator, __("Value's name already exists."));

		return $values;
	}
}