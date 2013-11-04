<?php
class ProductAddPricingForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'nb_users' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'nb_files' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'disk_space' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'period' => new sfWidgetFormChoice(
					array(
						'multiple' => true,
						'choices'  => PeriodPeer::retrieveAllPeriodsForSelect(true)
					),
					array(
						"style" => "width:250px; height: 75px; float:left;"
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
				'nb_users' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Number of users is required.")
					)
				),
				'nb_files' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Number of files is required.")
					)
				),
				'disk_space' => new sfValidatorNumber(
					array(
						'required' => true,
						'min' => 0
					),
					array(
						'required' => __("Disk space is required."),
						'invalid' => __("Disk space is required."),
						'min' => __("Disk space must be at least 0 MB.")
					)
				),
				'period' => new sfValidatorChoice(
					array(
						'multiple' => true,
						'min' => 1,
						'choices' => array_keys(PeriodPeer::retrieveAllPeriodsForSelect())
					),
					array(
						'invalid' => __("Please select one period at least."),
						'required' => __("Please select one period at least."),
						'min' => __("Please select one period at least.")
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
					"callback" => array($this, "checkPricing")
				)
			)
		);
	}

	public function checkPricing($validator, $values)
	{
		$id = $values["id"];
		$nb_users = $values["nb_users"];
		$nb_files = $values["nb_files"];
		$disk_space = $values["disk_space"];
		$period = $values["period"];

		if ($period && PricingPeer::retrieveByProductIdAndDiskSpaceAndNbUsersAndNbFilesAndPeriods($id, 
				($disk_space * 1024 * 1024), $nb_users, $nb_files, $period))
			throw new sfValidatorError($validator, __("Pricing already exists."));

		return $values;
	}
}