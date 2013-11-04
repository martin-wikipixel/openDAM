<?php
class RequestSendRequestForm extends BaseForm
{
	public function configure()
	{
		$groups = GroupePeer::getNoAccessGroupsInArray(sfContext::getInstance()->getUser()->getId());

		$this->setWidgets(
			array(
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