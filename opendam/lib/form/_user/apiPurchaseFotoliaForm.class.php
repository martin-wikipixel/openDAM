<?php
class ApiPurchaseFotoliaForm extends BaseForm
{
	public function configure()
	{
		$groups = GroupePeer::getUploadGroups(sfContext::getInstance()->getUser()->getId());
		$groups[0] = __("Select group");
		ksort($groups);

		$this->setWidgets(
			array(
				'url' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'group' => new sfWidgetFormChoice(
					array(
						'choices'  => $groups
					),
					array(
						"style" => "float:left; width:250px;",
						"onchange" => "getFolders();"
					)
				),
				'folder' => new sfWidgetFormChoice(
					array(
						'choices'  => array()
					),
					array(
						"style" => "float:left; width:250px;"
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'url' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'group' => new sfValidatorChoice(
					array(
						'choices' => array_keys(GroupePeer::getUploadGroups(sfContext::getInstance()->getUser()->getId()))
					),
					array(
						'invalid' => __("Group is required."),
						'required' => __("Group is required.")
					)
				),
				'folder' => new sfValidatorChoice(
					array(
						'choices' => array_keys(FolderPeer::getUploadFoldersPath(null, sfContext::getInstance()->getUser()->getId())),
					)
					,
					array(
						'invalid' => __("Folder is required."),
						'required' => __("Folder is required.")
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkFolder")
				)
			)
		);
	}

	public function checkFolder($validator, $values)
	{
		$folder = $values["folder"];

		if($folder == 0)
			throw new sfValidatorError($validator, __("Folder is required."));

		return $values;
	}
}