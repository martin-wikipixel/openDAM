<?php


/**
 * Skeleton subclass for representing a row from the 'constraint_i18n' table.
 *
 * 
 *
 * This class was autogenerated by Propel 1.4.2 on:
 *
 * Wed Nov 16 10:48:28 2011
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    lib.model
 */
class ConstraintI18n extends BaseConstraintI18n 
{
	protected $groupe_id = null;

	/*________________________________________________________________________________________________________________*/
	public function setGroupeId($groupe_id)
	{
		$this->groupe_id = $groupe_id;
	}

	/*________________________________________________________________________________________________________________*/
	public function getGroupeId()
	{
		return $this->groupe_id;
	}
}