<?php
class TagUpdateForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'title' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
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
				'title' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Tag name is required.")
					)
				),
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkTitle")
				)
			)
		);
	}

	public function checkTitle($validator, $values)
	{
		$title = $values["title"];
		$id = $values["id"];

		if($tag = TagPeer::retrieveByTitle($title)) {
			if(empty($id) || $id != $tag->getId())
				throw new sfValidatorError($validator, __("The tag is already exists. Please enter another name."));
		}

		return $values;
	}
}