<?php
class FolderFastEditForm extends BaseForm
{
	public function configure()
	{
		$subfolder_choices = Array();

		if(sfContext::getInstance()->getRequest()->getParameter("subfolder") || $data = sfContext::getInstance()->getRequest()->getParameter("data")) {
			if(isset($data))
				$group_id = $data["group_id"];
			else
				$group_id = sfContext::getInstance()->getRequest()->getParameter("group_id");

			$subfolder_choices = FolderPeer::getAllPathFolder($group_id);
		}

		$this->setWidgets(
			array(
				'group_id' => new sfWidgetFormChoice(
					array(
						'choices'  => GroupePeer::getGroupsInArray(sfContext::getInstance()->getUser()->getId())
					),
					array(
						"style" => "float:left; width:279px;"
					)
				),
				'subfolder' => new sfWidgetFormChoice(
					array(
						'choices'  => $subfolder_choices
					),
					array(
						"style" => "float:left; width:279px;"
					)
				),
				'subfolder2' => new sfWidgetFormInputHidden(
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
						'required' => __("Group name is required.")
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
		$name = $values["name"];
		$group_id = $values["group_id"];

		if($folder = FolderPeer::retrieveByName($name)) {
			if($folder->getGroupeId() == $group_id)
				throw new sfValidatorError($validator, __("Folder name already exists in this group."));
		}

		return $values;
	}
}