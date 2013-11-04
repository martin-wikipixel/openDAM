<?php
class CustomerMemoForm extends BaseForm
{
	public function configure()
	{
		$arrayMemoType = CustomerMemoTypePeer::getInArray();
		$arrayMemoType[0] = __("Select");
		ksort($arrayMemoType);

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'memo' => new sfWidgetFormTextarea(
					array(),
					array(
						"style" => "width:350px; float:left;"
					)
				),
				'type' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayMemoType
					),
					array(
						"style" => "float:left; width:356px;"
					)
				),
				'file' => new sfWidgetFormInputFile(
					array(),
					array(
						"style" => "width:350px; float:left;"
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
				'memo' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Memo is required.")
					)
				),
				'type' => new sfValidatorChoice(
					array(
						'required' => false,
						'choices' => array_keys(CustomerMemoTypePeer::getInArray())
					),
					array(
						'invalid' => __("Please select memo type."),
						'required' => __("Please select memo type.")
					)
				),
				'file' => new sfValidatorFile(
					array(
						'required' => false,
						'mime_types' => null
					),
					array(
						'max_size' => __("File is too large (maximum is 2M bytes).")
					)
				)
			)
		);
	}
}