<?php
class ConfigurationChartForm extends BaseForm
{
	public function configure()
	{
		$widgets = array();
		$validators = array();

		foreach(sfConfig::get("app_languages_available") as $language)
		{
			$widgets["chart_".$language] = new sfWidgetFormInputFile(
				array(),
				array(
					"style" => "width:250px; float:left;"
				)
			);

			$validators["chart_".$language] = new sfValidatorFile(
				array(
					'required' => false,
					'mime_types' => null
				),
				array(
					'max_size' => __("File is too large (maximum is 2M bytes).")
				)
			);
		}

		$this->setWidgets($widgets);
		$this->widgetSchema->setNameFormat('data[%s]');
		$this->setValidators($validators);
	}
}