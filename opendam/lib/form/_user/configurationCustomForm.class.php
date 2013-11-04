<?php
class ConfigurationCustomForm extends BaseForm
{
	public function configure()
	{
		$arrayOrientation = Array(
			"NorthWest" => __("Upper left corner"),
			"NorthEast" => __("Upper right corner"),
			"SouthWest" => __("Lower left corner"),
			"SouthEast" => __("Lower right corner"),
			"Center" => __("Center"),
		);

		$this->setWidgets(
			array(
				'cname' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:150px; float:left;"
					)
				),
				'header_img' => new sfWidgetFormInputFile(
					array(),
					array(
						"size" => "50",
						"style" => "float: left;"
					)
				),
				'uploaded_header_name' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'favicon_img' => new sfWidgetFormInputFile(
					array(),
					array(
						"size" => "50",
						"style" => "float: left;"
					)
				),
				'uploaded_favicon_name' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'email_img' => new sfWidgetFormInputFile(
					array(),
					array(
						"size" => "50",
						"style" => "float: left;"
					)
				),
				'uploaded_email_name' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'watermark_img' => new sfWidgetFormInputFile(
					array(),
					array(
						"size" => "50",
						"style" => "float: left;"
					)
				),
				'uploaded_watermark_name' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				/*'bgcolor' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),*/
				'sender' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'watermark_orientation' => new sfWidgetFormChoice(
					array(
						'choices' => $arrayOrientation
					),
					array(
						"style" => "float:left; width:256px;"
					)
				)
			)
		);

		$this->widgetSchema->setNameFormat('data[%s]');

		$this->setValidators(
			array(
				'cname' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'header_img' => new sfValidatorFile(
					array(
						'required' => false,
						'mime_types' => array("image/jpeg", "image/gif", "image/png")
					),
					array(
						'mime_types' => __("Only PNG, GIF and JPEG images are allowed."),
						'max_size' => __("File is too large (maximum is 2M bytes).")
					)
				),
				'uploaded_header_name' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'favicon_img' => new sfValidatorFile(
					array(
						'required' => false,
						'mime_types' => array("image/jpeg", "image/gif", "image/png")
					),
					array(
						'mime_types' => __("Only PNG, GIF and JPEG images are allowed."),
						'max_size' => __("File is too large (maximum is 2M bytes).")
					)
				),
				'uploaded_favicon_name' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'email_img' => new sfValidatorFile(
					array(
						'required' => false,
						'mime_types' => array("image/jpeg", "image/gif", "image/png")
					),
					array(
						'mime_types' => __("Only PNG, GIF and JPEG images are allowed.")
					)
				),
				'uploaded_email_name' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'watermark_img' => new sfValidatorFile(
					array(
						'required' => false,
						'mime_types' => array("image/png")
					),
					array(
						'mime_types' => __("Only PNG images are allowed.")
					)
				),
				'uploaded_watermark_name' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				/*'bgcolor' => new sfValidatorString(
					array(
						'required' => false
					)
				),*/
				'sender' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'watermark_orientation' => new sfValidatorChoice(
					array(
						'choices' => array_keys($arrayOrientation)
					),
					array(
						'invalid' => __("Please select orientation."),
						'required' => __("Please select orientation.")
					)
				)
			)
		);

		$this->validatorSchema->setPostValidator(new sfValidatorCallback(array("callback" => array($this, "checkCname"))));
	}

	public function checkCname($validator, $values)
	{
		$cname = $values["cname"];

		if(!empty($cname))
		{
			if($template = TemplatePeer::retrieveByCname($cname))
			{
				if($template->getCustomerId() != sfContext::getInstance()->getUser()->getCustomerId())
					throw new sfValidatorError($validator, __("This access URL is not available."));
				else
				{
					$new_cname = myTools::cleanCname($cname);

					if($new_cname != $cname)
						throw new sfValidatorError($validator, __("This access URL contains invalid characters."));
					elseif(filter_var('http://'.$cname, FILTER_VALIDATE_URL) === false)
						throw new sfValidatorError($validator, __("This access URL is not valid."));
					elseif(!myTools::allowedCname($cname.".".sfConfig::get('app_hostnames_domain')))
						throw new sfValidatorError($validator, __("This access URL is not valid."));
				}
			}
		}

		return $values;
	}
}