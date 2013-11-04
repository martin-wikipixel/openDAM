<?php
class GroupStep2Form extends BaseForm
{
	public function configure()
	{
		$group = GroupePeer::retrieveByPk($this->getDefault("id"));

		if($group->getFree())
		{
			switch($group->getFreeCredential())
			{
				case RolePeer::__CONTRIB:
					$widget = array(__("Select right"), RolePeer::__ADMIN =>__("Administration"), RolePeer::__CONTRIB => __("Writing"));
					$validator = array(RolePeer::__ADMIN, RolePeer::__CONTRIB);
				break;

				case RolePeer::__READER:
					$widget = array(__("Select right"), RolePeer::__ADMIN =>__("Administration"), RolePeer::__READER => __("Reading"));
					$validator = array(RolePeer::__ADMIN, RolePeer::__READER);
				break;
			}
		}
		else
		{
			$widget = array(__("Select right"), RolePeer::__ADMIN =>__("Administration"), RolePeer::__CONTRIB => __("Writing"), RolePeer::__READER => __("Reading"));
			$validator = array(RolePeer::__ADMIN, RolePeer::__CONTRIB, RolePeer::__READER);
		}

		$this->setWidgets(
			array(
				'id' => new sfWidgetFormInputHidden(
					array(),
					array()
				),
				'users' => new sfWidgetFormInputFile(
					array(),
					array(
						"style" => "float:left;"
					)
				),
				'invite_right_users' => new sfWidgetFormChoice(
					array(
						'choices'  => $widget
					),
					array(
						"style" => "width: 200px; float: left; margin-left: 5px;"
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
					),
					array()
				),
				'users' => new sfValidatorFile(
					array(
						'required' => true,
						'path' => sfConfig::get("app_path_temp_dir")
					),
					array(
						'required' => __("Please select CSV file.")
					)
				),
				'invite_right_users' => new sfValidatorChoice(
					array(
						'choices' => $validator
					),
					array(
						'invalid' => __("Select user role."),
						'required' => __("Select user role.")
					)
				)
			)
		);
	}
}