<?php 
class DateTimeUtils {
	/**
	 * Convertit un timestamp en DateTime.
	 * 
	 * @param DateTime $timestamp
	 */
	public static function fromTimetamp($timestamp)
	{
		$year = date("Y", $timestamp);
		$month = date("n", $timestamp);
		$day = date("j", $timestamp);
			
		$hour = date("H", $timestamp);
		$minute = date("i", $timestamp);
		
		$datetime = new DateTime();
		
		$datetime->setDate($year, $month, $day);
		$datetime->setTime($hour, $minute);
		
		return $datetime;
	}
	
	/**
	 * Formatage d'un date par defaut de wikipixel. 
	 *
	 * @param unknown $date
	 * @return Ambigous <NULL, string, string>
	 */
	public static function formatDate($date, $culture = null, $charset = null)
	{
		static $dateFormats = array();
		
		if (null === $date)
		{
			return null;
		}
		
		if (!$culture)
		{
			$culture = sfContext::getInstance()->getUser()->getCulture();
		}
		
		if (!$charset)
		{
			$charset = sfConfig::get('sf_charset');
		}
		
		if (!isset($dateFormats[$culture]))
		{
			$dateFormats[$culture] = new sfDateFormat($culture);
		}
		
		return $dateFormats[$culture]->format($date, 'd', null, $charset);
	}

	/**
	 * Formatage d'un date et du temps par defaut de wikipixel.
	 *
	 * @param unknown $date
	 * @return Ambigous <NULL, string, string>
	 */
	public static function formatDateTime($date, $culture = null, $charset = null)
	{
		static $dateFormats = array();
		
		if (null === $date)
		{
			return null;
		}
		
		if (!$culture)
		{
			$culture = sfContext::getInstance()->getUser()->getCulture();
		}
		
		if (!$charset)
		{
			$charset = sfConfig::get('sf_charset');
		}
		
		if (!isset($dateFormats[$culture]))
		{
			$dateFormats[$culture] = new sfDateFormat($culture);
		}
		
		$formater = $dateFormats[$culture];

		return sfContext::getInstance()->getI18N()->__("%date% at %time%", 
				array("%date%" => $formater->format($date, 'd'), "%time%" => $formater->format($date, 't')));
	}
}
?>
