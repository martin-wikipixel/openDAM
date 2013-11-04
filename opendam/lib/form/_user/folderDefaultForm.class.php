<?php
class FolderDefaultForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'address' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "float: left; font-size: 11px; color: gray; width:320px;",
						"onblur" => "onBlur(this);",
						"onfocus" => "onFocus(this);"
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
				'address' => new sfValidatorString(
					array(
						'required' => false
					)
				)
			)
		);
	}
}