<?php

gatekeeper();
if (is_callable('group_gatekeeper'))
    group_gatekeeper();

$entity_guid = get_input('entity_guid');
$entity = get_entity($entity_guid);

if ($entity instanceof ElggObject && $entity->getSubtype() == 'e_portfolio') {
    $e_portfolio = $entity;
    $container_guid = $e_portfolio->container_guid;
    $container = get_entity($container_guid);
    $owner = $e_portfolio->getOwnerEntity();
    elgg_set_page_owner_guid($container_guid);

    if (elgg_instanceof($container, 'group')) {
        elgg_push_breadcrumb($container->name, "e_portfolio/group/$container->guid/all");
    } else {
        elgg_push_breadcrumb($container->name, "e_portfolio/owner/$container->username");
    }

    $title = $e_portfolio->title;
    elgg_push_breadcrumb($title);

    $content = elgg_view("object/e_portfolio", array('full_view' => true, 'entity' => $e_portfolio));
    $body = elgg_view_layout('content', array('filter' => '', 'content' => $content, 'title' => $title, 'sidebar' => elgg_view('e_portfolio/tree', array('e_portfolio' => $e_portfolio))));
} elseif ($entity instanceof ElggObject && $entity->getSubtype() == 'e_portfolio_page') {
    $e_portfolio_page = $entity;
    $e_portfolio = get_entity($e_portfolio_page->container_guid);
    $container_guid = $e_portfolio->container_guid;
    $container = get_entity($container_guid);
    $owner = $e_portfolio->getOwnerEntity();
    elgg_set_page_owner_guid($container_guid);

    if (elgg_instanceof($container, 'group')) {
        elgg_push_breadcrumb($container->name, "e_portfolio/group/$container->guid/all");
    } else {
        elgg_push_breadcrumb($container->name, "e_portfolio/owner/$container->username");
    }
    elgg_push_breadcrumb($e_portfolio->title, $e_portfolio->getURL());

    $title = $e_portfolio_page->title;

    $content = elgg_view("object/e_portfolio_page", array('full_view' => true, 'entity' => $e_portfolio_page));
    $body = elgg_view_layout('content', array('filter' => '', 'content' => $content, 'title' => $title, 'sidebar' => elgg_view('e_portfolio/tree', array('e_portfolio' => $e_portfolio))));
} else {
    register_error(elgg_echo('e_portfolio:notfound'));
    forward();
}

echo elgg_view_page($title, $body);
