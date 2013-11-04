<?php
class ChangelogUpdateForm extends BaseForm
{
	public function configure()
	{
		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'version' => new sfWidgetFormInputText(
					array(),
					array(
						"style" => "width:250px; float:left;"
					)
				),
				'changes_en' => new sfWidgetFormTextarea(
					array(),
					array(
						"style" => "width:250px; height: 100px; float:left;"
					)
				),
				'changes_fr' => new sfWidgetFormTextarea(
					array(),
					array(
						"style" => "width:250px; height: 100px; float:left;"
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
				'version' => new sfValidatorString(
					array(
						'required' => true
					),
					array(
						'required' => __("Version is required.")
					)
				),
				'changes_en' => new sfValidatorString(
					array(
						'required' => false
					)
				),
				'changes_fr' => new sfValidatorString(
					array(
						'required' => false
					)
				),
			)
		);

		$this->validatorSchema->setPostValidator(
			new sfValidatorCallback(
				array(
					"callback" => array($this, "checkVersion")
				)
			)
		);
	}

	public function checkVersion($validator, $values)
	{
		$version = $values["version"];
		$id = $values["id"];

		$c = new Criteria();
		$c->addJoin(ChangelogPeer::ID, ChangelogI18nPeer::ID);
		$c->add(ChangelogI18nPeer::TITLE, $version);

		if(!empty($id))
			$c->add(ChangelogPeer::ID, $id, Criteria::NOT_EQUAL);

		if(ChangelogPeer::doCount($c) > 0)
			throw new sfValidatorError($validator, __("Version already exists."));

		return $values;
	}
}