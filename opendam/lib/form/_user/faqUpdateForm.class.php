<?php
class FaqUpdateForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'title' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:400px; float:left;"
					)
				),
				'content' => new sfWidgetFormTextareaTinyMCE(
					array(),
					array(
						"style" => "height: 400px; width: 400px; float:left;"
					)
				),
				'sort' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:50px; float:left;"
					)
				),
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
				'title' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Title is required.")
					)
				),
				'content' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Content is required.")
					)
				),
				'sort' => new sfValidatorString(
					array(
						'required' => false
					)
				),
			)
		);
	}
}