<?php

gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$e_portfoliopost = get_input('e_portfoliopost');
$e_portfolio = get_entity($e_portfoliopost);

$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

if ($e_portfolio->getSubtype() == "e_portfolio" && $e_portfolio->canEdit()) {

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

   $description = get_input('description');
   $allow_comments = get_input('allow_comments');
   $tags = get_input('e_portfoliotags');
   $access_id = get_input('access_id');
   $container_guid = $e_portfolio->container_guid;
   $container = get_entity($container_guid);    
   $selected_action = get_input('submit');

   // Cache to the session
   elgg_make_sticky_form('edit_e_portfolio');

   // Convert string of tags into a preformatted array
   $tagarray = string_to_tag_array($tags);
       
   $change_access_id = false;
   if ($e_portfolio->access_id!=$access_id)
      $change_access_id = true;
   $e_portfolio->access_id = $access_id;

   $e_portfolio->description = $description;
   $e_portfolio->allow_comments = $allow_comments;

   // Now let's add tags.
   if (is_array($tagarray)) {
      $e_portfolio->tags = $tagarray;
   }
   
   if (!$e_portfolio->save()) {
      register_error(elgg_echo("e_portfolio:error_save"));
      forward(elgg_get_site_url() . 'e_portfolio/edit/' . $e_portfoliopost);
   }

   if (($change_access_id) && ($access_id == 0)) {
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'limit' => false, 'container_guid' => $e_portfoliopost);
      $pages = elgg_get_entities_from_metadata($options);
      foreach ($pages as $page){
         $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'limit' => false, 'container_guid' => $page->getGUID());
         $artifacts = elgg_get_entities_from_metadata($options);
	 foreach ($artifacts as $artifact) {
            $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $artifact->getGUID(), 'inverse_relationship' => FALSE, 'type' => 'object','limit'=>0));
            foreach ($files as $file) {
	       $file->access_id = $access_id;
               if (!$file->save()) {
                  register_error(elgg_echo("e_portfolio:file_error_save"));
                  forward($_SERVER['HTTP_REFERER']);
               }
            }
	    $artifact->access_id = $access_id;
            if (!$artifact->save()) {
               register_error(elgg_echo("e_portfolio:artifact_error_save"));
               forward($_SERVER['HTTP_REFERER']);
            }
         }
         $page->access_id = $access_id;
         if (!$page->save()) {
            register_error(elgg_echo("e_portfolio:page_error_save"));
            forward($_SERVER['HTTP_REFERER']);
         }
      }
   }

   // Remove the e_portfolio post cache
   elgg_clear_sticky_form('edit_e_portfolio');

   // Success message
   system_message(elgg_echo("e_portfolio:updated"));
		
   //Add to the river
   // if (time() - $e_portfolio->time_updated > 1800)
   //    elgg_create_river_item(array(
   //       'view'=>'river/object/e_portfolio/update',
   //       'action_type'=>'update',
   //       'subject_guid'=>$user_guid,
   //       'object_guid'=>$e_portfoliopost,
   //    ));

   $e_portfolio->time_updated = time();

   //Forward
   if (strcmp($selected_action,elgg_echo('e_portfolio:save'))==0) {
      forward(elgg_get_site_url() . 'e_portfolio/add_page/' . $e_portfoliopost);
   } else {
      forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfoliopost);
   }
}

}

?>