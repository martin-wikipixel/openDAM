<?php
class Backend_Admin_Preset_NewEditForm extends BaseForm
{
	public function configure()
	{
		$tmp = LicencePeer::getLicenceInArray();
		$licences = FormUtils::buildSelect($tmp, "getName");
		$lincenceKeys = FormUtils::getIds($tmp);

		$tmp = UsageUsePeer::getUses();
		$uses = FormUtils::buildSelect($tmp, "getTitle");
		$useKeys = FormUtils::getIds($tmp);
		
		$tmp = UsageDistributionPeer::getDistributions();
		$distributions = FormUtils::buildSelect($tmp, "getTitle");
		$distributionKeys = FormUtils::getIds($tmp);
		
		$this->setWidgets(
			array(
					"name" => new sfWidgetFormInputText(
						array(),
						array()
					),
					"licence" => new sfWidgetFormChoice(
						array(
							"choices"  => $licences
						),
						array()
					),
					"use" => new sfWidgetFormChoice(
							array(
									"choices"  => $uses
							),
							array()
					),
					"distribution" => new sfWidgetFormChoice(
							array(
									"choices"  => $distributions
							),
							array()
					),
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
					"name" => new sfValidatorAnd(
								array(
										new sfValidatorString(
												array("required" => true)
										),
										new sfValidatorCallback(
												array("callback" => array($this, "checkName"))
										)
								)
					),
					"licence" => new sfValidatorChoice(
							array(
									"choices" => $lincenceKeys
							),
							array()
					),
					"use" => new sfValidatorChoice(
							array(
									"choices" => $useKeys
							),
							array()
					),
					"distribution" => new sfValidatorChoice(
							array(
									"choices" => $distributionKeys
							),
							array()
					),
			)
		);
	}

	/*________________________________________________________________________________________________________________*/
	public function checkName($validator, $name)
	{
		$id = $this->getOption("id");
		$customerId = $this->getOption("customerId");

		Assert::ok($customerId > 0);

		$preset = PresetPeer::retrieveByNameAndCustomerId($name, $customerId);

		if ($preset) {
			if (!$id || ($id && $id != $preset->getId())) {
				throw new sfValidatorError($validator, __("Name already exists."));
			}
		}

		return $name;
	}
}