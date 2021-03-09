<?php

gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);
$owner=$user;

$container_guid = get_input('container_guid');
$container = get_entity($container_guid);

if ($container instanceof ElggGroup) {
   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
   $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
   $e_portfolio_group_setup = $e_portfolio_group_setup[0];
}

if (($e_portfolio_group_setup) && ($e_portfolio_group_setup->qualify_opened)) {

   register_error(elgg_echo('e_portfolio:error_rating_opened'));
   //Forward
   if ($container instanceof ElggGroup) {
      forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
   } else {
      forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
   }

} else {

$description = get_input('description');
$allow_comments = get_input('allow_comments');
$tags = get_input('e_portfoliotags');
$access_id = get_input('access_id');
$selected_action = get_input('submit');

// Cache to the session
elgg_make_sticky_form('add_e_portfolio');

// Convert string of tags into a preformatted array
$tagarray = string_to_tag_array($tags);

$options = array('type_subtype_pairs' => array('object' => 'e_portfolio'), 'limit' => false, 'container_guid' => $container_guid,'owner_guid' => $user_guid);
$mine = elgg_get_entities_from_metadata($options);
if ($mine) {
   register_error(elgg_echo('e_portfolio:error_existing'));
   //Forward
   if ($container instanceof ElggGroup) {
      forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
   } else {
      forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
   }
}

// Initialise a new ElggObject
$e_portfolio = new ElggObject();
$e_portfolio->subtype = "e_portfolio";
$e_portfolio->owner_guid = $user_guid;
$e_portfolio->container_guid = $container_guid;
if ($container instanceof ElggGroup) {
   $e_portfolio->group_guid = $container_guid;
}
$e_portfolio->group_guid = $container_guid;
$e_portfolio->access_id = $access_id;
if ($container instanceof ElggGroup) {
   $title = elgg_echo(sprintf(elgg_echo('e_portfolio:title:user:group'),$user->name,$container->name));
} else {
   $title = elgg_echo(sprintf(elgg_echo('e_portfolio:title:user'),$user->name));
}
$e_portfolio->title = $title;
$e_portfolio->description = $description;
if (is_array($tagarray)) {
   $e_portfolio->tags = $tagarray;
}
$e_portfolio->allow_comments = $allow_comments;
$e_portfolio->time_updated = time();
if (!$e_portfolio->save()) {
   register_error(elgg_echo("e_portfolio:error_save"));
   if ($container instanceof ElggGroup) {
      forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
   } else {
      forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
   }
}

$e_portfoliopost = $e_portfolio->getGUID();

// Remove the e_portfolio post cache
elgg_clear_sticky_form('add_e_portfolio');

//System message
system_message(elgg_echo("e_portfolio:created"));

//Add to river
elgg_create_river_item(array(
   'view'=>'river/object/e_portfolio/create',
   'action_type'=>'create',
   'subject_guid'=>$user_guid,
   'object_guid'=>$e_portfoliopost,
));

//Forward
if (strcmp($selected_action,elgg_echo('e_portfolio:save'))==0) {
   forward(elgg_get_site_url() . 'e_portfolio/add_page/' . $e_portfoliopost);
} else {
   forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfoliopost);
}

}

?>