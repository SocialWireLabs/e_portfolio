<?php
	
gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$e_portfolio_artifact_guid = get_input('e_portfolio_artifact_guid');	
$e_portfolio_artifact = get_entity($e_portfolio_artifact_guid);
$e_portfolio_page_guid = $e_portfolio_artifact->container_guid;
$e_portfolio_page = get_entity($e_portfolio_page_guid);
$e_portfoliopost = $e_portfolio_page->container_guid;
$e_portfolio = get_entity($e_portfoliopost);

$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

$page_owner = $container;
if (elgg_instanceof($container, 'object')) {
   $page_owner = $container->getContainerEntity();
}
elgg_set_page_owner_guid($page_owner->getGUID());

elgg_push_breadcrumb($e_portfolio->title, $e_portfolio->getURL());
elgg_push_breadcrumb($e_portfolio_page->title, $e_portfolio_page->getURL());
elgg_push_breadcrumb(elgg_echo('e_portfolio:edit_artifact'));

$title = elgg_echo('e_portfolio:editartifactpost');
$content = elgg_view("forms/e_portfolio/edit_artifact", array('entity' => $e_portfolio_artifact));

$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);
		
?>