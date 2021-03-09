<?php

gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$e_portfolio_page_guid = get_input('e_portfolio_page_guid');
$e_portfolio_page = get_entity($e_portfolio_page_guid);
$e_portfoliopost = $e_portfolio_page->container_guid;
$e_portfolio = get_entity($e_portfoliopost);
$container_guid = $e_portfolio->container_guid;
$container = get_input($container_guid);

if ($e_portfolio_page->getSubtype() == "e_portfolio_page" && $e_portfolio_page->canEdit()) {

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

   $title = strip_tags(get_input('title'));
   $skills = get_input('skills');
   $reflections = get_input('reflections');
   $allow_comments = get_input('allow_comments');
   $tags = get_input('tags');
   $access_id = get_input('access_id'); 
   $selected_action = get_input('submit');
  
   // Cache to the session
   elgg_make_sticky_form('edit_page_e_portfolio');

   // Convert string of tags into a preformatted array
   $tagarray = string_to_tag_array($tags);

   // Make sure the title is not blank
   if (empty($title)) {
      register_error(elgg_echo("e_portfolio:page_title_blank"));
      forward(elgg_get_site_url() . 'e_portfolio/edit_page/' . $e_portfolio_page_guid);
   }		

   if ($e_portfolio_page->access_id != $access_id) {
      $e_portfolio_page->access_id = $access_id;
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'limit' => false, 'container_guid' => $e_portfolio_page_guid);
      $artifacts = elgg_get_entities_from_metadata($options);
      foreach ($artifacts as $artifact) {
         $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $artifact->getGUID(), 'inverse_relationship' => FALSE, 'type' => 'object','limit'=>0));
         foreach ($files as $file) {
            $file->access_id = $access_id;
            if (!$file->save()) {
               register_error(elgg_echo("e_portfolio:file_error_save"));
               forward(elgg_get_site_url() . 'e_portfolio/edit_page/' . $e_portfolio_page_guid);
            }
         }
         $artifact->access_id = $access_id;
         if (!$artifact->save()) {
	    register_error(elgg_echo("e_portfolio:artifact_error_save"));
            forward(elgg_get_site_url() . 'e_portfolio/edit_page/' . $e_portfolio_page_guid);
	 }
      }
   }

   $e_portfolio_page->title = $title;
   if (is_array($tagarray)) {
      $e_portfolio_page->tags = $tagarray;
   }
    
   if (!$e_portfolio_page->save()) {
      register_error(elgg_echo("e_portfolio:page_error_save"));
      forward(elgg_get_site_url() . 'e_portfolio/edit_page/' . $e_portfolio_page_guid);
   }

   $e_portfolio_page->skills = $skills;
   $e_portfolio_page->reflections = $reflections;
   $e_portfolio_page->allow_comments = $allow_comments;

   $page_number = $e_portfolio_page->page_number;

   if ($container instanceof ElggGroup) {
      if (($e_portfolio_group_setup)&&(!$e_portfolio_page->var_pages)&&($page_number<=$e_portfolio_group_setup->num_pages)) {
         if (strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0) {
            $e_portfolio_page->mark_weight = $e_portfolio_group_setup->mark_weight[$page_number];
         } else {
            if ($e_portfolio_page->use_rubric) 
               $e_portfolio_page->max_game_points = $e_portfolio_group_setup->max_game_points[$page_number];
         }
         if ($e_portfolio_page->use_rubric) 
            $e_portfolio_page->rubric_guid = $e_portfolio_group_setup->rubric_guid[$page_number]; 
      }
   }
		                       
   // Remove the e_portfolio post cache
   elgg_clear_sticky_form('edit_page_e_portfolio');
		
   // Success message
   system_message(elgg_echo("e_portfolio:page_updated"));
		
   // Add to the river
   // if (time() - $e_portfolio->time_updated > 1800)
   //    elgg_create_river_item(array(
   //       'view'=>'river/object/e_portfolio/update',
   //       'action_type'=>'update',
   //       'subject_guid'=>$user_guid,
   //       'object_guid'=>$e_portfoliopost,
   //    ));

   $e_portfolio->time_updated = time();

   //Forward
   if (strcmp($selected_action,elgg_echo('e_portfolio:save_page'))==0) {
      forward(elgg_get_site_url() . 'e_portfolio/add_artifact/' . $e_portfolio_page_guid);
   } else {
      forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfoliopost);
   }
}

}

?>