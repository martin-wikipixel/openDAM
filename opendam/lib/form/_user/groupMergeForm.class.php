<?php
class GroupMergeForm extends BaseForm
{
	public function configure()
	{
		$groups_array = GroupePeer::getGroupsInArray3();
		$groups_array[0] = __('Select group');
		ksort($groups_array);

		$this->setWidgets(
			array(
				'group_from' => new sfWidgetFormChoice(
					array(
						'choices'  => $groups_array
					),
					array(
						"style" => "float:left; width:200px;"
					)
				),
				'group_to' => new sfWidgetFormChoice(
					array(
						'choices'  => $groups_array
					),
					array(
						"style" => "float:left; width:200px;"
					)
				),
				'rights' => new sfWidgetFormChoice(
					array(
						'choices'  => array("yes"=>__("Yes"), "no"=>__("No"))
					),
					array(
						"style" => "float:left; width:200px;"
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'group_from' => new sfValidatorChoice(
					array(
						'choices' => array_keys(GroupePeer::getGroupsInArray3())
					),
					array(
						'invalid' => __("Group is required."),
						'required' => __("Group is required.")
					)
				),
				'group_to' => new sfValidatorChoice(
					array(
						'choices' => array_keys(GroupePeer::getGroupsInArray3())
					),
					array(
						'invalid' => __("Another main folder is required."),
						'required' => __("Another main folder is required.")
					)
				),
				'rights' => new sfValidatorChoice(
					array(
						'choices' => array("yes","no")
					),
					array(
						'invalid' => __("Choice of the maintenance of rights is required."),
						'required' => __("Choice of the maintenance of rights is required.")
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkGroup")
				)
			)
		);
	}

	public function checkGroup($validator, $values)
	{
		$group_from = $values["group_from"];
		$group_to = $values["group_to"];

		if($group_from == $group_to)
			throw new sfValidatorError($validator, __("Please select a different group to merge."));

		return $values;
	}
}

