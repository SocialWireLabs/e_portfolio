<?php

gatekeeper();

$page_guid = (int) get_input('page_guid');
$page = get_entity($page_guid);
$e_portfolio = get_entity($page->container_guid);
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

if ($page->getSubtype() == "e_portfolio_page" && $page->canEdit()) {

   if ($container instanceof ElggGroup) {
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
      $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
      $e_portfolio_group_setup = $e_portfolio_group_setup[0];
   }

   if (($e_portfolio_group_setup) && ($e_portfolio_group_setup->qualify_opened)) {
      register_error(elgg_echo('e_portfolio:error_rating_opened'));
   } else {
		
   $access_id = 0;
   $page->access_id = $access_id;
   $well = true;
   if (!$page->save()) {
      register_error(elgg_echo("e_portfolio:page_error_save"));
      $well = false;
   }
   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'limit' => false, 'container_guid' => $page_guid);
   $artifacts = elgg_get_entities_from_metadata($options);
   foreach ($artifacts as $artifact) {
      $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $artifact->getGUID(), 'inverse_relationship' => FALSE, 'type' => 'object','limit'=>0));
      foreach ($files as $file) {
         $file->access_id = $access_id;
	 if (!$file->save()) {
	    register_error(elgg_echo("e_portfolio:file_error_save"));
	    if ($well)
	       $well = false;
	 }
      }
      $artifact->access_id = $access_id;
      if (!$artifact->save()) {
         register_error(elgg_echo("e_portfolio:artifact_error_save"));
	 if ($well)
	    $well = false;
      }
   }
   if ($well)
      system_message(elgg_echo("e_portfolio:page_closed"));
}
}
forward($_SERVER['HTTP_REFERER']);
?>