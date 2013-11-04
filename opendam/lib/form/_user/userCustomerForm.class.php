<?php
class UserCustomerForm extends BaseForm
{
	public function configure()
	{
		$arrayCustomer = CustomerPeer::getInArray();

		$this->setWidgets(
			array(
				'admin' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'customer' => new sfWidgetFormChoice(
					array(
						'choices'  => $arrayCustomer
					),
					array(
						"style" => "width:200px; float: left;"
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'admin' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'id' => new sfValidatorString(
					array(
						'required' => false
					),
					array()
				),
				'customer' => new sfValidatorChoice(
					array(
						'choices' => array_keys($arrayCustomer)
					),
					array(
						'invalid' => __("Please select customer."),
						'required' => __("Please select customer.")
					)
				)
			)
		);
	}
}