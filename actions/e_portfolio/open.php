<?php

gatekeeper();

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
   } else {
	
      $container = get_entity($e_portfolio->container_guid);
      $access_id = $container->group_acl;
      $e_portfolio->access_id = $access_id;
      if (!$e_portfolio->save()) {
         register_error(elgg_echo("e_portfolio:error_save"));
      }  else {
         system_message(elgg_echo("e_portfolio:opened"));     
      }
   }
}
forward($_SERVER['HTTP_REFERER']);
?>