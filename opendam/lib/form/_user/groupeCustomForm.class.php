<?php
class GroupeCustomForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
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
				)
			)
		);

		$this->validatorSchema->setPostValidator(new sfValidatorCallback(array("callback" => array($this, "checkCname"))));
	}

	public function checkCname($validator, $values)
	{
		$id = $values["id"];
		$cname = $values["cname"];

		if(!empty($cname))
		{
			if($group = GroupePeer::retrieveByUrl($cname))
			{
				if($group->getId() != $id)
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
			elseif(myTools::existsCname($cname))
				throw new sfValidatorError($validator, __("This access URL is not available."));
		}

		return $values;
	}
}