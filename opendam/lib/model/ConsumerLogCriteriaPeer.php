<?php

/**
 * Subclass for performing query and update operations on the 'consumer_log_criteria' table.
 *
 * 
 *
 * @package lib.model
 */
class ConsumerLogCriteriaPeer extends BaseConsumerLogCriteriaPeer 
{
	/*________________________________________________________________________________________________________________*/
	public static function deleteThisMonth($s, $e)
	{
		$c = new Criteria();

		$crit0 = $c->getNewCriterion(self::CREATED_AT, $s, Criteria::GREATER_THAN);
		$crit1 = $c->getNewCriterion(self::CREATED_AT, $e, Criteria::LESS_THAN);

		$crit0->addAnd($crit1);
		$c->add($crit0);

		self::doDelete($c);
	}
}
