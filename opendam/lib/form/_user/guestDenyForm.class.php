<?php
class GuestDenyForm extends BaseForm
{
	public function configure()
	{
		$arrayCountry = CountryPeer::getInArray();

		$arrayCulture = CulturePeer::getInArray();

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'code' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'deny' => new sfWidgetFormTextarea(
					array(),
					array()
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
				'code' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'deny' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Deny reason is required.")
					)
				)
			)
		);
	}
} ?>