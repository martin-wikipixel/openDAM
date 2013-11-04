<?php 

class FormUtils
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * Permet de construire un select depuis un tableau de model.
	 * 
	 * @param array 	$objects 		Les entités issues du model.
	 * @param string 	$methodName		Le nom de la méthode (ex: getName)
	 * @param boolean 	$addEmtySelect  Ajoute un champ vide "Select" (default = true)
	 * @param string 	$addEmtyText	Texte de l'option empty (default = "Select")
	 * 
	 * @return Array<id, name>
	 */
	public static function buildSelect($objects, $methodName, $addEmtySelect = true, $addEmtyText = null)
	{
		$rows = array();

		if ($addEmtySelect) {
			if ($addEmtyText === null) {
				$addEmtyText = __("Select");
			}
			
			$rows[""] = $addEmtyText;
		}
		
		foreach ($objects as $object) {
			$rows[$object->getId()] = $object->$methodName();
		}
		
		return $rows;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Extaits tous les ids (méthode getId()) d'une liste d'instance ici d'un model.
	 * 
	 * @param array $objects
	 * @return multitype:NULL
	 */
	public static function getIds(array $objects)
	{
		$ids = array();
		
		foreach ($objects as $object) {
			$ids[] = $object->getId();
		}
		
		return $ids;
	}
}