<?php

function sureRemoveDir($dir)
{
	if(!$dh = @opendir($dir)) return;
	while (false !== ($obj = readdir($dh)))
	{
		if($obj=='.' || $obj=='..') continue;
		if (!@unlink($dir.'/'.$obj)) sureRemoveDir($dir.'/'.$obj);
	}

	closedir($dh);
	@rmdir($dir);
}

function getImageRights()
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	return array(
		"all" => __("ALL"),
		"public" => __("Public"),
		"creative_commons" => __("Creative commons"),
		"no_print" => __("No print"),
	);
}

function getLogTypes()
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	return array(
		"all" => __("ALL"),
		"comment-add" => __("Comments"),
		"user-" => __("Users"),
		"file-" => __("Files"),
		"folder-" => __("Folders"),
		"group-" => __("Groups"),
		"tag-" => __("Tags"),
		"invitation-" => __("Invitations"),
		"request-" => __("Requests"),
		"tag-" => __("Tags"),
		"permalink-" => __("Permalinks"),
		"profil-" => __("Profils"),
		"faq-" => __("Faqs"),
		"unit-" => __("Units"),
		"cart-" => __("Cart"),
	);
}


function returnLogContent($user_id, $object_id, $log_type, $ids=array())
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	if(!empty($user_id))
	{
		$user = UserPeer::retrieveByPK($user_id);
		$user = !$user->getFirstname() || !$user->getLastname() ? $user->getEmail() : $user->getFirstname()." ".$user->getLastname();
	}
	else
	{
		$user_id = 0;
		$user = __("Unknown user");
	}

	switch ($log_type)
	{
		# COMMENT
		case "comment-add": 
			$file = FilePeer::retrieveByPK($object_id);
			return __("%1% commented on the file %2%.", array("%1%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>", "%2%" => "<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>"));
		case "comment-update": 
			$file = FilePeer::retrieveByPK($object_id);
			return __("A comment on the file %1% updated by %2%.", array("%1%" => "<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "comment-delete":
			$file = FilePeer::retrieveByPK($object_id);
			return __("A comment deleted by %1% on the file %2%.",array("%1%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>", "%2%" => "<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>"));
		# PROFILE
		case "profile-update":
			return __("%1% updated his/her profile.", array("%1%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_blank'>".$user."</a></i></b>"));
		# FAQ
		case "faq-update":
			$faq = FaqPeer::retrieveByPK($object_id);
			return __("FAQ %1% updated by %2%.", array("%1%" => "<b><i><a href='/faq/show?id=".$faq->getId()."' target='_blank'>".$faq."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "faq-delete":
			$faq = FaqPeer::retrieveByPK($object_id);
			return __("FAQ %1% deleted by %2%.", array("%1%" => "<b><i><a href='/faq/show?id=".$faq->getId()."' target='_blank'>".$faq."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# FILE
		case "file-retouch":
			$file = FilePeer::retrieveByPK($object_id);
			return __("A file %1% retouched by %2%.", array("%1%" => "<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "file-edit":
			$file = FilePeer::retrieveByPK($object_id);
			return __("A file %1%'s informations updated by %2%.", array("%1%" => "<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "files-edit":
			$files = FilePeer::retrieveByPKs($ids);
			$files_array = array();

			foreach ($files as $file)
				$files_array[] = "<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>";

			return __("The files %1%'s informations updated by %2%.", array("%1%" => join(", ", $files_array), "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "file-download":
			$file = FilePeer::retrieveByPK($object_id);
			return __("A file %1% downloaded by %2%.", array("%1%" => "<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "files-download":
			$files = FilePeer::retrieveByPKs($ids);
			$files_array = array();

			foreach ($files as $file)
				$files_array[] = "<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>";

			return __("The files %1% downloaded by %2%.", array("%1%" => join(", ", $files_array), "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "file-delete":
			$file = FilePeer::retrieveByPK($object_id);
			return __("A file %1% deleted by %2%.", array("%1%" => "<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "file-print":
			$file = FilePeer::retrieveByPK($object_id);
			return __("A file %1% printed by %2%.", array("%1%" => "<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "file-email":
			$file = FilePeer::retrieveByPK($object_id);
			return __("A file %1% was sent by %2%.", array("%1%" =>"<b><i><a href='/file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()."' target='_blank'>".$file."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# FOLDER
		case "folder-create":
			$folder = FolderPeer::retrieveByPK($object_id);
			return __("A folder %1% created by %2%.", array("%1%" => "<b><i><a href='/folder/show?id=".$folder->getId()."' target='_blank'>".$folder."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "folder-update":
			$folder = FolderPeer::retrieveByPK($object_id);
			return __("A folder %1%'s information updated by %2%.", array("%1%" => "<b><i><a href='/folder/show?id=".$folder->getId()."' target='_blank'>".$folder."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "folder-delete":
			$folder = FolderPeer::retrieveByPK($object_id);
			return __("A folder %1% deleted by %2%.", array("%1%" => "<b><i><a href='/folder/show?id=".$folder->getId()."' target='_blank'>".$folder."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "folder-download":
			$folder = FolderPeer::retrieveByPK($object_id);
			return __("A folder %1% downloaded by %2%.", array("%1%" => "<b><i><a href='/folder/show?id=".$folder->getId()."' target='_blank'>".$folder."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "folder-move":
			$folder = FolderPeer::retrieveByPK($object_id);
			$group = $folder->getGroupe();
			return __("A folder %1% moved to the group %2% by %3%.", array("%1%" => "<b><i><a href='/folder/show?id=".$folder->getId()."' target='_blank'>".$folder."</a></i></b>", "%2%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%3%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# GROUP
		case "group-merge":
			$group = GroupePeer::retrieveByPK($object_id);
			return __("A group %1% merged to the group %2% by %3%.", array("%1%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%2%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%3%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "group-create":
			$group = GroupePeer::retrieveByPK($object_id);
			return __("A group %1% created by %2%.", array("%1%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "group-update":
			$group = GroupePeer::retrieveByPK($object_id);
			return __("A group %1%'s information updated by %2%.", array("%1%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "group-delete":
			$group = GroupePeer::retrieveByPK($object_id);
			return __("A group %1% deleted by %2%.", array("%1%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# INVITATION 
		case "invitation-send":
			$group = FolderPeer::retrieveByPK($object_id);
			$users = UserPeer::retrieveByPKs($ids);
			$users_array = array();

			foreach ($users as $user)
				$users_array[] = $user;

			return __("An invitations for the group %1% sent to users %2% by %3%.", array("%1%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%2%" => "<b><i>".join(", ", $users_array)."</i></b>", "%3%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "invitation-accept":
			$group = GroupePeer::retrieveByPK($object_id);
			return __("%1% accepted an invitation to the group %2%.", array("%1%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>", "%2%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>"));
		case "invitation-deny":
			$group = GroupePeer::retrieveByPK($object_id);
			return __("%1% denied an invitation to the group %2%.", array("%1%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>", "%2%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>"));
		# HOMEINFO
		case "info-edit":
			return __("Home page info updated by %1%.", array("%1%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# REQUEST
		case "request-group":
			$group = GroupePeer::retrieveByPK($object_id);
			return __("Request for access to the group %1% sent by %2%.", array("%1%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "request-folder":
			$folder = FolderPeer::retrieveByPK($object_id);
			return __("Request for access to the folder %1% sent by %2%.", array("%1%" => "<b><i><a href='/group/folder?id=".$folder->getId()."' target='_blank'>".$folder->getName()."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "request-contact":
			return __("Contact message sent by %1%.", array("%1%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "request-cancel-group":
			$group = GroupePeer::retrieveByPK($object_id);
			return __("The sent request for access to the group %1% canceled by %2%.", array("%1%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "request-accept-group":
			$group = GroupePeer::retrieveByPK($object_id);
			return __("The sent request for access to the group %1% accepted by %2%.", array("%1%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "request-deny-group":
			$group = GroupePeer::retrieveByPK($object_id);
			return __("The sent request for access to the group %1% denied by %2%.", array("%1%" => "<b><i><a href='/group/show?id=".$group->getId()."' target='_blank'>".$group."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "request-cancel-folder":
			$folder = FolderPeer::retrieveByPK($object_id);
			return __("The sent request for access to the folder %1% canceled by %2%.", array("%1%" => "<b><i><a href='/folder/show?id=".$folder->getId()."' target='_blank'>".$folder->getName()."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "request-accept-folder":
			$folder = FolderPeer::retrieveByPK($object_id);
			return __("The sent request for access to the folder %1% accepted by %2%.", array("%1%" => "<b><i><a href='/folder/show?id=".$folder->getId()."' target='_blank'>".$folder->getName()."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "request-deny-folder":
			$folder = FolderPeer::retrieveByPK($object_id);
			return __("The sent request for access to the folder %1% denied by %2%.", array("%1%" => "<b><i><a href='/folder/show?id=".$folder->getId()."' target='_blank'>".$folder->getName()."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# UPLOAD
		case "file-upload":
			$folder = FolderPeer::retrieveByPK($object_id);
			$files = FilePeer::retrieveByPKs($ids);
			$files_array = array();

			foreach ($files as $file)
				$files_array[] = "<b><i><a href='/file/show?id=".$file->getId()."&folder_id=".$folder->getId()."' target='_blank'>".$file."</a></i></b>";

			if(count($files_array) > 1)
				return __("The files %1% uploaded to the folder %2% by %3%.", array("%1%" => join(", ", $files_array), "%2%" => "<b><i><a href='/folder/show?id=".$folder->getId()."' target='_blank'>".$folder."</a></i></b>", "%3%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
			else
				return __("The file %1% uploaded to the folder %2% by %3%.", array("%1%" => join(", ", $files_array), "%2%" => "<b><i><a href='/folder/show?id=".$folder->getId()."' target='_blank'>".$folder."</a></i></b>", "%3%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# TAG
		case "tag-create":
			$tag = TagPeer::retrieveByPK($object_id);
			return __("A tag %1% created by %2%.", array("%1%" => "<b><i><a href='/tag/attach?id=".$object_id."' target='_black'>".$tag."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "tag-update":
			$tag = TagPeer::retrieveByPK($object_id);
			return __("A tag %1% updated by %2%.", array("%1%" => "<b><i><a href='/tag/attach?id=".$object_id."' target='_black'>".$tag."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "tag-delete":
			$tag = TagPeer::retrieveByPK($object_id);
			return __("A tag %1% deleted by %2%.", array("%1%" => "<b><i><a href='/tag/attach?id=".$object_id."' target='_black'>".$tag."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# USER
		case "user-create":
			$user1 = UserPeer::retrieveByPK($object_id);
			return __("A new user %1% added by %2%.", array("%1%" => "<b><i><a href='/user/edit?id=".$object_id."' target='_black'>".$user1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "user-delete":
			$user1 = UserPeer::retrieveByPK($object_id);
			return __("User %1% deleted by %2%.", array("%1%" => "<b><i><a href='/user/edit?id=".$object_id."' target='_black'>".$user1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "user-save":
			$user1 = UserPeer::retrieveByPK($object_id);
			return __("User %1% updated by %2%.", array("%1%" => "<b><i><a href='/user/edit?id=".$object_id."' target='_black'>".$user1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "user-suspend":
			$user1 = UserPeer::retrieveByPK($object_id);
			return __("User %1% suspended by %2%.", array("%1%" => "<b><i><a href='/user/edit?id=".$object_id."' target='_black'>".$user1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "user-enable":
			$user1 = UserPeer::retrieveByPK($object_id);
			return __("User %1% enabled by %2%.", array("%1%" => "<b><i><a href='/user/edit?id=".$object_id."' target='_black'>".$user1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# PERMALINK
		case "permalink-create":
			$permalink1 = PermalinkPeer::retrieveByPK($object_id);
			return __("A new permalink %1% added by %2%.", array("%1%" => "<b><i><a href='/permalink/edit?id=".$object_id."' target='_black'>".$permalink1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "permalink-delete":
			$permalink1 = PermalinkPeer::retrieveByPK($object_id);
			return __("Permalink %1% deleted by %2%.", array("%1%" => "<b><i><a href='/permalink/edit?id=".$object_id."' target='_black'>".$permalink1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "permalink-download":
			$permalink1 = PermalinkPeer::retrieveByPK($object_id);
			return __("Permalink %1% used by %2%.", array("%1%" => "<b><i><a href='/permalink/edit?id=".$object_id."' target='_black'>".$permalink1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# UNIT
		case "unit-create":
			$user1 = UnitPeer::retrieveByPK($object_id);
			return __("A new user's group %1% added by %2%.", array("%1%" => "<b><i><a href='/unit/edit?id=".$object_id."' target='_black'>".$user1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "unit-delete":
			$user1 = UnitPeer::retrieveByPK($object_id);
			return __("User's group %1% deleted by %2%.", array("%1%" => "<b><i><a href='/unit/edit?id=".$object_id."' target='_black'>".$user1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "unit-save":
			$user1 = UnitPeer::retrieveByPK($object_id);
			return __("User's group %1% updated by %2%.", array("%1%" => "<b><i><a href='/unit/edit?id=".$object_id."' target='_black'>".$user1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		# CART
		case "cart-save":
			$cart1 = BasketPeer::retrieveByPk($object_id);
			return __("A new cart %1% added by %2%.", array("%1%" => "<b><i><a href='/basket/show?id=".$object_id."' target='_blank'>".$cart1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "cart-add":
			$cart1 = BasketPeer::retrieveByPk($object_id);
			$files = FilePeer::retrieveByPKs($ids);
			$files_array = array();

			foreach ($files as $file)
				$files_array[] = "<b><i><a href='/file/show?id=".$file->getId()."&folder_id=".$file->getFolderId()."' target='_blank'>".$file."</a></i></b>";

			return __("A new file %1% added to cart %2% added by %3%.", array("%1%" => join(", ", $files_array), "%2%" => "<b><i><a href='/basket/show?id=".$object_id."' target='_blank'>".$cart1."</a></i></b>", "%3%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "cart-remove":
			$cart1 = BasketPeer::retrieveByPk($object_id);
			$files = FilePeer::retrieveByPKs($ids);
			$files_array = array();

			foreach ($files as $file)
				$files_array[] = "<b><i><a href='/file/show?id=".$file->getId()."&folder_id=".$file->getFolderId()."' target='_blank'>".$file."</a></i></b>";

			return __("A file %1% removed from cart %2% added by %3%.", array("%1%" => join(", ", $files_array), "%2%" => "<b><i><a href='/basket/show?id=".$object_id."' target='_blank'>".$cart1."</a></i></b>", "%3%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "cart-update":
			$cart1 = BasketPeer::retrieveByPk($object_id);
			return __("Cart %1% updated by %2%.", array("%1%" => "<b><i><a href='/basket/show?id=".$object_id."' target='_blank'>".$cart1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "cart-send":
			$cart1 = BasketPeer::retrieveByPk($object_id);
			return __("Cart %1% sent by %2%.", array("%1%" => "<b><i><a href='/basket/show?id=".$object_id."' target='_blank'>".$cart1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "cart-download":
			$cart1 = BasketPeer::retrieveByPk($object_id);
			return __("Cart %1% downloaded by %2%.", array("%1%" => "<b><i><a href='/basket/show?id=".$object_id."' target='_blank'>".$cart1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		case "cart-delete":
			$cart1 = BasketPeer::retrieveByPk($object_id);
			return __("Cart %1% deleted by %2%.", array("%1%" => "<b><i><a href='/basket/show?id=".$object_id."' target='_blank'>".$cart1."</a></i></b>", "%2%" => "<b><i><a href='/user/edit?id=".$user_id."' target='_black'>".$user."</a></i></b>"));
		default: break;
	}
}


function returnLogTypes($log_type)
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	switch ($log_type)
	{
		# COMMENT
		case "comment-add": return __("Add comment");
		case "comment-update": return __("Update comment");
		case "comment-delete": return __("Delete comment");
		# PROFILE
		case "profile-update": return __("Profile update");
		# FAQ
		case "faq-update": return __("Update FAQ");
		case "faq-delete": return __("Delete FAQ");
		# FILE
		case "file-retouch": return __("Retouch file");
		case "file-edit": return __("Update file information");
		case "files-edit": return __("Update files information");
		case "file-download": return __("Donwload file");
		case "files-download": return __("Donwload files");
		case "file-delete": return __("Delete file");
		case "file-print": return __("Print file");
		case "file-email": return __("Email file");
		# FOLDER
		case "folder-create": return __("New folder");
		case "folder-update": return __("Update folder information");
		case "folder-delete": return __("Delete folder");
		case "folder-move": return __("Move folder");
		case "folder-download": return __("Download folder");
		# GROUP
		case "group-merge": return __("Merge groups");
		case "group-create": return __("New group");
		case "group-update": return __("Update group informations");
		case "group-delete": return __("Delete group");
		# INVITATION 
		case "invitation-send": return __("Invite users to the group");
		case "invitation-accept": return __("Accept an invitation");
		case "invitation-deny": return __("Deny the invitation");
		# HOMEINFO
		case "info-edit": return __("Edit home info");
		# REQUEST
		case "request-group": return __("Request for access to the group");
		case "request-folder": return __("Request for access to the folder");
		case "request-contact": return __("Contact request");
		case "request-cancel-group": return __("Cancel group request");
		case "request-accept-group": return __("Accept group request");
		case "request-deny-group": return __("Deny group request");
		case "request-cancel-folder": return __("Cancel folder request");
		case "request-accept-folder": return __("Accept folder request");
		case "request-deny-folder": return __("Deny folder request");
		# UPLOAD
		case "file-upload": return __("File upload");
		# TAG
		case "tag-create": return __("New tag");
		case "tag-update": return __("Update tag");
		case "tag-delete": return __("Delete tag");
		# USER
		case "user-create": return __("New user");
		case "user-delete": return __("Delete user");
		case "user-save": return __("Update user");
		case "user-suspend": return __("Suspend user");
		case "user-enable": return __("Enable user");
		# PERMALINK
		case "permalink-create": return __("New permalink");
		case "permalink-delete": return __("Delete permalink");
		case "permalink-download": return __("Use permalink");
		# UNIT
		case "unit-create": return __("New unit");
		case "unit-delete": return __("Delete unit");
		case "unit-save": return __("Update unit");
		# CART
		case "cart-save": return __("New cart");
		case "cart-add": return __("Add file to cart");
		case "cart-remove": return __("Remove file from cart");
		case "cart-update": return __("Update cart");
		case "cart-send": return __("Send cart");
		case "cart-delete": return __("Delete cart");
		case "cart-download": return __("Download cart");
		default: break;
	}
}

function viewBy($selected, $url, $tail="")
{
	$url = url_for($url);
	$url.= (preg_match('/\?/', $url) ? '&' : '?').'view=';
  
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	echo '<ul>';
	echo '<li class="lbl">'.__("View").'</li>';
	echo '<li class="grid"><a href="'.$url.'grid'.$tail.'" style="'.($selected == "grid" ? "font-weight:bold;" : "").'"><span>Grid</span></a></li>';
	echo '<li class="gallery"><a href="'.$url.'gallery'.$tail.'" style="'.($selected == "gallery" ? "font-weight:bold;" : "").'"><span>Gallery</span></a></li>';
	echo '<li class="list"><a href="'.$url.'list'.$tail.'" style="'.($selected == "list" ? "font-weight:bold;" : "").'"><span>List</span></a></li>';
	echo '</ul>';
}

function sortBy($selected, $form_name, $form_action, $show_date=true, $show_email=false, $show_rate=false, $show_effective=false, $show_id=false, $show_title=false, $show_description=false, $show_first_name=false, $show_address=false, $show_city=false, $show_invitation=false, $show_name=true, $show_invited=false, $show_expiration=false, $show_price=false, $show_duration=false, $show_code=false, $show_type=false, $show_company=false, $show_path=false, $show_number=false, $show_customer=false, $show_total=false, $show_created_at=false, $show_file_name=false, $show_created_at_file=false, $show_size=false, $show_state=false, $show_hash=false)
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	echo '<select id="sort" name="sort" class="sort-select" onchange="document.'.$form_name.'.action=\''.$form_action.'\'; document.'.$form_name.'.submit(); ">';

	if($show_name)
	{
		echo '<option value="name_asc" '.($selected == 'name_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Name ascending").'</option>';
		echo '<option value="name_desc" '.($selected == 'name_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Name descending").'</option>';
	}

	if($show_date)
	{
		echo '<option value="date_asc" '.($selected == 'date_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Date ascending").'</option>';
		echo '<option value="date_desc" '.($selected == 'date_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Date descending").'</option>';    
	}

	if($show_email)
	{
		echo '<option value="email_asc" '.($selected == 'email_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Email ascending").'</option>';
		echo '<option value="email_desc" '.($selected == 'email_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Email descending").'</option>';
	}

	if($show_rate)
	{
		echo '<option value="rate_asc" '.($selected == 'rate_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Rate ascending").'</option>';
		echo '<option value="rate_desc" '.($selected == 'rate_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Rate descending").'</option>';
	}

	if($show_effective)
	{
		echo '<option value="effective_asc" '.($selected == 'effective_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Effective ascending").'</option>';
		echo '<option value="effective_desc" '.($selected == 'effective_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Effective descending").'</option>';
	}

	if($show_id)
	{
		echo '<option value="id_asc" '.($selected == 'id_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Id ascending").'</option>';
		echo '<option value="id_desc" '.($selected == 'id_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Id descending").'</option>';
	}

	if($show_title)
	{
		echo '<option value="title_asc" '.($selected == 'title_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Title ascending").'</option>';
		echo '<option value="title_desc" '.($selected == 'title_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Title descending").'</option>';
	}

	if($show_description)
	{
		echo '<option value="desc_asc" '.($selected == 'desc_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Description ascending").'</option>';
		echo '<option value="desc_desc" '.($selected == 'desc_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Description descending").'</option>';
	}

	if($show_first_name)
	{
		echo '<option value="first_name_asc" '.($selected == 'first_name_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("First name ascending").'</option>';
		echo '<option value="first_name_desc" '.($selected == 'first_name_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("First name descending").'</option>';
	}

	if($show_address)
	{
		echo '<option value="address_asc" '.($selected == 'address_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Address ascending").'</option>';
		echo '<option value="address_desc" '.($selected == 'address_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Address descending").'</option>';
	}

	if($show_city)
	{
		echo '<option value="city_asc" '.($selected == 'city_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("City ascending").'</option>';
		echo '<option value="city_desc" '.($selected == 'city_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("City descending").'</option>';
	}

	if($show_invitation)
	{
		echo '<option value="date_asc" '.($selected == 'date_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Date of invitation ascending").'</option>';
		echo '<option value="date_desc" '.($selected == 'date_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Date of invitation descending").'</option>';
	}

	if($show_invited)
	{
		echo '<option value="invited_asc" '.($selected == 'invited_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Inviting ascending").'</option>';
		echo '<option value="invited_desc" '.($selected == 'invited_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Inviting descending").'</option>';
	}

	if($show_expiration)
	{
		echo '<option value="expire_asc" '.($selected == 'expire_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Expiration ascending").'</option>';
		echo '<option value="expire_desc" '.($selected == 'expire_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Expiration descending").'</option>';
	}

	if($show_price)
	{
		echo '<option value="price_asc" '.($selected == 'price_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Price ascending").'</option>';
		echo '<option value="price_desc" '.($selected == 'price_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Price descending").'</option>';
	}

	if($show_duration)
	{
		echo '<option value="duration_asc" '.($selected == 'duration_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Duration ascending").'</option>';
		echo '<option value="duration_desc" '.($selected == 'duration_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Duration descending").'</option>';
	}

	if($show_code)
	{
		echo '<option value="code_asc" '.($selected == 'code_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Code ascending").'</option>';
		echo '<option value="code_desc" '.($selected == 'code_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Code descending").'</option>';
	}

	if($show_type)
	{
		echo '<option value="type_asc" '.($selected == 'type_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Type ascending").'</option>';
		echo '<option value="type_desc" '.($selected == 'type_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Type descending").'</option>';
	}

	if($show_company)
	{
		echo '<option value="company_asc" '.($selected == 'company_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Company ascending").'</option>';
		echo '<option value="company_desc" '.($selected == 'company_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Company descending").'</option>';
	}

	if($show_path)
	{
		echo '<option value="path_asc" '.($selected == 'path_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Path ascending").'</option>';
		echo '<option value="path_desc" '.($selected == 'path_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Path descending").'</option>';
	}

	if($show_number)
	{
		echo '<option value="id_asc" '.($selected == 'id_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Number ascending").'</option>';
		echo '<option value="id_desc" '.($selected == 'id_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Number descending").'</option>';
	}

	if($show_customer)
	{
		echo '<option value="customer_asc" '.($selected == 'customer_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Customer ascending").'</option>';
		echo '<option value="customer_desc" '.($selected == 'customer_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Customer descending").'</option>';
	}

	if($show_total)
	{
		echo '<option value="total_asc" '.($selected == 'total_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Total ascending").'</option>';
		echo '<option value="total_desc" '.($selected == 'total_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Total descending").'</option>';
	}

	if($show_created_at)
	{
		echo '<option value="created_at_asc" '.($selected == 'created_at_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Registration date ascending").'</option>';
		echo '<option value="created_at_desc" '.($selected == 'created_at_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Registration date descending").'</option>';
	}

	if($show_file_name)
	{
		echo '<option value="file_name_asc" '.($selected == 'file_name_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Filename ascending").'</option>';
		echo '<option value="file_name_desc" '.($selected == 'file_name_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Filename descending").'</option>';
	}

	if($show_created_at_file)
	{
		echo '<option value="creation_date_asc" '.($selected == 'creation_date_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Date of creation ascending").'</option>';
		echo '<option value="creation_date_desc" '.($selected == 'creation_date_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Date of creation descending").'</option>';
	}

	if($show_size)
	{
		echo '<option value="size_asc" '.($selected == 'size_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Size ascending").'</option>';
		echo '<option value="size_desc" '.($selected == 'size_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Size descending").'</option>';
	}

	if($show_state)
	{
		echo '<option value="sate_asc" '.($selected == 'sate_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("State ascending").'</option>';
		echo '<option value="sate_desc" '.($selected == 'sate_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("State descending").'</option>';
	}

	if($show_hash)
	{
		echo '<option value="hash_asc" '.($selected == 'hash_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Hash ascending").'</option>';
		echo '<option value="hash_desc" '.($selected == 'hash_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Hash descending").'</option>';
	}

	echo '</select>';
}

function itemBy($selected, $form_name, $form_action)
{
	echo '<select id="item" name="item" class="view-select-item" onchange="document.'.$form_name.'.action=\''.$form_action.'\'; document.'.$form_name.'.submit(); ">';
	echo '<option value="20" '.($selected == '20' ? 'style="font-weight:bold;" selected' : '').'>20</option>';
	echo '<option value="50" '.($selected == '50' ? 'style="font-weight:bold;" selected' : '').'>50</option>';
	echo '<option value="100" '.($selected == '100' ? 'style="font-weight:bold;" selected' : '').'>100</option>';
	echo '<option value="500" '.($selected == '500' ? 'style="font-weight:bold;" selected' : '').'>500</option>';
	echo '<option value="all" '.($selected == 'all' ? 'style="font-weight:bold;" selected' : '').'>'.__('All').'</option>';
	echo '</select>';
}

function sortByFavorites($selected)
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	echo '<select id="sortFavorites" name="sortFavorites" class="sort-select">';
	echo '<option value="name_asc" '.($selected == 'name_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Name ascending").'</option>';
	echo '<option value="name_desc" '.($selected == 'name_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Name descending").'</option>';
	echo '<option value="date_asc" '.($selected == 'date_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Date ascending").'</option>';
	echo '<option value="date_desc" '.($selected == 'date_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Date descending").'</option>';    
	echo '</select>';

	echo "
	<script type=\"text/javascript\">
	jQuery(document).ready(function() {
		jQuery('#sortFavorites').bind('change', function() {
			redirectToUrl('/favorite/list?object_type=' + jQuery('#object_type').val() + '&sort=', jQuery('#sortFavorites').val(), '#favorites');
		});
	});
	</script>";
}

function sortByGarbage($selected)
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	echo '<select id="sortGarbage" name="sortGarbage" class="sort-select">';
	echo '<option value="name_asc" '.($selected == 'name_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Name ascending").'</option>';
	echo '<option value="name_desc" '.($selected == 'name_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Name descending").'</option>';
	echo '<option value="date_asc" '.($selected == 'date_asc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Date ascending").'</option>';
	echo '<option value="date_desc" '.($selected == 'date_desc' ? 'style="font-weight:bold;" selected' : '').'>'.__("Date descending").'</option>';    
	echo '</select>';

	echo "
	<script type=\"text/javascript\">
	jQuery(document).ready(function() {
		jQuery('#sortGarbage').bind('change', function() {
			redirectToUrl('/basket/garbage?sort=', jQuery('#sortGarbage').val(), '#garbages');
		});
	});
	</script>";
}

function getUserRoles()
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	return array(
		"administrator" => __("Administrator"),
		"contributor"   => __("Contributor"),
		"reader"        => __("Reader")
	);
}

function getFieldTypes()
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	return array(
		"Text field" => __("Text field"),
		"Date field" => __("Date field"),
		"Multiple choice" => __("Multiple choice"),
		"Boolean field" => __("Boolean field")
	);
}


function getGroupActions()
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	return array(
		"merge"  => __("Remove the group and re-assign the contents to an another group."),
		"delete" => __("Completely delete the group with its content."),
	);
}


function pager_navigation($pager, $uri, $method = 'get', $anchor="")
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	if($method == 'post')
		return pager_navigation_post($pager, $uri);

	$navigation = '<div class="pagination"><div class="pagination_inner">';

	if ($pager->haveToPaginate())
	{
		$uri .= (preg_match('/\?/', $uri) ? '&' : '?').'page=';

		// First and previous page
		if ($pager->getPage() != 1)
		{
			$navigation .= link_to('<em><<</em>', $uri.'1'.$anchor, array());
			$navigation .= link_to('<em><</em>', $uri.$pager->getPreviousPage().$anchor, array());
		}

		// Pages one by one
		$links = array();
		foreach ($pager->getLinks() as $page)
			$links[] = link_to('<em>'.$page.'</em>', $uri.$page.$anchor, array('class' => ($page == $pager->getPage()?"selected":""), ));

		$navigation .= join('', $links);

		// Next and last page
		if ($pager->getPage() != $pager->getCurrentMaxLink())
		{
			$navigation .= link_to('<em>></em>', $uri.$pager->getNextPage().$anchor, array());
			$navigation .= link_to('<em>>></em>', $uri.$pager->getLastPage().$anchor, array());
		}
	}

	if ($pager->getNbResults() > 0)
		$navigation .= '<span>Total <b>'.$pager->getNbResults().'</b> '.($pager->getNbResults() > 1 ? __('results.').' ' : __('result.').' ').$pager->getFirstIndice().'-'.$pager->getLastIndice().' '.__('in this page.').'</span>';

	$navigation .= '</div></div>';

	return $navigation;
}

function pager_navigation_home($pager, $uri, $method = 'get', $anchor="")
{
	if ($method == 'post')
		return pager_navigation_post($pager, $uri);

	$navigation = '<div class="pagination"><div class="pagination_inner">';

	if ($pager->haveToPaginate())
	{
		$uri .= (preg_match('/\?/', $uri) ? '&' : '?').'page=';

		// First and previous page
		if ($pager->getPage() != 1)
		{
			$navigation .= link_to('<em><<</em>', $uri.'1'.$anchor, array());
			$navigation .= link_to('<em><</em>', $uri.$pager->getPreviousPage().$anchor, array());
		}

		// Pages one by one
		$links = array();
		foreach ($pager->getLinks() as $page)
			$links[] = link_to('<em>'.$page.'</em>', $uri.$page.$anchor, array('class' => ($page == $pager->getPage()?"selected":""), ));

		$navigation .= join('', $links);

		// Next and last page
		if ($pager->getPage() != $pager->getCurrentMaxLink())
		{
			$navigation .= link_to('<em>></em>', $uri.$pager->getNextPage().$anchor, array());
			$navigation .= link_to('<em>>></em>', $uri.$pager->getLastPage().$anchor, array());
		}
	}

	$navigation .= '</div></div>';

	return $navigation;
}

function showLocationName($lat, $lng)
{
	$country_name = "";
	$name = "";

	$xml_response = @file_get_contents("https://maps.googleapis.com/maps/api/geocode/xml?latlng=".$lat.",".$lng."&sensor=false");

	if ($xml_response === false) 
		return __("Unknown location");
	else
	{
		$xml = new SimpleXMLElement($xml_response);
		$result = $xml->xpath('/GeocodeResponse/result/address_component[2]/long_name');

		if(array_key_exists(0, $result))
			$name = (string) $result[0];

		$result = $xml->xpath('/GeocodeResponse/result[2]/address_component[2]/long_name');

		if(array_key_exists(0, $result))
			$country_name = (string) $result[0];
 
		if($country_name && $name)
			return $name." / ".$country_name;
		elseif($country_name)
			return $country_name;
		elseif($name)
			return $name;
	}

	return __("Unknown location");
}

function prepareSidebar($groups=array(), $folders=array(), $files=array())
{
	$group_options = array();
	$types = array();
	$usage_rights = array();
	$sizes = array();
	$dates = array();
	$years = FilePeer::getYears();

	foreach ($folders as $folder)
		$group_options[$folder->getGroupeId()] = $folder->getGroupe();

	foreach ($files as $file)
	{
		$group_options[$file->getGroupeId()] = $file->getGroupe();
		$types[$file->getExtention()] = strtoupper($file->getExtention());
	}  
  
	$group_options = array_unique($group_options);
	$types = array_unique($types);
	$usage_rights = array_unique($usage_rights);  

	if(sizeof($files))
		$sizes = FilePeer::getSizes();

	if(sizeof($files))
		$dates = FilePeer::getShootingDate();
  
	$sidebar_array = array(
		"group_ids" => getIds($groups), 
		"folder_ids" => getIds($folders), 
		"file_ids" => getIds($files),
		"group_options" => $group_options,
		"types" => $types,
		"usage_rights" => $usage_rights,
		"years" => $years,
		"sizes" => $sizes,
		"dates" => $dates
	);

	return $sidebar_array;
}

function getIds($objects)
{
	$ids  = array();

	foreach ($objects as $object)
		$ids[] = $object->getId();

	return $ids;
}

function replaceAccentedCharacters($str)
{
	$table = array(
		'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
		'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
		'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
		'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
		'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
		'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
		'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
		'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r'
		);

	$str = strtr($str, $table);

	return $str;
}

function pager_navigation_search($page, $maxPage, $uri)
{
	if($maxPage > 1)
	{
		if($maxPage > 15)
			$temp = 15;
		else
			$temp = $maxPage;

		if(($page%15 == 0) && $maxPage > 15)
		{
			$i = $page;
			$temp = (($i / 15) + 1) * 15;
		}
		else
			$i = 1;

		$navigation = '<div class="pagination">
						<div class="pagination_inner">';

		$uri .= (preg_match('/\?/', $uri) ? '&' : '?').'page=';

		if($page != 1)
		{
			$navigation .= '<a href="'.$uri.'1"><em><<</em></a>';
			$navigation .= '<a href="'.$uri.($page - 1).'"><em><</em></a>';
		}

		for($k = $i; $k <= $temp; $k++)
		{
			if($k == $page)
				$navigation .= '<a href="'.$uri.$k.'" class="selected"><em>'.$k."</em></a>";
			else
				$navigation .= '<a href="'.$uri.$k.'"><em>'.$k."</em></a>";
		}

		if($page != $maxPage)
		{
			$navigation .= '<a href="'.$uri.($page + 1).'"><em>></em></a>';
			$navigation .= '<a href="'.$uri.$maxPage.'"><em>>></em></a>';
		}

		$navigation .= '</div>
					</div>';

		return $navigation;
	}
}

function image_thumbnail($file, $width = null, $height = null) 
{
	sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
	
	$path = "@file_thumbnail?id=".$file->getId();
			
	if ($width) {
		$path .= "&width=".$width;
	}
	
	if ($height) {
		$path .= "&height=".$height;
	}
	
	$path .= "&mtime=".$file->getUpdatedAt("U");
	
	return url_for($path);
}

/**
 * Prints <link> tags for all stylesheets configured in view.yml or added to the response object.
 *
 * @see get_stylesheets()
 */
function assetic_include_stylesheets()
{
	$response = sfContext::getInstance()->getResponse();
	$config = sfConfig::get("app_assetic_css");
	$files = $config["files"];

	$targetPath = sfConfig::get("sf_web_dir")."/css/".$config["path"];
	$active = sfConfig::get("app_assetic_active");
	
	if (!$targetPath) {
		$active = false;
	}
	
	// supprime les fichiers qui sont incluse aussi dans le view
	foreach ($files as $file) {
		$response->removeStylesheet($file);
	}
	
	// prod
	if ($active) {
		$updatedAt = filemtime($targetPath);
				
		// inclusion
		echo stylesheet_tag($config["path"]."?v=".$updatedAt);
	}
	else {
		// affichage des scripts non concaténés
		$html = "";
		
		foreach ($files as $style) {
			$html .= stylesheet_tag($style);
		}
		
		echo $html;
	}
	
	echo get_stylesheets();
}

/**
 *
 * @see get_javascripts()
 */
function assetic_include_javascripts()
{
	$response = sfContext::getInstance()->getResponse();
	$config = sfConfig::get("app_assetic_js");
	$files = $config["files"];

	$targetPath = sfConfig::get("sf_web_dir")."/js/".$config["path"];
	$active = sfConfig::get("app_assetic_active");

	if (!$targetPath) {
		$active = false;
	}

	// supprime les fichiers qui sont incluse aussi dans le view
	foreach ($files as $file) {
		$response->removeJavascript($file);
	}

	// prod
	if ($active) {
		$updatedAt = filemtime($targetPath);
		
		// inclusion
		echo javascript_include_tag($config["path"]."?v=".$updatedAt);
	}
	else {
		// affichage des scripts non concaténés
		$html = "";
		
		foreach ($files as $style) {
			$html .= javascript_include_tag($style);
		}
		
		echo $html;
	}
	
	echo get_javascripts();
}

function format_price($price, $currency = null) {
	echo myTools::format_price($price, $currency);
}

function format_pricing(Pricing $pricing, $currency = null) {
	echo myTools::format_pricing($pricing, $currency);
}

/**
 * Gérère une url relative.
 * (même signature que symfony2)
 *
 * @param string $key 		Le nom de la route (ex: @homepage)
 * @param array  $params	Liste des paramètres
 */
function path($key, array $params = array())
{
	return generate_url($key, $params, false);
}

/**
 * Gérère une url absolute.
 * (même signature que symfony2)
 *
 * @param string $key 		Le nom de la route (ex: @homepage)
 * @param array  $params	Liste des paramètres
 */
function url($key, array $params = array()) {
	return generate_url($key, $params, true);
}

/**
 * Renvoie les paramètres de la query string de la requête courante.
 * 
 * @param boolean $removeEmpty Si vrai, supprime les paramètres vides.
 * 
 * @return Array(parameter's name, parameter's value)
 */
function query_params($removeEmpty = false) {
	$params = array();
	parse_str($_SERVER['QUERY_STRING'], $params);

	if ($removeEmpty) {
		foreach ($params as $key => $param) {
			if (empty($param)) {
				unset($params[$key]);
			}
		}
	}

	return $params;
}

/**
 * Renvoie tous les paramètres de la requête courante.
 * Inclut tous les paramètres de la query string + les paramètres inclus dans l'url
 * pour les urls de type rest ex: /user/:id/files
 * 
 * @param bool $removeEmpty Si vrai, supprime les paramètres vides.
 * @return Array(parameter's name, parameter's value)
 */
function request_params($removeEmpty = false) {
	$params = sfContext::getInstance()->getRequest()->getParameterHolder()->getAll();
	
	unset($params["module"]);
	unset($params["action"]);
	
	if ($removeEmpty) {
		foreach ($params as $key => $param) {
			if (empty($param)) {
				unset($params[$key]);
			}
		}
	}
	
	return $params;
}

/**
 * Génère une url.
 * 
 * @param string $key 		Le nom de la route (ex: @homepage)
 * @param array  $params	Liste des paramètres
 * @param bool   $absolute
 */
function generate_url($key, array $params = array(), $absolute = false) {
	if (substr($key, 0, 1) == "@") {
		$key = substr($key, 1);
	}
	
	$routing = sfContext::getInstance()->getRouting();
	return $routing->generate($key, $params, $absolute);
}

/**
 * Fusionne les paramètres avec la requête courante.
 * (prend uniquement les paramètres de la query string)
 * 
 * @param array $eraseParams
 * @param array $removeParams
 * @return array
 */
function merge_query_params($eraseParams = array(), $removeParams = array()) {
	$params = query_params(true);
	
	if ($eraseParams) {
		foreach ($eraseParams as $key => $param) {
			$params[$key] = $eraseParams[$key];
		}
	}
	
	foreach ($removeParams as $key) {
		if (isset($params[$key])) {
			unset($params[$key]);
		}
	}

	return $params;
}

/**
 * Fusionne les paramètres avec la requête courante.
 *
 * @param array $eraseParams
 * @param array $removeParams
 * @return array
 */
function merge_request_params($eraseParams = array(), $removeParams = array()) {
	$params = request_params(true);

	if ($eraseParams) {
		foreach ($eraseParams as $key => $param) {
			$params[$key] = $eraseParams[$key];
		}
	}

	foreach ($removeParams as $key) {
		if (isset($params[$key])) {
			unset($params[$key]);
		}
	}

	return $params;
}
/**
 * Convertit les paramètres en input hidden.
 */
function params_to_input_hidden(array $params) {
	foreach ($params as $name => $value) {
		if (is_array($value)) {
			foreach ($value as $pname => $pvalue) {
				?>
				<input type="hidden" name="<?php echo $name."[".(is_int($pname) ? "" : $pname)."]"?>" value="<?php echo $pvalue?>">
				<?php
			}
		}
		else {
			?>
			<input type="hidden" name="<?php echo $name?>" value="<?php echo $value?>">
			<?php
		}
	}	
}

function pagination($pager, $routingKey, array $params = null) {
	if (!$params) {
		$params = request_params();
	}

	if (!$pager->haveToPaginate()) {
		return;
	}

	$links = $pager->getLinks(5)->getRawValue();

	/*
	echo "count:".$pager->count()."<br>";
	echo "first indice: ".$pager->getFirstIndice()."<br>";
	echo "last indice: ".$pager->getLastIndice()."<br>";
	echo "getCurrentMaxLink: ".$pager->getCurrentMaxLink()."<br>";
	echo "getFirstPage: ".$pager->getFirstPage()."<br>";
	echo "getLastPage: ".$pager->getLastPage()."<br>";
	*/
	?>
		<div class="pagination pagination-centered">
			<ul class="links">
				<!-- first page -->
				<?php if (!in_array($pager->getFirstPage(), $links)):?>
					<?php $params["page"] = $pager->getFirstPage();?>
					<li><a title="<?php echo __("Page %x", array("%x" => $pager->getFirstPage()))?>" href="<?php echo generate_url($routingKey, $params);?>">«</a></li>
				<?php endif?>

				<!-- previous page -->
				<?php if ($pager->getPage() != 1):?>
					<?php $params["page"] = $pager->getPage() - 1;?>
					<li><a title="<?php echo __("Page %x", array("%x" => $params["page"]))?>" href="<?php echo generate_url($routingKey, $params);?>"><?php echo __("Previous")?></a></li>
				<?php endif;?>

				<!-- current pages -->
				<?php foreach ($links as $page):?>
					<?php $params["page"] = $page;?>
					<li class="<?php echo ($page == $pager->getPage()) ? "active":"";?>"><a href="<?php echo generate_url($routingKey, $params);?>"><?php echo $page?></a></li>
				<?php endforeach;?>

				<!-- next page -->
				<?php if ($pager->getPage() != $pager->getLastPage()):?>
					<?php $params["page"] = $pager->getPage() + 1;?>
					<li><a title="<?php echo __("Page %x", array("%x" => $params["page"]))?>" href="<?php echo generate_url($routingKey, $params);?>"><?php echo __("Next")?></a></li>
				<?php endif;?>
			
				<!-- last page -->
				<?php if (!in_array($pager->getLastPage(), $links)):?>
					<?php $params["page"] = $pager->getLastPage();?>
					<li><a title="<?php echo __("Page %x", array("%x" => $params["page"]))?>" href="<?php echo generate_url($routingKey, $params);?>">»</a></li>
				<?php endif;?>
			</ul>

			<div class="infos">
				<?php echo __("%firstIndice% - %lastIndice% of %count% items", 
						array("%firstIndice%" => $pager->getFirstIndice(), "%lastIndice%" => $pager->getLastIndice(), "%count%" => $pager->count()));?>
			</div>
		</div>
		<?php
	}

function getNextPagination($pager, $routingKey, array $params = null)
{
	if (!$params) {
		$params = request_params();
	}

	if (!$pager->haveToPaginate()) {
		return;
	}

	if ($pager->getPage() != $pager->getLastPage()) {
		$params["page"] = $pager->getPage() + 1;

		return generate_url($routingKey, $params);
	}

	return;
}

function getUsStates($format_for_select = false)
{
	$states = Array();
	$states[] = 'Alabama';
	$states[] = 'Alaska';
	$states[] = 'Arizona';
	$states[] = 'Arkansas';
	$states[] = 'California';
	$states[] = 'Colorado';
	$states[] = 'Connecticut';
	$states[] = 'Delaware';
	$states[] = 'Florida';
	$states[] = 'Georgia';
	$states[] = 'Hawaii';
	$states[] = 'Idaho';
	$states[] = 'Illinois';
	$states[] = 'Indiana';
	$states[] = 'Iowa';
	$states[] = 'Kansas';
	$states[] = 'Kentucky';
	$states[] = 'Louisiana';
	$states[] = 'Maine';
	$states[] = 'Maryland';
	$states[] = 'Massachusetts';
	$states[] = 'Michigan';
	$states[] = 'Minnesota';
	$states[] = 'Mississippi';
	$states[] = 'Missouri';
	$states[] = 'Montana';
	$states[] = 'Nebraska';
	$states[] = 'Nevada';
	$states[] = 'New Hampshire';
	$states[] = 'New Jersey';
	$states[] = 'New Mexico';
	$states[] = 'New York';
	$states[] = 'North Carolina';
	$states[] = 'North Dakota';
	$states[] = 'Ohio';
	$states[] = 'Oklahoma';
	$states[] = 'Oregon';
	$states[] = 'Pennsylvania';
	$states[] = 'Rhode Island';
	$states[] = 'South Carolina';
	$states[] = 'South Dakota';
	$states[] = 'Tennessee';
	$states[] = 'Texas';
	$states[] = 'Utah';
	$states[] = 'Vermont';
	$states[] = 'Virginia';
	$states[] = 'Washington';
	$states[] = 'West Virginia';
	$states[] = 'Wisconsin';
	$states[] = 'Wyoming';

	return $states;
}

function draw_breadcrumb(array $nodes) {
	$count = count($nodes);
	?>
	<ul class="breadcrumb">
		<?php for ($i = 0; $i < $count; $i++):?>
			<?php 
			$node = $nodes[$i];
			$active = isset($node["active"]) ? (bool) $node["active"] : false;
			
			// mais en actif le dernier node par défaut
			if (!isset($node["active"]) && $count-1 == $i) {
				$active = true;
			}
			?>
			<li class="<?php if ($active) echo "active";?>">
				<a href="<?php echo $node["link"];?>"><?php echo $node["text"];?></a>
				
				<?php if ($i < $count -1):?>
					<span class="divider">/</span>
				<?php endif;?>
			</li>
		<?php endfor;?>
	</ul>
<?php
}

/**
 * Remplace include_javascripts en intégrant le paramètre mtime (la date de dernière modification)
 *
 * @see get_stylesheets()
 */
function include_javascripts_2()
{
	$response = sfContext::getInstance()->getResponse();
	sfConfig::set('symfony.asset.javascripts_included', true);

	$html = '';
	
	$jsPath = sfConfig::get("sf_web_dir")."/js";
	
	foreach ($response->getJavascripts() as $file => $options) {
		$absolutePathname = $jsPath.DIRECTORY_SEPARATOR.$file;
		
		if (!file_exists($absolutePathname)) {
			continue;
		}

		$mtime = filemtime($absolutePathname);

		$file .= "?mtime=".$mtime;
		$html .= javascript_include_tag($file, $options);
	}

	echo $html;
}

function draw_timezone_html($currentZone = null) 
{
	if ($currentZone === null) {
		$request = sfContext::getInstance()->getRequest();
		$suser = sfContext::getInstance()->getUser();
		
		$currentZone = $request->getParameter("zone", $suser->getZone());
	}
?>
	<form class="form-inline">
		<?php params_to_input_hidden(merge_query_params(null, array("zone", "page")));?>
		
		<label><?php echo __("Zone")?></label>
		<select name="zone">
			<option value=""><?php echo __("All")?></option>
			<option value="1" <?php if ($currentZone == "1") echo "selected"?>>USA</option>
			<option value="2" <?php if ($currentZone == "2") echo "selected"?>><?php echo __("other");?></option>
		</select>
					
		<button class="btn">OK</button>
	</form>
<?php
}


/**
 * Returns the title of the current page according to the response attributes,
 * to be included in the <title> section of a HTML document.
 *
 * <b>Note:</b> Modify the sfResponse object or the view.yml to modify the title of a page.
 *
 * @return string page title
 */
function my_include_title($defaultTitle = "")
{
	$title = sfContext::getInstance()->getResponse()->getTitle();
	
	if (!$title) {
		$title = $defaultTitle;
	}
	
	echo content_tag('title', $title)."\n";
}

function my_format_date($date)
{
	return DateTimeUtils::formatDate($date);
}

function my_format_date_time($date)
{
	return DateTimeUtils::formatDateTime($date);
}

/**
 * Inclut tous les stylesheets comme la fonction "include_stylesheets()" de syfony mais en media print.
 */
function include_stylesheets_media_print()
{
	$response = sfContext::getInstance()->getResponse();
	sfConfig::set('symfony.asset.stylesheets_included', true);
	
	$html = '';
	foreach ($response->getStylesheets() as $file => $options)
	{
		$options["media"] = "print";
		$html .= stylesheet_tag($file, $options);
	}
	
	echo $html;
}
