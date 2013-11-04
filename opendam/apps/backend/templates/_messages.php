<?php $form = get_slot('form'); ?>
<?php if (!empty($form) && $form->hasErrors()): ?>
  <div class="error box">
    <?php foreach($form->getErrors() as $name => $error): ?>
    &nbsp; - <?php echo __($error) ?><br />
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php if ($sf_user->hasFlash('success')): ?>
  <div class="success box"><?php echo __($sf_data->getRaw('sf_user')->getFlash('success'))?></div>  
<?php endif; ?>

<?php if ($sf_user->hasFlash('info')): ?>
  <div class="info box"><?php echo __($sf_data->getRaw('sf_user')->getFlash('info'))?></div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('warning')): ?>
  <div class="warning box"><?php echo __($sf_data->getRaw('sf_user')->getFlash('warning'))?></div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('error')): ?>
  <div class="error alert box"><?php echo __($sf_data->getRaw('sf_user')->getFlash('error'))?></div>
<?php endif; ?>