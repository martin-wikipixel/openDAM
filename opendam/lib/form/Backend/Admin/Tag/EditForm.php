<?php
class Backend_Admin_Tag_EditForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				"name" => new sfWidgetFormInputText(
					array(),
					array()
				),
			)
		);

		$this->widgetSchema->setNameFormat("data[%s]");

		$this->setValidators(
			array(
					"name" => new sfValidatorAnd(
							array(
									new sfValidatorString(
											array("required" => true)
									),
									new sfValidatorCallback(
											array("callback" => array($this, "checkNotExists"))
									)
							)
					),
			)
		);
	}

	/*________________________________________________________________________________________*/
	public function checkNotExists($validator, $name)
	{
		$id = $this->getOption("id");
		Assert::ok($id > 0);
		
		$tag = TagPeer::retrieveByTitle($name);
		
		if ($tag && $tag->getId() != $id) {
			throw new sfValidatorError($validator, __("The tag is already exists. Please enter another name."));
		}

		return $name;
	}
}