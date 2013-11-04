<ul>
  <?php foreach ($users as $user):?>
    <li id="<?php echo $user->getId()?>"><?php echo $user?></li>
  <?php endforeach;?>
</ul>