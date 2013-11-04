<?php
class ConfigurationUpdateIpForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'customer_id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'ip_address' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'owner_ip' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
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
				'customer_id' => new sfValidatorString(
					array(
						'required' => true
					)
				),
				'ip_address' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Ip address is required.")
					)
				),
				'owner_ip' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Owner's ip is required.")
					)
				),
			)
		);

		$this->validatorSchema->setPostValidator(new sfValidatorCallback(array("callback" => array($this, "checkIp"))));
	}

	public function checkIp($validator, $values)
	{
		$customer_id = $values["customer_id"];
		$ip_address = $values["ip_address"];
		$id = $values["id"];

		$c = new Criteria();
		$c->add(TockenIpPeer::CUSTOMER_ID, $customer_id);
		$c->add(TockenIpPeer::CODE, $ip_address);

		if(!empty($id))
			$c->add(TockenIpPeer::ID, $id, Criteria::NOT_EQUAL);

		if(TockenIpPeer::doCount($c) > 0)
			throw new sfValidatorError($validator, __("Ip address already exists."));
		else
			return $values;
	}
}