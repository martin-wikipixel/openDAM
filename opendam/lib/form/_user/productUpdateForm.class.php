<?php
class ProductUpdateForm extends BaseForm
{
	public function configure()
	{
		$widgets = array();
		$validators = array();

		$widgets["id"] = new sfWidgetFormInputHidden(
			array(),
			array()
		);

		$validators["id"] = new sfValidatorString(
			array(
				'required' => false
			)
		);

		foreach(sfConfig::get("app_languages_available") as $language)
		{
			$widgets["title_".$language] = new sfWidgetFormInputText(
				array(),
				array(
					"style" => "width:250px; float:left;"
				)
			);

			$validators["title_".$language] = new sfValidatorString(
					array(
					'required' => true
				),
				array(
					'required' => __("Name (%1%) is required.", array("%1%" => $language))
				)
			);

			$widgets["description_".$language] = new sfWidgetFormTextarea(
				array(),
				array(
					"style" => "width:250px; float:left;"
				)
			);

			$validators["description_".$language] = new sfValidatorString(
				array(
					'required' => false
				),
				array()
			);
		}

		$widgets["default_product"] = new sfWidgetFormChoice(
			array(
				'choices'  => array(__('No'), __('Yes'))
			),
			array(
				"style" => "float:left; width:257px;"
			)
		);

		$validators["default_product"] = new sfValidatorChoice(
			array(
				'required' => false,
				'choices' => array_keys(array(__('No'), __('Yes')))
			),
			array()
		);

		$this->setWidgets($widgets);
		$this->widgetSchema->setNameFormat('data[%s]');
		$this->setValidators($validators);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkName")
				)
			)
		);
	}

	public function checkName($validator, $values)
	{
		$id = $values["id"];

		foreach(sfConfig::get("app_languages_available") as $language)
		{
			$title = $values["title_".$language];

			if($product = ProductPeer::retrieveByName($title))
			{
				if(empty($id) || $id != $product->getId())
					throw new sfValidatorError($validator, __("Name (%1%) already exists.", array("%1%" => $language)));
			}
		}

		return $values;
	}
}