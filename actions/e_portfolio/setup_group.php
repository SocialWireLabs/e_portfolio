<?php

gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$e_portfolio_group_setup_guid = get_input('e_portfolio_group_setup_guid');
$e_portfolio_group_setup = get_entity($e_portfolio_group_setup_guid);

if ($e_portfolio_group_setup) {
   $options = array('types' => 'object','subtypes' => 'e_portfolio','container_guid' => $e_portfolio_group_setup->container_guid);
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
   $var_pages = $e_portfolio_group_setup->var_pages;
   $use_rubric = $e_portfolio_group_setup->use_rubric;
   $rating_type = $e_portfolio_group_setup->rating_type;
   if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
      $type_mark = $e_portfolio_group_setup->type_mark;
      if (strcmp($type_mark,'e_portfolio_type_mark_numerical')==0){
         $max_mark = $e_portfolio_group_setup->max_mark;
      }
   }
} else {
   $var_pages = get_input('var_pages');
   if (strcmp($var_pages,"on")==0)
      $var_pages = true;
   else
      $var_pages = false;
   $use_rubric = get_input('use_rubric');
   if (strcmp($use_rubric,"on")==0)
      $use_rubric = true;
   else
      $use_rubric = false;
   $rating_type = get_input('rating_type');
   if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
      $type_mark = get_input('type_mark');
      if (strcmp($type_mark,'e_portfolio_type_mark_numerical')==0){
         $max_mark = get_input('max_mark');
      }
   } 
} 

$public_global_marks = get_input('public_global_marks');
if (strcmp($public_global_marks,"on")==0)
   $public_global_marks = true;
else
   $public_global_marks = false;

if (!$var_pages) {
   $num_pages = get_input('num_pages');
}

if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
   if ($var_pages) 
      $mark_weight = get_input('mark_weight');
   else {
      $mark_weight_array = get_input('mark_weight_array');
      $mark_weight = implode(Chr(26),$mark_weight_array);  
   }   
} else {
   if ($use_rubric) {
      if ($var_pages) 
         $max_game_points = get_input('max_game_points');
      else {
         $max_game_points_array = get_input('max_game_points_array');  
	 $max_game_points = implode(Chr(26),$max_game_points_array);
      }
   }
}
if ($use_rubric) {
   if ($var_pages) 
      $rubric_guid = get_input('rubric_guid');
   else {
      $rubric_guid_array = get_input('rubric_guid_array');
      $rubric_guid = implode(Chr(26),$rubric_guid_array);
   }
}

// Cache to the session
elgg_make_sticky_form('setup_group_e_portfolio');
 
if (!$pages_qualified) {
   if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
      if (strcmp($type_mark,'')==0) {
         register_error(elgg_echo("e_portfolio:empty_type_mark"));
         forward($_SERVER['HTTP_REFERER']);
      }
      if (strcmp($type_mark,'e_portfolio_type_mark_numerical')==0){
         if (strcmp($max_mark,'')==0) { 
            register_error(elgg_echo("e_portfolio:empty_max_mark"));
            forward($_SERVER['HTTP_REFERER']);
         }
      }  
   }
}

if (!$var_pages){

   //Integer num_pages
   $is_integer = true;
   $mask_integer='^([[:digit:]]+)$';                           
   if (ereg($mask_integer,$num_pages,$same)){
      if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
         $is_integer=false;
      }
   } else {
      $is_integer=false;
   }
   if (!$is_integer){
      register_error(elgg_echo("e_portfolio:bad_num_pages"));
      forward($_SERVER['HTTP_REFERER']);
   }
   if (($pages_qualified)&&($num_pages<$e_portfolio_group_setup->num_pages)){
      register_error(elgg_echo("e_portfolio:decrease_num_pages_not_allowed"));
      forward($_SERVER['HTTP_REFERER']);
   }
   if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
      if ($num_pages != count($mark_weight_array)) {
         register_error(elgg_echo("e_portfolio:bad_num_mark_weight"));
         forward($_SERVER['HTTP_REFERER']);
      }
   } else {
      if ($use_rubric) {
         if ($num_pages != count($max_game_points_array)) {
            register_error(elgg_echo("e_portfolio:bad_num_max_game_points"));
            forward($_SERVER['HTTP_REFERER']);
         }
      }
   }
   if ($use_rubric) {
      if ($num_pages != count($rubric_guid_array)) {
         register_error(elgg_echo("e_portfolio:bad_num_rubric_guid"));
         forward($_SERVER['HTTP_REFERER']);
      }
   }
}

if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
   //Integer mark_weight (0<mark_weight<100)
   if ($var_pages) {
      $is_integer = true;
      $mask_integer='^([[:digit:]]+)$';                           
      if (ereg($mask_integer,$mark_weight,$same)){
         if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
            $is_integer=false;
         }
      } else {
         $is_integer=false;
      }
      if (!$is_integer){
         register_error(elgg_echo("e_portfolio:bad_mark_weight"));
         forward($_SERVER['HTTP_REFERER']);
      }
      if ($mark_weight>100){
         register_error(elgg_echo("e_portfolio:bad_mark_weight"));
         forward($_SERVER['HTTP_REFERER']);
      }
   } else {
      foreach($mark_weight_array as $one_mark_weight) {
         $is_integer = true;
         $mask_integer='^([[:digit:]]+)$';                           
         if (ereg($mask_integer,$one_mark_weight,$same)){
            if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
               $is_integer=false;
            }
         } else {
            $is_integer=false;
         }
         if (!$is_integer){
            register_error(elgg_echo("e_portfolio:bad_mark_weight"));
            forward($_SERVER['HTTP_REFERER']);
         }
         if ($one_mark_weight>100){
            register_error(elgg_echo("e_portfolio:bad_mark_weight"));
            forward($_SERVER['HTTP_REFERER']);
         }
      }
   }
} else {
   if ($use_rubric) {
      //Integer max game points
      if ($var_pages) {
         $is_integer = true;
         $mask_integer='^([[:digit:]]+)$';                           
         if (ereg($mask_integer,$max_game_points,$same)){
            if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
               $is_integer=false;
            }
         } else {
            $is_integer=false;
         }
         if (!$is_integer){
            register_error(elgg_echo("e_portfolio:bad_max_game_points"));
            forward($_SERVER['HTTP_REFERER']);
         }
	 if (($pages_qualified)&&($max_game_points!=$e_portfolio_group_setup->max_game_points)){
            register_error(elgg_echo("e_portfolio:change_max_game_points_not_allowed"));
            forward($_SERVER['HTTP_REFERER']);
         }
      } else {
         $i=1;
	 $previous_max_game_points = explode(Chr(26),$e_portfolio_group_setup->max_game_points);
         foreach ($max_game_points_array as $one_max_game_points) {
	    $is_integer = true;
            $mask_integer='^([[:digit:]]+)$';                           
            if (ereg($mask_integer,$one_max_game_points,$same)){
               if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
                  $is_integer=false;
               }
            } else {
               $is_integer=false;
            }
            if (!$is_integer){
               register_error(elgg_echo("e_portfolio:bad_max_game_points"));
               forward($_SERVER['HTTP_REFERER']);
            }
	    if (($pages_qualified)&&($i<=$e_portfolio_group_setup->num_pages)&&($one_max_game_points!=$previous_max_game_points[$i-1])) {
               register_error(elgg_echo("e_portfolio:change_max_game_points_not_allowed"));
               forward($_SERVER['HTTP_REFERER']);
            }
	    $i=$i+1;
	 }
      }
   }
}
if (($use_rubric)&&($pages_qualified)) {
   if ($var_pages) {
      if ($rubric_guid!=$e_portfolio_group_setup->rubric_guid){
         register_error(elgg_echo("e_portfolio:change_rubric_not_allowed"));
         forward($_SERVER['HTTP_REFERER']);
      }
   } else {
      $i=1;
      $previous_rubric_guid = explode(Chr(26),$e_portfolio_group_setup->rubric_guid);
      foreach ($rubric_guid_array as $one_rubric_guid) {
         if (($i<=$e_portfolio_group_setup->num_pages)&&($one_rubric_guid!=$previous_rubric_guid[$i-1])) {
            register_error(elgg_echo("e_portfolio:change_rubrics_not_allowed"));
            forward($_SERVER['HTTP_REFERER']);
         }
      	 $i = $i+1;  
      }
   }
}
           
if ($e_portfolio_group_setup && $e_portfolio_group_setup->getSubtype() == 'e_portfolio_group_setup') {
   $container_guid = $e_portfolio_group_setup->container_guid;
   if (!$pages_qualified) {
      if ($var_pages) {
         $e_portfolio_group_setup->var_pages = true;
      } else {
         $e_portfolio_group_setup->var_pages = false;
      }
      $e_portfolio_group_setup->rating_type = $rating_type;
      if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
         $e_portfolio_group_setup->type_mark = $type_mark;
         if (strcmp($type_mark,'e_portfolio_type_mark_numerical')==0){
            $e_portfolio_group_setup->max_mark = $max_mark;
         }
         switch($type_mark){
            case 'e_portfolio_type_mark_numerical':
               if ($max_mark==10)
                  $e_portfolio_group_setup->mark_type= NUMERIC10;
               else
                  $e_portfolio_group_setup->mark_type= NUMERIC100;
               break;
            case 'e_portfolio_type_mark_textual':
               $e_portfolio_group_setup->mark_type= STRINGUNI;
               break;
            case 'e_portfolio_type_mark_apto':
               $e_portfolio_group_setup->mark_type= BOOLEAN;
               break;
         }    
      } 
      if ($use_rubric) {
         $e_portfolio_group_setup->use_rubric=true;
      } else { 
         $e_portfolio_group_setup->use_rubric=false;
      }
   }

   $e_portfolio_group_setup->public_global_marks = $public_global_marks;

   if (!$var_pages) 
      $e_portfolio_group_setup->num_pages = $num_pages;
   
   if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
      $e_portfolio_group_setup->mark_weight = $mark_weight;
   } else {
       if ($use_rubric) 
	 $e_portfolio_group_setup->max_game_points = $max_game_points;
   }
   if ($use_rubric) 
      $e_portfolio_group_setup->rubric_guid = $rubric_guid;

} else {
   $container_guid = get_input('container_guid');
   $e_portfolio_group_setup = new ElggObject();
   $e_portfolio_group_setup->subtype = 'e_portfolio_group_setup';
   $e_portfolio_group_setup->owner_guid = $user_guid;
   $e_portfolio_group_setup->container_guid = $container_guid;
   $e_portfolio_group_setup->group_guid = $container_guid;
   $e_portfolio_group_setup->access_id = get_entity($container_guid)->group_acl;
   $e_portfolio_group_setup->qualify_opened = false;
   
   if ($var_pages) {
      $e_portfolio_group_setup->var_pages = true;
   } else {
      $e_portfolio_group_setup->var_pages = false;
      $e_portfolio_group_setup->num_pages = $num_pages;
   }
   $e_portfolio_group_setup->rating_type = $rating_type;
   if (strcmp($rating_type,'e_portfolio_rating_type_marks')==0){
      $e_portfolio_group_setup->type_mark = $type_mark;
      if (strcmp($type_mark,'e_portfolio_type_mark_numerical')==0){
         $e_portfolio_group_setup->max_mark = $max_mark;
      }
      switch($type_mark){
         case 'e_portfolio_type_mark_numerical':
            if ($max_mark==10)
               $e_portfolio_group_setup->mark_type= NUMERIC10;
            else
               $e_portfolio_group_setup->mark_type= NUMERIC100;
            break;
         case 'e_portfolio_type_mark_textual':
            $e_portfolio_group_setup->mark_type= STRINGUNI;
            break;
         case 'e_portfolio_type_mark_apto':
            $e_portfolio_group_setup->mark_type= BOOLEAN;
            break;
      }
      $e_portfolio_group_setup->public_global_marks = $public_global_marks;
      $e_portfolio_group_setup->mark_weight = $mark_weight;
   } else {
      if ($use_rubric) {
	 $e_portfolio_group_setup->max_game_points = $max_game_points;
      }
   }
   if ($use_rubric) {
      $e_portfolio_group_setup->use_rubric=true;
      $e_portfolio_group_setup->rubric_guid=$rubric_guid;
   } else { 
      $e_portfolio_group_setup->use_rubric=false;
   }
   if (!$e_portfolio_group_setup->save()) {
      register_error(elgg_echo("e_portfolio:error_save"));
      forward($_SERVER['HTTP_REFERER']);
   }
}

//Pages setup
$options = array('types' => 'object','subtypes' => 'e_portfolio','container_guid' => $container_guid, 'limit'=>false);
$e_portfolios = elgg_get_entities_from_metadata($options);
foreach ($e_portfolios as $one_e_portfolio){
   $options = array('types' => 'object','subtypes' => 'e_portfolio_page','container_guid' => $one_e_portfolio->getGUID(),'limit'=>false);
   $e_portfolio_pages = elgg_get_entities_from_metadata($options);
   foreach ($e_portfolio_pages as $one_e_portfolio_page){
      $page_number = $one_e_portfolio_page->page_number;
      if (((!$e_portfolio_group_setup->var_pages) && ($page_number<=$e_portfolio_group_setup->num_pages)) || ($e_portfolio_group_setup->var_pages)) {
	 if (!$pages_qualified) {
	    $one_e_portfolio_page->var_pages = $e_portfolio_group_setup->var_pages;
            $one_e_portfolio_page->rating_type = $e_portfolio_group_setup->rating_type;
	    $one_e_portfolio_page->use_rubric = $e_portfolio_group_setup->use_rubric;
	    if (strcmp($one_e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0) {
               $one_e_portfolio_page->mark_type = $e_portfolio_group_setup->mark_type;
               $one_e_portfolio_page->type_mark = $e_portfolio_group_setup->type_mark;
               $one_e_portfolio_page->max_mark = $e_portfolio_group_setup->max_mark;
	    }
	 } 
	 if (strcmp($one_e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0) {
	    if (!$one_e_portfolio_page->var_pages) {
	       $mark_weight_stream = $e_portfolio_group_setup->mark_weight;
	       $mark_weight_array = explode(Chr(26),$mark_weight_stream);
	       $one_e_portfolio_page->mark_weight = $mark_weight_array[$page_number-1];
	    } else 
	       $one_e_portfolio_page->mark_weight = $e_portfolio_group_setup->mark_weight;
	 } else {
	    if ($one_e_portfolio_page->use_rubric) {
               if (!$one_e_portfolio_page->var_pages) {
		  $max_game_points_stream = $e_portfolio_group_setup->max_game_points;
		  $max_game_points_array = explode(Chr(26),$max_game_points_stream);
                  $one_e_portfolio_page->max_game_points = $max_game_points_array[$page_number-1];
	       } else  {
		  $one_e_portfolio_page->max_game_points = $e_portfolio_group_setup->max_game_points;
	       }
	    }
	 }
	 if ($one_e_portfolio_page->use_rubric) {
	    if (!$one_e_portfolio_page->var_pages) {	       
	       $rubric_guid_stream = $e_portfolio_group_setup->rubric_guid;
	       $rubric_guid_array = explode(Chr(26),$rubric_guid_stream);
               $one_e_portfolio_page->rubric_guid = $rubric_guid_array[$page_number-1]; 
            } else {   
               $one_e_portfolio_page->rubric_guid = $e_portfolio_group_setup->rubric_guid; 
            }
         }	 
      }
   }
}

// Remove the e_portfolio post cache
elgg_clear_sticky_form('setup_group_e_portfolio');

system_message(elgg_echo("e_portfolio:setup_group_saved"));

forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
?>