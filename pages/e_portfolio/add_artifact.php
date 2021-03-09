<?php
	
gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$e_portfolio_page_guid = get_input('e_portfolio_page_guid');	
$e_portfolio_page = get_entity($e_portfolio_page_guid);
$e_portfoliopost = $e_portfolio_page->container_guid;
$e_portfolio = get_entity($e_portfoliopost);

$artifact_type = get_input('artifact_type');
if (empty($artifact_type))
   $artifact_type = "simple";

$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

$page_owner = $container;
if (elgg_instanceof($container, 'object')) {
   $page_owner = $container->getContainerEntity();
}
elgg_set_page_owner_guid($page_owner->getGUID());

elgg_push_breadcrumb($e_portfolio->title, $e_portfolio->getURL());
elgg_push_breadcrumb($e_portfolio_page->title, $e_portfolio_page->getURL());
elgg_push_breadcrumb(elgg_echo('e_portfolio:add_artifact'));

$title = elgg_echo('e_portfolio:addartifactpost');
$content = elgg_view("forms/e_portfolio/add_artifact", array('entity' => $e_portfolio_page, 'artifact_type' => $artifact_type));
 

$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);
		
?>