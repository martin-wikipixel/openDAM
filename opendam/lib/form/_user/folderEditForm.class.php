<?php
class FolderEditForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'group_id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'subfolder' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'subfolder2' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'inside' => new sfWidgetFormInputHidden(
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
						"style" => "width:385px; height: 80px; float: left;"
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'group_id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'subfolder' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'subfolder2' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'inside' => new sfValidatorString(
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
						'required' => true
					),
					array(
						'required' => __("Folder name is required.")
					)
				),
				'description' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Description is required.")
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
		$id = $values["id"];
		$name = $values["name"];
		$group_id = $values["group_id"];

		if($folder = FolderPeer::retrieveByName($name)) {
			if($folder->getGroupeId() == $group_id && $folder->getId() != $id)
				throw new sfValidatorError($validator, __("Folder name already exists in this group."));
		}

		return $values;
	}
}