<?php
class DiskUpdateForm extends BaseForm
{
	public function configure()
	{
		$customers = CustomerPeer::getInArray();
		$customers[0] = __('Select customer');
		ksort($customers);

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'name' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:304px; float:left;"
					)
				),
				'path' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:150px; float:left;"
					)
				),
				'default' => new sfWidgetFormChoice(
					array(
						'expanded' => true,
						'choices'  => array(0 => __("No"), 1 => __("Yes")),
					)
				),
				'customer' => new sfWidgetFormChoice(
					array(
						'choices'  => $customers
					),
					array(
						"style" => "float:left; width:311px;"
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
				'name' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Name is required.")
					)
				),
				'path' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Path is required.")
					)
				),
				'default' => new sfValidatorChoice(
					array(
						'choices' => array(0,1)
					),
					array(
						'invalid' => __("Default option is required."),
						'required' => __("Default option is required.")
					)
				),
				'customer' => new sfValidatorChoice(
					array(
						'choices' => array_keys($customers)
					),
					array(
						'invalid' => __("Customer is required."),
						'required' => __("Customer is required.")
					)
				)
			)
		);
	}
}