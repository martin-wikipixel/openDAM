<?php
class CustomerEventForm extends BaseForm
{
	public function configure()
	{
		$arrayUser = AdminPeer::getForForm();

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'event_id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'date' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:230px; float:left;",
						"readonly" => true
					)
				),
				'recipient_id' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayUser
					),
					array(
						"style" => "float:left; width:256px;"
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
				'duration' => new sfWidgetFormChoice(
					array(
						'choices'  => Array(5 => __("5 min"), 10 => __("10 min"), 15 => __("15 min"), 20 => __("20 min"), 25 => __("25 min"), 30 => __("30 min"), 35 => __("35 min"), 40 => __("40 min"), 45 => __("45 min"), 50 => __("50 min"), 55 => __("55 min"), 60 => __("60 min"))
					),
					array(
						"style" => "float:left; width:256px;"
					)
				),
				'priority' => new sfWidgetFormChoice(
					array(
						'choices'  => CustomerEventPeer::getPriority()
					),
					array(
						"style" => "float:left; width:256px;"
					)
				),
				'notice' => new sfWidgetFormChoice(
					array(
						'choices'  => Array(5 => __("5 min"), 10 => __("10 min"), 15 => __("15 min"), 30 => __("30 min"), 60 => __("60 min"))
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
				'event_id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'date' => new sfValidatorString(
					array(
						'required' => true
					)
				),
				'recipient_id' => new sfValidatorChoice(
					array(
						'choices' => array_keys($arrayUser)
					),
					array(
						'invalid' => __("Recipient is required."),
						'required' => __("Recipient is required.")
					)
				),
				'title' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Title is required.")
					)
				),
				'description' => new sfValidatorString(
					array(
						'required' => false
					),
					array(
						'required' => __("Description is required.")
					)
				),
				'duration' => new sfValidatorChoice(
					array(
						'choices' => Array(5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60)
					),
					array(
						'invalid' => __("Duration is required."),
						'required' => __("Duration is required.")
					)
				),
				'priority' => new sfValidatorChoice(
					array(
						'choices' => array_keys(CustomerEventPeer::getPriority())
					),
					array(
						'invalid' => __("Priority is required."),
						'required' => __("Priority is required.")
					)
				),
				'notice' => new sfValidatorChoice(
					array(
						'choices' => Array(5, 10, 15, 30, 60)
					),
					array(
						'invalid' => __("Notice is required."),
						'required' => __("Notice is required.")
					)
				)
			)
		);
	}
}