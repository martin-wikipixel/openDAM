<?php
$stars = array('0' => 'no', '1' => 'one', '2' => 'two', '3' => 'three', '4' => 'four', '5' => 'five');
$star = 0;
$is_rated = false;

if ($rate = RatingPeer::getFileRate($file_id))
{
  if($rate->getNbRate() > 0){
    $star = round($rate->getTotalRate() / $rate->getNbRate());
  }
  
  $user_ids = explode(", ", $rate->getUserIds());
  if(in_array($sf_user->getId(), $user_ids)) $is_rated = true;
}
?>

<ul class="rating <?php echo $stars[$star]?>star" id="rating">
  <?php if (!$is_rated):?>
    <li class="one"><a href="#" title="1 Star" onclick="doRate(<?php echo $file_id?>,1);return false;">1</a></li>
    <li class="two"><a href="#" title="2 Stars" onclick="doRate(<?php echo $file_id?>,2);return false;">2</a></li>
    <li class="three"><a href="#" title="3 Stars" onclick="doRate(<?php echo $file_id?>,3); return false;">3</a></li>
    <li class="four"><a href="#" title="4 Stars" onclick="doRate(<?php echo $file_id?>,4); return false;">4</a></li>
    <li class="five"><a href="#" title="5 Stars" onclick="doRate(<?php echo $file_id?>,5); return false;">5</a></li>
  <?php endif;?>
</ul>

<script type="text/javascript">

function doCancel(){
  alert(jQuery("ul.rating li.three a").css("background"));
  alert(jQuery("ul.rating li.three a").css("background-position"));
  //jQuery("ul.rating li.three a").css("background-position", "0 -147px;");
}

</script>