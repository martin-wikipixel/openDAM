<?php
class GroupThumbnailForm extends BaseForm
{
	public function configure()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'step' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'thumbnail' => new sfWidgetFormInputFile(
					array(),
					array(
						"size" => "50",
						"style" => "float: left;"
					)
				),
				'uploaded_thumbnail_name' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'is_upload' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'x1' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'x2' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'y1' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'y2' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'w' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'h' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'width' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'height' => new sfWidgetFormInputHidden(
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
				'step' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'thumbnail' => new sfValidatorFile(
					array(
						'required' => false,
						'mime_types' => array("image/jpeg", "image/gif", "image/png")
					),
					array(
						'mime_types' => __("Only PNG, GIF and JPEG images are allowed.")
					)
				),
				'uploaded_thumbnail_name' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'is_upload' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'x1' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'x2' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'y1' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'y2' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'w' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'h' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'width' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'height' => new sfValidatorString(
					array(
						'required' => false
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkThumbnail")
				)
			)
		);
	}

	public function checkThumbnail($validator, $values)
	{
		$thumbnail = $values["thumbnail"];

		if(!empty($thumbnail))
		{
			$size = getimagesize($thumbnail->getTempName());
			
			if($size[0] < 220 || $size[1] < 100)
				throw new sfValidatorError($validator, __("Image size must be at least 220x100 pixels."));
		}

		return $values;
	}
}