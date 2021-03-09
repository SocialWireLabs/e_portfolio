<div class="contentWrapper">

<?php

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$e_portfolio_group_setup = $vars['entity'];
$container_guid = $vars['container_guid'];
$container = get_entity($container_guid);

$pages_qualified = false;
if ($e_portfolio_group_setup) {
   $options = array('types' => 'object','subtypes' => 'e_portfolio','container_guid' => $e_portfolio_group_setup->container_guid, 'limit'=>false);
   $e_portfolios = elgg_get_entities_from_metadata($options);
   foreach ($e_portfolios as $one_e_portfolio){
      $options = array('types' => 'object','subtypes' => 'e_portfolio_page','container_guid' => $one_e_portfolio->getGUID(),'limit'=>false);
      $e_portfolio_pages = elgg_get_entities_from_metadata($options);
      foreach ($e_portfolio_pages as $page){
         $page_owner = $page->getOwnerEntity();
         $rubric_rate = socialwire_rubric_get_rating($page_owner->getGUID(),$page->getGUID());
         if ((strcmp($page->rating,"not_qualified")!=0)||($rubric_rate)) {
	    $pages_qualified = true;
	    break;
	 }
      }
   }
} 

if ($pages_qualified) {
   $disabled = "disabled";
} else {
   $disabled = "";
}

$action = "e_portfolio/setup_group";

if (isset($vars['entity'])) {
   if (!elgg_is_sticky_form('setup_group_e_portfolio')) {
      $var_pages = $vars['entity']->var_pages;
      $rating_type = $vars['entity']->rating_type;
      $use_rubric = $vars['entity']->use_rubric;
      if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
         $type_mark = $vars['entity']->type_mark;
         if (strcmp($type_mark,'e_portfolio_type_mark_numerical')==0){
            $max_mark = $vars['entity']->max_mark;
         }
	 $mark_weight = $vars['entity']->mark_weight;
	 if (!$var_pages)
	    $mark_weight = explode(Chr(26),$mark_weight);
	 $public_global_marks = $vars['entity']->public_global_marks;
      } else {
         if ($use_rubric) { 
            $max_game_points = $vars['entity']->max_game_points;
	    if (!$var_pages)
	       $max_game_points = explode(Chr(26),$max_game_points);
         }
      }
      if (!$var_pages)
         $num_pages = $vars['entity']->num_pages;
      if ($use_rubric) { 
         $rubric_guid = $vars['entity']->rubric_guid;
	 if (!$var_pages)
	    $rubric_guid = explode(Chr(26),$rubric_guid);
      }
   } else {
      if (!$pages_qualified) {
         $var_pages = elgg_get_sticky_value('setup_group_e_portfolio','var_pages');
         if (strcmp($var_pages,'on')==0) {
            $var_pages = true;
         } else {
            $var_pages = false;
         }
         $rating_type = elgg_get_sticky_value('setup_group_e_portfolio','rating_type');
         $use_rubric = elgg_get_sticky_value('setup_group_e_portfolio','use_rubric');
         if (strcmp($use_rubric,'on')==0) {
            $use_rubric = true;
         } else {
            $user_rubric = false;
         }
         if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){ 
            $type_mark = elgg_get_sticky_value('setup_group_e_portfolio','type_mark');
            if (strcmp($type_mark,'e_portfolio_type_mark_numerical')==0){
               $max_mark = elgg_get_sticky_value('setup_group_e_portfolio','max_mark');
            }
	    $public_global_marks = elgg_get_sticky_value('setup_group_e_portfolio','public_global_marks');
	 }
      } else {
         $var_pages = $vars['entity']->var_pages;
         $rating_type = $vars['entity']->rating_type;
         $use_rubric = $vars['entity']->use_rubric;
         if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
            $type_mark = $vars['entity']->type_mark;
            if (strcmp($type_mark,'e_portfolio_type_mark_numerical')==0){
               $max_mark = $vars['entity']->max_mark;
            }
	    $public_global_marks = $vars['entity']->public_global_marks;
	 }
      }
      if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){ 
	 if ($var_pages)  
	    $mark_weight = elgg_get_sticky_value('setup_group_e_portfolio','mark_weight');
	 else
	    $mark_weight = elgg_get_sticky_value('setup_group_e_portfolio','mark_weight_array');
      } else {
         if ($use_rubric) { 
	    if ($var_pages) 
               $max_game_points = elgg_get_sticky_value('setup_group_e_portfolio','max_game_points');
	    else
	       $max_game_points = elgg_get_sticky_value('setup_group_e_portfolio','max_game_points_array');
	 }
      }
      if (!$var_pages)        
         $num_pages = elgg_get_sticky_value('setup_group_e_portfolio','num_pages');
      if ($use_rubric) {
         if ($var_pages) 
            $rubric_guid = elgg_get_sticky_value('setup_group_e_portfolio','rubric_guid');
	 else
	  $rubric_guid = elgg_get_sticky_value('setup_group_e_portfolio','rubric_guid_array');
      }
   }
} else {
   if (!elgg_is_sticky_form('setup_group_e_portfolio')) {
      $var_pages = false;
      $rating_type = "e_portfolio_rating_type_marks";
      $use_rubric = false;
      $type_mark = 'e_portfolio_type_mark_numerical';
      $public_global_marks = false;
      $max_mark = '10';
      $max_game_points = array();
      $mark_weight = array();
      $rubric_guid = array();
      $num_pages = "";
   } else {
      $var_pages = elgg_get_sticky_value('setup_group_e_portfolio','var_pages');
      if (strcmp($var_pages,'on')==0) {
         $var_pages = true;
      } else {
         $var_pages = false;
      }
      $rating_type = elgg_get_sticky_value('setup_group_e_portfolio','rating_type');
      $use_rubric = elgg_get_sticky_value('setup_group_e_portfolio','use_rubric');
      if (strcmp($use_rubric,'on')==0) {
         $use_rubric = true;
      } else {
         $user_rubric = false;
      }
      if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){ 
         $type_mark = elgg_get_sticky_value('setup_group_e_portfolio','type_mark');
         if (strcmp($type_mark,'e_portfolio_type_mark_numerical')==0){
            $max_mark = elgg_get_sticky_value('setup_group_e_portfolio','max_mark');
         }
	 if ($var_pages) 
            $mark_weight = elgg_get_sticky_value('setup_group_e_portfolio','mark_weight');
	 else
	    $mark_weight = elgg_get_sticky_value('setup_group_e_portfolio','mark_weight_array');
	 $public_global_marks = elgg_get_sticky_value('setup_group_e_portfolio','public_global_marks');
      } else {
         if ($use_rubric) {
	    if ($var_pages) 
               $max_game_points = elgg_get_sticky_value('setup_group_e_portfolio','max_game_points');
	    else
	       $max_game_points = elgg_get_sticky_value('setup_group_e_portfolio','max_game_points_array'); 
	 }
      }
      if (!$var_pages)        
         $num_pages = elgg_get_sticky_value('setup_group_e_portfolio','num_pages');
      if ($use_rubric) {
         if ($var_pages)  
            $rubric_guid = elgg_get_sticky_value('setup_group_e_portfolio','rubric_guid');
	 else
	   $rubric_guid = elgg_get_sticky_value('setup_group_e_portfolio','rubric_guid_array'); 
      } 
   }
}

elgg_clear_sticky_form('setup_group_e_portfolio');

$rating_type_label=elgg_echo('e_portfolio:rating_type_label');
$options_type=array();
$options_rating_type[0]=elgg_echo('e_portfolio:rating_type_marks');
$options_rating_type[1]=elgg_echo('e_portfolio:rating_type_game_points');
$op_rating_type=array();
$op_rating_type[0]='e_portfolio_rating_type_marks';
$op_rating_type[1]='e_portfolio_rating_type_game_points';
if (strcmp($rating_type,$op_rating_type[0])==0){
   $checked_radio_rating_type_0 = "checked = \"checked\"";
   $checked_radio_rating_type_1 = "";
   $style_display_rating_type = "display:block";
   $style_display_rating_type_2 = "display:none";
} else {
   $checked_radio_rating_type_0 = "";
   $checked_radio_rating_type_1 = "checked = \"checked\"";
   $style_display_rating_type = "display:none";
   $style_display_rating_type_2 = "display:block";
}

$max_mark_label=elgg_echo('e_portfolio:max_mark_label');
$max_mark_array = array('10' => '10', '100' => '100');
$options_max_mark=array();
$options_max_mark[0]=elgg_echo('10');
$options_max_mark[1]=elgg_echo('100');
$op_max_mark=array();
$op_max_mark[0]='10';
$op_max_mark[1]='100';
if (strcmp($max_mark,$op_max_mark[0])==0){
   $checked_radio_max_mark_0 = "checked = \"checked\"";
   $checked_radio_max_mark_1 = "";
}
if (strcmp($max_mark,$op_max_mark[1])==0){
   $checked_radio_max_mark_0 = "";
   $checked_radio_max_mark_1 = "checked = \"checked\"";
}

$type_mark_label=elgg_echo('e_portfolio:type_mark_label');
$options_type_mark=array();
$options_type_mark[0]=elgg_echo('e_portfolio:type_mark_numerical');
$options_type_mark[1]=elgg_echo('e_portfolio:type_mark_textual');
$options_type_mark[2]=elgg_echo('e_portfolio:type_mark_apto');
$op_type_mark=array();
$op_type_mark[0]='e_portfolio_type_mark_numerical';
$op_type_mark[1]='e_portfolio_type_mark_textual';
$op_type_mark[2]='e_portfolio_type_mark_apto';
if (strcmp($type_mark,$op_type_mark[0])==0){
   $checked_radio_type_mark_0 = "checked = \"checked\"";
   $checked_radio_type_mark_1 = "";
   $checked_radio_type_mark_2 = "";
   $style_display_type_mark = "display:block";
}
if (strcmp($type_mark,$op_type_mark[1])==0){
   $checked_radio_type_mark_0 = "";
   $checked_radio_type_mark_1 = "checked = \"checked\"";
   $checked_radio_type_mark_2 = "";
   $style_display_type_mark = "display:none";
}
if (strcmp($type_mark,$op_type_mark[2])==0){
   $checked_radio_type_mark_0 = "";
   $checked_radio_type_mark_1 = "";
   $checked_radio_type_mark_2 = "checked = \"checked\"";
   $style_display_type_mark = "display:none";
}

$mark_weight_label=elgg_echo('e_portfolio:mark_weight_label');
$mark_weights_label=elgg_echo('e_portfolio:mark_weights_label');

$max_game_points_label=elgg_echo('e_portfolio:max_game_points_label');

$var_pages_label = elgg_echo('e_portfolio:var_pages_label');
if ($var_pages) {
   $selected_var_pages = "checked = \"checked\"";
   $style_display_var_pages = "display:none";
   $style_display_var_pages_2 = "display:block";
   $style_display_var_pages_3 = "display:none";
   $style_display_var_pages_4 = "display:block";
   $style_display_var_pages_5 = "display:none";
   $style_display_var_pages_6 = "display:block";
   $style_display_var_pages_7 = "display:none";
} else {
   $selected_var_pages = "";
   $style_display_var_pages = "display:block";
   $style_display_var_pages_2 = "display:none";
   $style_display_var_pages_3 = "display:block";
   $style_display_var_pages_4 = "display:none";
   $style_display_var_pages_5 = "display:block";
   $style_display_var_pages_6 = "display:none";
   $style_display_var_pages_7 = "display:block";
}

$num_pages_label = elgg_echo('e_portfolio:num_pages_label');

$public_global_marks_label = elgg_echo('e_portfolio:public_global_marks_label');
if ($public_global_marks){
   $selected_public_global_marks = "checked = \"checked\"";
} else {
   $selected_public_global_marks = "";
}

$rubric_label = elgg_echo('e_portfolio:rubric_label');
if ($use_rubric) {
   $selected_rubric = "checked = \"checked\"";
   $style_display_rubric = "display:block";
   $style_display_rubric_2 = "display:block";
} else {
   $selected_rubric = "";
   $style_display_rubric = "display:none";
   $style_display_rubric_2 = "display:none";
}

$rubrics_label = elgg_echo('e_portfolio:rubrics_label');

?>

<form action="<?php echo elgg_get_site_url()."action/".$action?>" name="setup_group_e_portfolio" enctype="multipart/form-data" method="post">

<?php echo elgg_view('input/securitytoken'); ?>

<p>
<b>
<?php echo "<input type = \"checkbox\" $disabled name = \"var_pages\" $selected_var_pages onChange=\"e_portfolio_show_var_pages()\"> $var_pages_label";?>
</b>   
</p>
<div id="resultsDiv_var_pages" style="<?php echo $style_display_var_pages;?>;">
   <p>
   <b><?php echo $num_pages_label; ?></b>
   <?php echo "<input type = \"text\" name = \"num_pages\" value = $num_pages>"; ?></p><br>
</div>

<p>
<b>
<?php echo $rating_type_label; ?>
</b><br>
<?php echo "<input type=\"radio\" $disabled name=\"rating_type\" value=$op_rating_type[0] $checked_radio_rating_type_0 onChange=\"e_portfolio_show_rating_type()\">$options_rating_type[0]"; ?><br> 
<?php echo "<input type=\"radio\" $disabled name=\"rating_type\" value=$op_rating_type[1] $checked_radio_rating_type_1 onChange=\"e_portfolio_show_rating_type()\">$options_rating_type[1]"; ?><br> 
</p>
<div id="resultsDiv_rating_type" style="<?php echo $style_display_rating_type;?>;">
   <p>
   <?php echo $type_mark_label; ?>
   </b><br>
   <?php echo "<input type=\"radio\" $disabled name=\"type_mark\" value=$op_type_mark[0] $checked_radio_type_mark_0 onChange=\"e_portfolio_show_type_mark(0)\">$options_type_mark[0]"; ?><br> 
   <?php echo "<input type=\"radio\" $disabled name=\"type_mark\" value=$op_type_mark[1] $checked_radio_type_mark_1 onChange=\"e_portfolio_show_type_mark(1)\">$options_type_mark[1]"; ?><br> 
   <?php echo "<input type=\"radio\" $disabled name=\"type_mark\" value=$op_type_mark[2] $checked_radio_type_mark_2 onChange=\"e_portfolio_show_type_mark(2)\">$options_type_mark[2]"; ?><br> 
   </p>
   <div id="resultsDiv_type_mark" style="<?php echo $style_display_type_mark;?>;">
      <p>
      <b>
      <?php echo $max_mark_label; ?>
      </b><br>
      <?php echo "<input type=\"radio\" $disabled name=\"max_mark\" value=$op_max_mark[0] $checked_radio_max_mark_0>$options_max_mark[0]"; ?><br> 
      <?php echo "<input type=\"radio\" $disabled name=\"max_mark\" value=$op_max_mark[1] $checked_radio_max_mark_1>$options_max_mark[1]"; ?><br>      
      </p><br>
   </div>
   <p>
   <b>
   <?php echo "<input type = \"checkbox\" name = \"public_global_marks\" $selected_public_global_marks> $public_global_marks_label"; ?> 
   </b>
   </p><br>
   <div id="resultsDiv_var_pages_2" style="<?php echo $style_display_var_pages_2;?>;">   
      <p>
      <b><?php echo $mark_weight_label; ?></b> 
      <?php
      if (is_array($mark_weight))
         $mark_weight_value = $mark_weight[0];
      else
         $mark_weight_value = $mark_weight; 
      echo "<input type = \"text\"  name = \"mark_weight\" value = $mark_weight_value>"; 
      ?>   
      </p><br>
   </div>  
   <div id="resultsDiv_var_pages_3" style="<?php echo $style_display_var_pages_3;?>;">  
      <b><?php echo $mark_weights_label; ?></b>
      <?php
      if ((count($mark_weight) > 0) && (is_array($mark_weight))){
         $i=0;
         foreach ($mark_weight as $one_mark_weight) {
            ?>
            <p class="clone_weight">
            <?php
            echo elgg_view("input/text", array("name" => "mark_weight_array[]","value" => $one_mark_weight));
            if ((($i>0)&&(!$pages_qualified))||(($e_portfolio_group_setup)&&($i>=$e_portfolio_group_setup->num_pages)&&($pages_qualified))){	
               ?>
               <!-- remove response -->
               <a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete"); ?></a> 
               <?php
            } 
            ?>
            </p>
            <?php
            $i=$i+1;
         }  
      } else {
         ?>
         <p class="clone_weight">
         <?php
         echo elgg_view("input/text", array("name" => "mark_weight_array[]","value" => $mark_weight));
         ?>   
         </p>         
         <?php
      }
      ?>
      <!-- add link to add which triggers a jquery clone function -->
      <a href="#" class="add" rel=".clone_weight"><?php echo elgg_echo("e_portfolio:add_mark_weight"); ?></a>
      <br /><br />
      </p>        
      <br>
   </div> 
</div>
<?php
if (elgg_is_active_plugin('rubric')){
?>
   <div id="resultsDiv_rating_type_2" style="<?php echo $style_display_rating_type_2;?>;">
   <div id="resultsDiv_rubric_2" style="<?php echo $style_display_rubric_2;?>;">
   <div id="resultsDiv_var_pages_4" style="<?php echo $style_display_var_pages_4;?>;"> 
      <p>
      <b><?php echo $max_game_points_label; ?></b> 
       <?php
       if (is_array($max_game_points)) {
         $max_game_points_value = $max_game_points[0];
      } else {
         $max_game_points_value = $max_game_points;  
       }
       echo "<input type = \"text\" name = \"max_game_points\" value = $max_game_points_value>"; 
       ?>   
      </p><br>
   </div>
   <div id="resultsDiv_var_pages_5" style="<?php echo $style_display_var_pages_5;?>;">  
      <b><?php echo $max_game_pointss_label; ?></b>
      <?php
      if ((count($max_game_points) > 0) && (is_array($max_game_points))) {
         $i=0;
         foreach ($max_game_points as $one_max_game_points) {
            ?>
            <p class="clone_max_game_points">
            <?php
	        echo elgg_view("input/text", array("name" => "max_game_points_array[]","value" => $one_max_game_points));
            if ((($i>0)&&(!$pages_qualified))||(($e_portfolio_group_setup)&&($i>=$e_portfolio_group_setup->num_pages)&&($pages_qualified))){	
               ?>
               <!-- remove response -->
               <a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete"); ?></a> 
               <?php
            } 
            ?>
            </p>
            <?php
            $i=$i+1;
         }  
      } else {	
         ?>
         <p class="clone_max_game_points">
         <?php
	    echo elgg_view("input/text", array("name" => "max_game_points_array[]","value" => $max_game_points));
         ?>   
         </p>         
         <?php
      }
      ?>
      <!-- add link to add which triggers a jquery clone function -->
      <a href="#" class="add" rel=".clone_max_game_points"><?php echo elgg_echo("e_portfolio:add_max_game_points"); ?></a>
      <br /><br />
      </p>        
      <br>
   </div> 
   </div>
   </div>
   <?php
}
if (elgg_is_active_plugin('rubric')){
   $members = $container->getMembers(array('limit'=>false));
   $rubrics = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'rubric', 'limit' => false, 'owner_guid' => $user_guid));
   foreach ($members as $member) {
      $member_guid = $member->getGUID();
      $group_owner_guid = $container->owner_guid;
      if (($member_guid!=$user_guid)&&(($group_owner_guid==$member_guid)||(check_entity_relationship($member_guid,'group_admin',$container_guid)))){
         $other_rubrics = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'rubric', 'limit' => false, 'owner_guid' => $member_guid, 'container_guid' => $container_guid));
	 if ($rubrics)
	    $rubrics = array_merge($rubrics,$other_rubrics);
	 else
	    $rubrics = $other_rubrics;
      }
   }   
   ?>
   <p>
   <b>
   <?php echo "<input type = \"checkbox\" $disabled name = \"use_rubric\" $selected_rubric onChange=\"e_portfolio_show_rubric()\"> $rubric_label";?>
   </b>   
   </p>
   <div id="resultsDiv_rubric" style="<?php echo $style_display_rubric;?>;"> 
   <div id="resultsDiv_var_pages_6" style="<?php echo $style_display_var_pages_6;?>;">   
      <p>
      <select name="rubric_guid">
      <?php
      if (is_array($rubric_guid))
         $rubric_guid_value = $rubric_guid[0];
      else
         $rubric_guid_value = $rubric_guid; 
      foreach ($rubrics as $this_rubric){
         $this_rubric_guid = $this_rubric->getGUID();
         $this_rubric_title = $this_rubric->title;
         ?>
         <option value="<?php echo $this_rubric_guid;?>" <?php if ($this_rubric_guid==$rubric_guid_value) echo "selected=\"selected\"";?>> <?php echo $this_rubric_title; ?> </option> 
         <?php
      }
      ?>
      </select>
      </p></br>
   </div>
   <div id="resultsDiv_var_pages_7" style="<?php echo $style_display_var_pages_7;?>;">   
     <b><?php echo $rubrics_label; ?></b><br>
      <?php
      if ((count($rubric_guid) > 0) && (is_array($rubric_guid))) {
         $i=0;
         foreach ($rubric_guid as $one_rubric_guid) {
	       $disabled_rubric = $disabled;
            ?>
            <p class="clone_rubric">
	    <select name="rubric_guid_array[]">
            <?php
            foreach ($rubrics as $this_rubric){
               $this_rubric_guid = $this_rubric->getGUID();
               $this_rubric_title = $this_rubric->title;
               ?>
               <option value="<?php echo $this_rubric_guid;?>" <?php if ($this_rubric_guid==$one_rubric_guid) echo "selected=\"selected\"";?>> <?php echo $this_rubric_title; ?> </option> 
               <?php
            }
            ?>
            </select>
            <?php
            if ((($i>0)&&(!$pages_qualified))||(($e_portfolio_group_setup)&&($i>=$e_portfolio_group_setup->num_pages)&&($pages_qualified))){	
               ?>
               <!-- remove response -->
               <a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete"); ?></a> 
               <?php
            } 
            ?>
            </p>
            <?php
            $i=$i+1;
         }  
      } else {
         ?>
         <p class="clone_rubric">
	 <select name="rubric_guid_array[]">
         <?php
         foreach ($rubrics as $this_rubric){
            $this_rubric_guid = $this_rubric->getGUID();
            $this_rubric_title = $this_rubric->title;
            ?>
            <option value="<?php echo $this_rubric_guid;?>" <?php if ($this_rubric_guid==$rubric_guid) echo "selected=\"selected\"";?>> <?php echo $this_rubric_title; ?> </option> 
            <?php
         }
         ?>
         </select>
         </p>         
         <?php
      }
      ?>
      <!-- add link to add which triggers a jquery clone function -->
      <a href="#" class="add" rel=".clone_rubric"><?php echo elgg_echo("e_portfolio:add_rubric"); ?></a>
      <br /><br />
      </p>        
      <br>
   </div>
   </div>
<?php
}

?>
<!-- add the add/delete_response functionality  -->
<script type="text/javascript">
// remove function for the jquery clone plugin
$(function(){
   var removeLink = '<a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete");?></a>';
   $('a.add').relCopy({ append: removeLink});
});
</script>
<?php


if (isset($vars['entity'])) {
   ?>
   <input type="hidden" name="e_portfolio_group_setup_guid" value="<?php echo $vars['entity']->getGUID(); ?>">
   <?php
   $submit_input = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('e_portfolio:save_setup_group')));
} else {
   ?>
   <input type="hidden" name="container_guid" value="<?php echo $vars['container_guid']; ?>">
   <?php
   $submit_input = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('e_portfolio:save_enable_setup_group')));
}
echo $submit_input;

?>
</form>

<script language="javascript">
   function e_portfolio_show_rating_type(){
      var resultsDiv_rating_type = document.getElementById('resultsDiv_rating_type');
      var resultsDiv_rating_type_2 = document.getElementById('resultsDiv_rating_type_2');
      if (resultsDiv_rating_type.style.display == 'none'){
         resultsDiv_rating_type.style.display = 'block';
         resultsDiv_rating_type_2.style.display = 'none';
      } else {       
         resultsDiv_rating_type.style.display = 'none';
         resultsDiv_rating_type_2.style.display = 'block';
      }
   } 
   function e_portfolio_show_type_mark(item){
      var resultsDiv_type_mark = document.getElementById('resultsDiv_type_mark')
;    
      
      if (item == 0){
         resultsDiv_type_mark.style.display = 'block';
      } else {
         resultsDiv_type_mark.style.display = 'none';
      }
   }  
   function e_portfolio_show_var_pages(){
      var resultsDiv_var_pages = document.getElementById('resultsDiv_var_pages');
      var resultsDiv_var_pages_2 = document.getElementById('resultsDiv_var_pages_2');
       var resultsDiv_var_pages_3 = document.getElementById('resultsDiv_var_pages_3');
       var resultsDiv_var_pages_4 = document.getElementById('resultsDiv_var_pages_4');
       var resultsDiv_var_pages_5 = document.getElementById('resultsDiv_var_pages_5');
       var resultsDiv_var_pages_6 = document.getElementById('resultsDiv_var_pages_6');
       var resultsDiv_var_pages_7 = document.getElementById('resultsDiv_var_pages_7');
      if (resultsDiv_var_pages.style.display == 'none'){
         resultsDiv_var_pages.style.display = 'block';
	 resultsDiv_var_pages_2.style.display = 'none';
	 resultsDiv_var_pages_3.style.display = 'block';
	 resultsDiv_var_pages_4.style.display = 'none';
	 resultsDiv_var_pages_5.style.display = 'block';
	 resultsDiv_var_pages_6.style.display = 'none';
	 resultsDiv_var_pages_7.style.display = 'block';
      } else {       
         resultsDiv_var_pages.style.display = 'none';
	 resultsDiv_var_pages_2.style.display = 'block';
	 resultsDiv_var_pages_3.style.display = 'none';
	 resultsDiv_var_pages_4.style.display = 'block';
	 resultsDiv_var_pages_5.style.display = 'none';
	 resultsDiv_var_pages_6.style.display = 'block';
	 resultsDiv_var_pages_7.style.display = 'none';
      }
   }     
   function e_portfolio_show_rubric(){
      var resultsDiv_rubric = document.getElementById('resultsDiv_rubric');
      var resultsDiv_rubric_2 = document.getElementById('resultsDiv_rubric_2');
      if (resultsDiv_rubric.style.display == 'none'){
         resultsDiv_rubric.style.display = 'block';
         resultsDiv_rubric_2.style.display = 'block';
      } else {       
         resultsDiv_rubric.style.display = 'none';
         resultsDiv_rubric_2.style.display = 'none';
      }
   }   

</script>

<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/e_portfolio/lib/reCopy.js"></script><!-- copy field jquery plugin -->

</div>
