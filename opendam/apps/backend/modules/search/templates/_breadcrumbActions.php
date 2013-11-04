<li><?php echo __("Results:"); ?> <?php echo $countOthers; ?> <i class="icon-folder-close"></i> <?php echo $countFiles; ?> <i class="icon-picture"></i></li>
<li class="container-dropdown">
	<a href="javascript: void(0);" class="dropdown-toggle"><i class="icon-wrench"></i> <?php echo __("Display options"); ?> <i class="icon-caret-down"></i></a>
	<ul class="dropdown-menu search-display" role="menu">
		<li><a href="javascript:void(0);" data-sort="name_asc"><i class="icon-sort-by-alphabet"></i> <?php echo __("Sort by name ascending"); ?></a></li>
		<li><a href="javascript:void(0);" data-sort="name_desc"><i class="icon-sort-by-alphabet-alt"></i> <?php echo __("Sort by name descending"); ?></a></li>
		<li><a href="javascript:void(0);" data-sort="date_asc"><i class="icon-sort-by-order"></i> <?php echo __("Sort by date ascending"); ?></a></li>
		<li><a href="javascript:void(0);" data-sort="date_desc"><i class="icon-sort-by-order-alt"></i> <?php echo __("Sort by date descending"); ?></a></li>
	</ul>
</li>