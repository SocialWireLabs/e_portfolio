<?php

gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$e_portfoliopost = get_input('e_portfoliopost');
$e_portfolio = get_entity($e_portfoliopost);
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

if ($container instanceof ElggGroup) {
   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
   $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
   $e_portfolio_group_setup = $e_portfolio_group_setup[0];
}

if (($e_portfolio_group_setup) && ($e_portfolio_group_setup->qualify_opened)) {

   register_error(elgg_echo('e_portfolio:error_rating_opened'));
   //Forward
   forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfoliopost);

} else {

// Get input data
$title = strip_tags(get_input('title'));
$skills = get_input('skills');
$reflections = get_input('reflections');
$allow_comments = get_input('allow_comments');
$tags = get_input('tags');
$access_id = get_input('access_id');
$selected_action = get_input('submit');

$options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'limit' => false, 'count' => true, 'container_guid' => $e_portfoliopost);
$count_pages = elgg_get_entities_from_metadata($options);
$page_number = $count_pages + 1;

if (($e_portfolio_group_setup) && (!$e_portfolio_group_setup->var_pages) && ($count_pages == $e_portfolio_group_setup->num_pages)) {
   register_error(elgg_echo('e_portfolio:error_num_pages_exceeded'));
   //Forward
   forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfoliopost);
}

// Cache to the session
elgg_make_sticky_form('add_page_e_portfolio');

// Convert string of tags into a preformatted array
$tagarray = string_to_tag_array($tags);

// Make sure the title is not blank
if (empty($title)) {
   register_error(elgg_echo("e_portfolio:page_title_blank"));
   forward(elgg_get_site_url() . 'e_portfolio/add_page/' . $e_portfoliopost);
}

// Initialise a new ElggObject
$e_portfolio_page = new ElggObject();
$e_portfolio_page->subtype = "e_portfolio_page";
$e_portfolio_page->owner_guid = $user_guid;
$e_portfolio_page->container_guid = $e_portfoliopost;
if ($container instanceof ElggGroup) {
   $e_portfolio_page->group_guid = $container_guid;
}
$e_portfolio_page->access_id = $access_id;
$e_portfolio_page->title = $title;
$e_portfolio_page->page_number = $page_number;
$e_portfolio_page->skills = $skills;
$e_portfolio_page->reflections = $reflections;
$e_portfolio_page->allow_comments = $allow_comments;
if (is_array($tagarray)) {
   $e_portfolio_page->tags = $tagarray;
}

if (($e_portfolio_group_setup) && (((!$e_portfolio_group_setup->var_pages) && ($page_number<=$e_portfolio_group_setup->num_pages)) || ($e_portfolio_group_setup->var_pages)) ) {
   $e_portfolio_page->var_pages = $e_portfolio_group_setup->var_pages;
   $e_portfolio_page->rating_type = $e_portfolio_group_setup->rating_type;
   $e_portfolio_page->use_rubric = $e_portfolio_group_setup->use_rubric;
   if (strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0) {
      $e_portfolio_page->mark_type = $e_portfolio_group_setup->mark_type;
      $e_portfolio_page->type_mark = $e_portfolio_group_setup->type_mark;
      $e_portfolio_page->max_mark = $e_portfolio_group_setup->max_mark;
   } 
   if (!$e_portfolio_page->var_pages) {
      if (strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0) {
         $mark_weight_stream = $e_portfolio_group_setup->mark_weight;
         $mark_weight_array = explode(Chr(26),$mark_weight_stream); 
         $e_portfolio_page->mark_weight = $mark_weight_array[$page_number-1];
      } else {
         if ($e_portfolio_page->use_rubric) {
	    $max_game_points_stream = $e_portfolio_group_setup->max_game_points;
	    $max_game_points_array = explode(Chr(26),$max_game_points_stream);
            $e_portfolio_page->max_game_points = $max_game_points_array[$page_number-1];
	 }
      }
      if ($e_portfolio_page->use_rubric) { 
         $rubric_guid_stream = $e_portfolio_group_setup->rubric_guid;
         $rubric_guid_array = explode(Chr(26),$rubric_guid_stream);
         $e_portfolio_page->rubric_guid = $rubric_guid_array[$page_number-1]; 
      }
   } else {
      if (strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0) {
         $e_portfolio_page->mark_weight = $e_portfolio_group_setup->mark_weight;
      } else {
         if ($e_portfolio_page->use_rubric) 
            $e_portfolio_page->max_game_points = $e_portfolio_group_setup->max_game_points;
      }
      if ($e_portfolio_page->use_rubric) 
         $e_portfolio_page->rubric_guid = $e_portfolio_group_setup->rubric_guid; 
   }
}   

$e_portfolio_page->rating = "not_qualified"; 

$e_portfolio_page->group_guid = $container_guid;  

// Now save the object
if (!$e_portfolio_page->save()) {
   register_error(elgg_echo("e_portfolio:page_error_save"));
   forward(elgg_get_site_url() . 'e_portfolio/add_page/' . $e_portfoliopost);
}

// Remove the e_portfolio post cache
elgg_clear_sticky_form('add_page_e_portfolio');

// Success message
system_message(elgg_echo("e_portfolio:page_created"));
                
// Add to river

// if (time() - $e_portfolio->time_updated > 1800)
//    elgg_create_river_item(array(
//          'view'=>'river/object/e_portfolio/update',
//          'action_type'=>'update',
//          'subject_guid'=>$user_guid,
//          'object_guid'=>$e_portfoliopost,
//    ));

$e_portfolio->time_updated = time();
                
//Forward
if (strcmp($selected_action,elgg_echo('e_portfolio:save_page'))==0) {
   forward(elgg_get_site_url() . 'e_portfolio/add_artifact/' . $e_portfolio_page->getGUID());
} else {
   forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfoliopost);
}

}
?>