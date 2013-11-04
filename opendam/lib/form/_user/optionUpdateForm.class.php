<?php
class OptionUpdateForm extends BaseForm
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

			if($option = ProductOptionPeer::retrieveByName($title))
			{
				if(empty($id) || $id != $option->getId())
					throw new sfValidatorError($validator, __("Name (%1%) already exists.", array("%1%" => $language)));
			}
		}

		return $values;
	}
}