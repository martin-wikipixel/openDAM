<?php
class ConnectorLdapForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'customer_id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'server' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'port' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'dn' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'password' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'type' => new sfWidgetFormInputCheckbox(
					array(),
					array(
						"value" => 1,
						"style" => "float: left;"
					)
				),
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'customer_id' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'server' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Server is required.")
					)
				),
				'port' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Port is required.")
					)
				),
				'dn' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Dn / Rdn is required.")
					)
				),
				'password' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'type' => new sfValidatorString(
					array(
						'required' => false
					)
				),
			)
		);
	}
}