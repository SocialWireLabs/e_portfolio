<?php
                
gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$e_portfolio_group_setup_guid = get_input('e_portfolio_group_setup_guid');
$e_portfolio_group_setup = get_entity($e_portfolio_group_setup_guid);
$page_number = get_input('page_number');

elgg_push_breadcrumb(elgg_echo('item:object:socialwire_mark'));

if ($page_number == "all") {
   $title = elgg_echo('item:object:socialwire_mark') . " (" . elgg_echo("e_portfolio") .")";
} else {
  $title = elgg_echo('item:object:socialwire_mark') . " (" . elgg_echo("e_portfolio") ." (#$page_number))";
}

$content .= elgg_view("e_portfolio/marks", array('entity' => $e_portfolio_group_setup, 'page_number' => $page_number));

$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);

		
?>