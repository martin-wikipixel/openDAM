<?php
class BasketPublicShowForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'email' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:385px; float: left;",
						"class" => "nc"
					)
				),
				'comment' => new sfWidgetFormTextarea(
					array(),
					array(
						"style" => "width:385px; height: 150px; float:left;",
						"class" => "nc"
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'email' => 	new sfValidatorEmail(
					array(
						'required' => true
					),
					array(
						'required' => __("Email is required."),
						'invalid' => __("Email address is invalid.")
					)
				),
				'comment' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Comment is required.")
					)
				)
			)
		);
	}
}