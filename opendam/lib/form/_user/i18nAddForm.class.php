<?php
class I18nAddForm extends BaseForm
{
	var $list_lang = array();

	public function configure()
	{
		$i18n = sfContext::getInstance()->getI18N();
		$messageSource = $i18n->createMessageSource(sfConfig::get("sf_app_i18n_dir"));

		$catalogues = $messageSource->catalogues();
		foreach ($catalogues as $item)
		{
			if (!array_key_exists($item[1], $this->list_lang))
				$this->list_lang[$item[1]] = $item[1];
		}

		$widgetArray = Array();
		foreach($this->list_lang as $lang)
		{
			$widgetArray["label_".$lang] = new sfWidgetFormInputText(
													array(),
													array(
														"style" => "width:250px; float:left;"
													)
											);
		}

		$this->setWidgets(
			array_merge(
				array(
					'constant' => new sfWidgetFormInputText(
						array(),
						array(
							"style" => "width:250px; float:left;"
						)
					)
				),
				$widgetArray
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$validatorArray = Array();
		foreach($this->list_lang as $lang)
		{
			$validatorArray["label_".$lang] = new sfValidatorString(
													array(
														'required' => true
													),
													array(
														'required' => __('Label "%lang%" is required.', array("%lang%" => $lang))
													)
												);
		}

		$this->setValidators(
			array_merge(
				array(
					'constant' => new sfValidatorString(
						array(
							'required' => true
						),
						array(
							'required' => __("Constant name is required.")
						)
					)
				),
				$validatorArray
			)
		);
	}
}