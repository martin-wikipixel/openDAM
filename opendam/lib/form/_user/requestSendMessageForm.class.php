<?php
class RequestSendMessageForm extends BaseForm
{
	public function configure()
	{
		$groups = GroupePeer::getGroupsInArray();
		$options = array();
		$options[0] = __("Select subject");

		if(sizeof($groups))
			$options[1] = __("A particular group");

		$options[2] = __("Technical problems");
		$options[3] = __("Other");

		$this->setWidgets(
			array(
				'request_type' => new sfWidgetFormChoice(
					array(
						'choices'  => $options
					),
					array(
						"style" => "float: left; width:250px;",
						"onchange" => "toggleRelatedGroup(this);"
					)
				),
				'group_id' => new sfWidgetFormChoice(
					array(
						'choices'  => $groups
					),
					array(
						"style" => "float: left; width:250px;"
					)
				),
				'message' => new sfWidgetFormTextarea(
					array(),
					array(
						"style" => "float:left; width:400px; height:100px;"
					)
				),
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'request_type' => new sfValidatorChoice(
					array(
						'required' => true,
						'choices' => array_keys($options)
					),
					array(
						'required' => __("Select your request type."),
						'invalid' => __("Select your request type.")
					)
				),
				'group_id' => new sfValidatorChoice(
					array(
						'required' => false,
						'choices' => array_keys($groups)
					),
					array()
				),
				'message' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Message is required.")
					)
				)
			)
		);
	}
}