<?php 
class RightUtils
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * 
	 */
	public static function getObjectForAlbumAndUser(Groupe $album, User $user)
	{
		$userAlbum = UserGroupPeer::retrieveByGroupIdAndUserId($album->getId(), $user->getId());

		if ($userAlbum) {
			return $userAlbum;
		}

		$groupAlbum = UnitGroupPeer::retrieveMinRoleByGroupIdAndUserId($album->getId(), $user->getId());

		if ($groupAlbum) {
			return $groupAlbum;
		}

		return $album;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie true si l'utilisateur peut mettre à jour un album (update).
	 * 
	 * Un utilisateur peut modifier un album s'il est propriétaire, ou manager de cette album.
	 * 
	 * @return boolean
	 */
	public static function canUpdateAlbum(Groupe $album)
	{
		$user = sfContext::getInstance()->getUser()->getInstance();

		/* if ($album->getUserId() == $user->getId()) {
			return true;
		} */

		if ($user->getRole() == RolePeer::__ADMIN && $album->getCustomerId() == $user->getCustomerId()) {
			return true;
		}

		$xHasRight = self::getObjectForAlbumAndUser($album, $user);

		if ($xHasRight) {
			return $xHasRight->getCredential()->getId() == RolePeer::__ADMIN;
		}

		return false;
	}


	/*________________________________________________________________________________________________________________*/
	public static function getAccessForFolderAndUser(Folder $folder, User $user)
	{
		$object = null;
		$inherit = null;
	
		if ($folder->getFree()) {
			$access = true;
		}
		else {
			$access = false;
		}
	
		/* On vérifie s'il existe une exception pour l'utilisateur */
		$userFolder = UserFolderPeer::retrieveByUserAndFolder($user->getId(), $folder->getId());
	
		/* En cas d'exception */
		if ($userFolder) {
			$access = !$access;
			$object = $userFolder;
		}
	
		/* Récupération des groupes de l'utilisateur */
		$userGroups = UserUnitPeer::retrieveByUser($user->getId());
	
		/* Récupération des groupes autorisés à accéder au dossier et qui sont liés à l'utilisateur */
		$userFolderGroups = UnitFolderPeer::retrieveByUserIdAndFolderId($user->getId(), $folder->getId());
	
		/* Si le dossier est ouvert à tout le monde et que tous les groupes auxquels appartient l'utilisateur sont
		 * liés au dossier OU
		* Si le dossier est fermé à tout le monde et que l'utilisateur fait parti d'au moins un groupe qui a les accès.
		* */
		if (($folder->getFree() && count($userFolderGroups) == count($userGroups) && $userFolderGroups) ||
		(!$folder->getFree() && $userFolderGroups)) {
			if (!$object) {
				// $access = !$folder->getFree();
				$access = true;
				$object = current($userFolderGroups)->getUnit();
			}
			else {
				$inherit = current($userFolderGroups)->getUnit();
			}
		}
		else {
			if (!$object) {
				if (count($userGroups) > 0) {
					$countAccess = 0;
					$countNoAccess = 0;
	
					foreach ($userGroups as $userGroup) {
						$userUnitFolder = UnitFolderPeer::retrieveByUnitIdAndFolderId($userGroup->getId(), $folder->getId());
	
						if ($userUnitFolder) {
							if ($userUnitFolder->getRole()) {
								$countAccess++;
							}
							else {
								$countNoAccess++;
							}
						}
					}
	
					if (!$countAccess) {
						$access = false;
					}
				}
			}
		}
	
		/* Récupération de l'album lié au dossier */
		$album = $folder->getGroupe();
	
		/* Si le dossier se trouve à la racine de l'univers */
		if (!$folder->getSubfolderId()) {
			/* Si l'album est ouvert à tout le monde */
			if ($album->getFree()) {
				if (!$object) {
					$object = $folder;
				}
			}
			else {
				/* On vérifie que l'utilisateur a bien accès à l'album */
				$userAlbum = UserGroupPeer::retrieveByGroupIdAndUserId($album->getId(), $user->getId());
	
				if ($userAlbum) {
					if (!$object) {
						$object = $folder;
					}
				}
				else {
					/* Récupération des groupes qui sont liés à l'utilisateur et à l'album */
					$userAlbumGroups = UnitGroupPeer::retrieveByAlbumIdAndUserId($album->getId(), $user->getId());
	
					/* Si au moins un groupe est lié à l'utilisateur*/
					if ($userAlbumGroups) {
						if (!$object) {
							$object = current($userAlbumGroups)->getUnit();
						}
						else {
							$inherit = current($userAlbumGroups)->getUnit();
						}
					}
					else {
						if (!$object) {
							$access = false;
							$object = null;
						}
					}
				}
			}
		}
		else {
			$isUserException = $object instanceof UserFolder;
	
			/* En cas d'exception sur l'utilisateur */
			if (!$isUserException) {
			/* Si le dossier est fermé et que l'utilisateur n'entre pas dans un cas spécifique */
				if(!$folder->getFree() && $access == false) {
					$object = $folder;
				}
				else {
					/* Vérification récursive pour vérifier que l'utilisateur a bien un droit d'accès */
					$recursiveAccess = self::getRecursiveAccessForFolderAndUser($folder, $user);

					$access = $recursiveAccess["access"];
					$object = $recursiveAccess["object"];
				}
			}
		}

		return array(
			"access"	=> $access,
			"object"	=> $object,
			"inherit"	=> $inherit
		);
	}

	protected static function getRecursiveAccessForFolderAndUser(Folder $folder, User $user, &$firstObject = null)
	{
		if ($folder->getSubfolderId()) {
			$count = 0;
			$object = null;
			$currentFolder = FolderPeer::retrieveByPK($folder->getSubfolderId());

			$userFolder = UserFolderPeer::retrieveByUserAndFolder($user->getId(), $currentFolder->getId());

			if (($currentFolder->getFree() && $userFolder) || (!$currentFolder->getFree() && !$userFolder)) {
				$count++;

				$object = $currentFolder;
			}

			$unitFolder = UnitFolderPeer::retrieveByUserIdAndFolderId($user->getId(), $currentFolder->getId());

			if (!$unitFolder) {
				$count++;
			}
			else {
				$object = current($unitFolder)->getUnit();
			}

			if (!$firstObject) {
				$firstObject = $object;
			}

			// echo " °° Folder: ".$currentFolder->getId()."<br />";
			// echo " °° User: ".$user->getEmail()."<br />";
			// echo " °° LastObject: ".print_r($firstObject, true)."<br />";
			// echo " °° Count: ".$count."<br /><br />";

			if ($count == 2) {
				return array(
					"access"	=> false,
					"object"	=> $firstObject
				);
			}

			self::getRecursiveAccessForFolderAndUser($currentFolder, $user, $firstObject);
		}

		return array(
			"access"	=> true,
			"object"	=> $firstObject
		);
	}
}
?>