<?php
                
gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$container_guid = get_input('container_guid');
$container = get_entity($container_guid);

$page_owner = $container;
if (elgg_instanceof($container, 'object')) {
   $page_owner = $container->getContainerEntity();
}
elgg_set_page_owner_guid($page_owner->getGUID());

elgg_push_breadcrumb(elgg_echo('e_portfolio:setup_group'));

$title = elgg_echo('e_portfolio:setupgrouppost');

$options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
$e_portfolio_group_setup = elgg_get_entities_from_metadata($options);

$content = elgg_view("forms/e_portfolio/setup_group", array('container_guid' => $container_guid, 'entity' => $e_portfolio_group_setup[0]));
$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);

		
?>