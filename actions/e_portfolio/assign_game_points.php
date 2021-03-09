<?php

gatekeeper();

$e_portfolio_page_guid = get_input('e_portfolio_page_guid');
$e_portfolio_page = get_entity($e_portfolio_page_guid);
$e_portfolio = get_entity($e_portfolio_page->container_guid);
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

$options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
$e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
$e_portfolio_group_setup = $e_portfolio_group_setup[0];

if (!$e_portfolio_group_setup->qualify_opened) {
   register_error(elgg_echo('e_portfolio:error_rating_closed'));
} else {

$owner = $e_portfolio_page->getOwnerEntity();
$owner_guid = $owner->getGUID();
$user_guid = elgg_get_logged_in_user_guid();
	
$rating = $e_portfolio_page->rating;

$access = elgg_set_ignore_access(true);
$game_points = gamepoints_get_entity($e_portfolio_page_guid);
if ($game_points) {
   if (strcmp($rating,"not_qualified")!=0) {
      gamepoints_update($game_points->guid, $rating);
   } else {
      gamepoints_update($game_points->guid,"");
   }
} else { 
   if (strcmp($rating,"not_qualified")!=0) {
      $description = $e_portfolio->title . " (" . $e_portfolio_page->title . ")";  
      gamepoints_add($owner_guid, $rating, $e_portfolio_page_guid, $container_guid,false,$description);
   }
}
elgg_set_ignore_access($access);

//System message
system_message(elgg_echo("e_portfolio:game_points_assigned"));

}

//Forward
forward("e_portfolio/view/$e_portfolio_page_guid");   

?>