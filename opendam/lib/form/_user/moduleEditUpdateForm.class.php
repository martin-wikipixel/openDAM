<?php
class ModuleEditUpdateForm extends BaseForm
{
	public function configure()
	{
		$arrayVisibility = ModuleVisibilityPeer::getInArray();

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'form_edit' => new sfWidgetFormInputHidden(
					array(),
					array(
						"value" => 1
					)
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
				'deactivated' => new sfWidgetFormInputCheckbox(
					array(),
					array(
						"value" => 1
					)
				),
				'all_customer' => new sfWidgetFormInputCheckbox(
					array(),
					array(
						"value" => 1
					)
				),
				'range' => new sfWidgetFormChoice(
					array(
						'multiple' => true,
						'choices'  => $arrayVisibility
					),
					array(
						"style" => "float:left; width:256px;"
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
				'form_edit' => new sfValidatorString(
					array(
						'required' => false
					)
				),

				'title' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Name is required.")
					)
				),
				'description' =>new sfValidatorString(
					array(
						'required' => false
					)
				),
				'deactivated' =>new sfValidatorString(
					array(
						'required' => false
					)
				),
				'all_customer' =>new sfValidatorString(
					array(
						'required' => false
					)
				),
				'range' => new sfValidatorChoice(
					array(
						'multiple' => true,
						'choices' => array_keys($arrayVisibility)
					),
					array(
						'invalid' => __("Range is required."),
						'required' => __("Range is required.")
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
		$title = $values["title"];
		$id = $values["id"];

		if($module = ModulePeer::retrieveByName($title)) {
			if(empty($id) || $id != $module->getId())
				throw new sfValidatorError($validator, __("Name already exists."));
		}

		return $values;
	}
}