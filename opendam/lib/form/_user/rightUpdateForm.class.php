<?php
class RightUpdateForm extends BaseForm
{
	public function configure()
	{
		$usage_right = Array();
		$fied_type = Array();

		$usage_right = UsageRightPeer::getRight();
		$usage_right[0] = __("Select usage right");
		ksort($usage_right);

		$field_type = UsageRightTypePeer::getType();
		$field_type[0] = __("Select type");
		ksort($field_type);

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'title' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'description' => new sfWidgetFormTextarea(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'right' => new sfWidgetFormChoice(
					array(
						'choices' => $usage_right,
					),
					array(
						"style" => "width:254px; float:left;"
					)
				),
				'editable' => new sfWidgetFormInputCheckbox(
					array(),
					array(
						"value" => 1
					)
				),
				'type' => new sfWidgetFormChoice(
					array(
						'choices' => $field_type,
					),
					array(
						"style" => "width:254px; float:left;"
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
				'title' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Option name is required.")
					)
				),
				'description' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'right' => new sfValidatorChoice(
					array(
						'choices' => array_keys($usage_right),
						'required' => false
					),
					array()
				),
				'editable' =>new sfValidatorString(
					array(
						'required' => false
					)
				),
				'type' => new sfValidatorChoice(
					array(
						'choices' => array_keys($field_type),
						'required' => false
					),
					array()
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
		$title = $values["title"];
		$id = $values["id"];
		$right_form = $values["right"];
		$editable = $values["editable"];
		$type = $values["type"];

		if($editable == 1 && empty($type))
			throw new sfValidatorError($validator, __("Please select field type."));

		if($right = UsageRightPeer::retrieveByTitle($title))
		{
			if($right->getId() != $id && $right->getRightId() == $right_form)
				throw new sfValidatorError($validator, __("Option name already exists."));
		}

		return $values;
	}
}