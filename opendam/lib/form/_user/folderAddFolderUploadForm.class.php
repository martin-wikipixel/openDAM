<?php
class FolderAddFolderUploadForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'group_id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'name' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
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
				'name' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Folder name is required.")
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(new sfValidatorCallback(array("callback" => array($this, "checkName"))));
	}

	public function checkName($validator, $values)
	{
		$name = $values["name"];
		$group_id = $values["group_id"];

		if($folder = FolderPeer::retrieveByName($name)) {
			if($folder->getGroupeId() == $group_id)
				throw new sfValidatorError($validator, __("Folder name already exists in this group."));
		}

		return $values;
	}
}