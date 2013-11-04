<?php 
	$size_user = $size_user->getRawValue();
	$folder_user = $folder_user->getRawValue();
	$file_user = $file_user->getRawValue();
	$upload_user = $upload_user->getRawValue();
	$download_user = $download_user->getRawValue();
?>
<?php echo __("Email"); ?>;<?php echo __("Used disk space (KB)"); ?>;<?php echo __("Number of folders"); ?>;<?php echo __("Number of files"); ?>;<?php echo __("Average size file (KB)"); ?>;<?php echo __("Average size folder (KB)"); ?>;<?php echo __("Average number of files by folder"); ?>;<?php echo __("Upload traffic (KB)"); ?>;<?php echo __("Download traffic (KB)"); ?>;<?php echo __("Last login")."\n"; ?>
<?php foreach($users as $user) : ?>
<?php $total_size = array_key_exists($user->getId(), $size_user) ? $size_user[$user->getId()]["total"] : ""; ?>
<?php $total_folders = array_key_exists($user->getId(), $folder_user) ? $folder_user[$user->getId()]["total"] : ""; ?>
<?php $total_picture = array_key_exists($user->getId(), $file_user) ? $file_user[$user->getId()]["total"] : ""; ?>
<?php $upload_traffic = array_key_exists($user->getId(), $upload_user) ? $upload_user[$user->getId()] : Array("total" => "", "nb" => ""); ?>
<?php $download_traffic = array_key_exists($user->getId(), $download_user) ? $download_user[$user->getId()] : Array("total" => "", "nb" => ""); ?>
<?php echo $user->getEmail(); ?>;<?php echo empty($total_size) ? "-" : str_replace(".", ",", MyTools::getSize($total_size, "kb", true)); ?>;<?php echo empty($total_folders) ? "-" : $total_folders; ?>;<?php echo empty($total_picture) ? "-" : $total_picture; ?>;<?php echo empty($total_size) || empty($total_picture) ? "-" :  str_replace(".", ",", MyTools::getSize(round($total_size / $total_picture, 2), "kb", true)); ?>;<?php echo empty($total_size) || empty($total_folders) ? "-" :  str_replace(".", ",", MyTools::getSize(round($total_size / $total_folders, 2), "kb", true)); ?>;<?php echo empty($total_picture) || empty($total_folders) ? "-" :  str_replace(".", ",", round($total_picture / $total_folders, 2)); ?>;<?php echo empty($upload_traffic["total"]) ? "-" : str_replace(".", ",", MyTools::getSize($upload_traffic["total"], "kb", true)); ?>;<?php echo empty($download_traffic["total"]) ? "-" : str_replace(".", ",", MyTools::getSize($download_traffic["total"], "kb", true)); ?>;<?php echo !$user->getLastLoginAt() ? "-\n" : $user->getLastLoginAt("d/m/Y H:i:s")."\n"; ?>
<?php endforeach; ?>
