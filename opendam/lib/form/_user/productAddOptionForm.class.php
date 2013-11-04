<?php
class ProductAddOptionForm extends BaseForm
{
	public function configure()
	{
		if(sfContext::getInstance()->getRequest()->getParameter("id") || $data = sfContext::getInstance()->getRequest()->getParameter("data")) {
			if(isset($data))
				$id = $data["id"];
			else
				$id = sfContext::getInstance()->getRequest()->getParameter("id");
		}

		$options = $temp = ProductOptionPeer::retrieveAllForSelect($id);
		$temp[0] = __("Select option");
		ksort($temp);

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'option' => new sfWidgetFormChoice(
					array(
						'choices'  => $temp
					),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'qty' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'price' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
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
				'option' => new sfValidatorChoice(
					array(
						'choices' => array_keys($options)
					),
					array(
						'invalid' => __("Option is required."),
						'required' => __("Option is required."),
					)
				),
				'qty' => new sfValidatorNumber(
					array(
						'required' => true,
						'min' => 0
					),
					array(
						'required' => __("Maximum quantity is required."),
						'invalid' => __("Maximum quantity is required."),
						'min' => __("Maximum quantity must be at least 1.")
					)
				),
				'price' => new sfValidatorNumber(
					array(
						'required' => true,
						'min' => 0
					),
					array(
						'required' => __("Price is required."),
						'invalid' => __("Price is required."),
						'min' => __("The price must be at least 0 euro.")
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkOption")
				)
			)
		);
	}

	public function checkOption($validator, $values)
	{
		$id = $values["id"];
		$option = $values["option"];

		if(ProductHasOptionPeer::retrieveByProductIdAndOptionId($id, $option))
			throw new sfValidatorError($validator, __("Option already exists."));

		return $values;
	}
}