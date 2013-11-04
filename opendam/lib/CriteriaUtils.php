<?php 
class CriteriaUtils
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * Construct la partie orderBy d'un criteria.
	 * Supporte les 2 syntaxes suivante : 
	 *    - orderBy[0] = "name_asc" -
	 *    - orderBy["name"] = "asc"
	 * 
	 * @param Criteria $critetia
	 * @param array $orderBy
	 */
	public static function buildOrderBy(Criteria $criteria, array $orderBy)
	{
		foreach ($orderBy as $orderColumn => $direction) {
			if (is_int($orderColumn)) {
				$lastUnderscore = strrpos($direction, "_");
				
				Assert::ok($lastUnderscore > 0);
				
				$orderColumn = substr($direction, 0, $lastUnderscore);
				$direction = substr($direction, $lastUnderscore+1);
			}

			$direction = strtolower($direction);

			if ($direction == "desc") {
				$criteria->addDescendingOrderByColumn($orderColumn);
			}
			else {
				$criteria->addAscendingOrderByColumn($orderColumn);
			}
		}
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Remplace les paramètres généré par BasePeer::createSelectSql 
	 * par la valeurs correspondante via une requete sql générée avec BasePeer::createSelectSql.
	 * 
	 * ex: .. WHERE groupe.STATE=:p1 AND groupe.CUSTOMER_ID=:p2 AND groupe.FREE=:p3
	 * 
	 * replaceParameters($sql,Array ( 
	 * 	 [0] => Array ( [table] => groupe [column] => STATE [value] => 1 )
	 * 	 [1] => Array ( [table] => groupe [column] => CUSTOMER_ID [value] => 15 ) 
	 *   [2] => Array ( [table] => groupe [column] => FREE [value] => 1 ) ) ))
	 * -> WHERE groupe.STATE=1 AND groupe.CUSTOMER_ID=1 AND groupe.FREE=true
	 * 
	 */
	public static function replaceParameters($sql, $sqlParams) {
		foreach ($sqlParams as $index => $param) {
			$key = ":p".++$index;
			$value = $param["value"];
			
			if (!is_scalar($value)) {
				throw new Exception(sprintf("value \"%s\" should be an scalar", $value));
			}

			if (is_string($value)) {
				$value = Propel::getConnection()->quote($value);
			}
			else if(is_bool($value)) {
				$value = (($value === true) ? 1 : 0); 
			}

			$sql = str_replace($key, $value, $sql);
		}

		return $sql;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function buidSqlFromCriteria(Criteria $criteria)
	{
		$sqlParams =  array();
		$sql = BasePeer::createSelectSql($criteria, $sqlParams);
		return CriteriaUtils::replaceParameters($sql, $sqlParams);
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function setSelectColumn(Criteria $criteria, $column)
	{
		$criteria->clearSelectColumns();
		$criteria->addSelectColumn($column);
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function setZone(Criteria $criteria, $field, $zone)
	{
		if ($zone == 1) {
			$criteria->add($field, array(66), Criteria::IN);//usa
		}
		else {
			$criteria->add($field, array(66), Criteria::NOT_IN);//other
		}
	}
}
?>