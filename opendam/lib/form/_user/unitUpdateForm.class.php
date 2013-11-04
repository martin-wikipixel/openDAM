<?php
class UnitUpdateForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'title' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left; margin-top: 4px;"
					)
				),
				'user_id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'new_user' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left; margin-top: 4px;"
					)
				),
				'users_unit' => new sfWidgetFormInputHidden(
					array(),
					array()
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
						'required' => __("Name is required.")
					)
				),
				'new_user' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'user_id' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'users_unit' => new sfValidatorString(
					array(
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

		if($unit = UnitPeer::retrieveByName($title)) {
			if(empty($id) || $id != $unit->getId())
				throw new sfValidatorError($validator, __("Unit's name is already exists."));
		}

		return $values;
	}
}