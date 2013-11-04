<?php
class FolderPublicShowForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'email' => new sfWidgetFormInputText(
					array(),
					array(
						"placeholder" => __("Email address"),
						"class" => "input-block-level"
					)
				),
				'comment' => new sfWidgetFormTextarea(
					array(),
					array(
						"placeholder" => __("Enter your comment here."),
						"class" => "input-block-level"
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