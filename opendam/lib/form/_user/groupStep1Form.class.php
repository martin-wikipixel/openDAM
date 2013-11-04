<?php
class GroupStep1Form extends BaseForm
{
	public function configure()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'redirect' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'name' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:385px; float:left;"
					)
				),
				'description' => new sfWidgetFormTextarea(
					array(),
					array(
						"style" => "width:385px; float:left;"
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
				'redirect' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'name' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'description' => new sfValidatorString(
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
		$id = $values["id"];
		$name = $values["name"];
		$description = $values["description"];

		if(empty($id))
		{
			if(empty($name))
				throw new sfValidatorError($validator, __("Group name is required."));

			if(empty($description))
				throw new sfValidatorError($validator, __("Description is required."));

			if($groupe = GroupePeer::retrieveByName($name))
			{
				if($groupe->getId() != $id)
					throw new sfValidatorError($validator, __("The same named group already exists. Please enter an another name."));
			}
		}

		return $values;
	}
}

