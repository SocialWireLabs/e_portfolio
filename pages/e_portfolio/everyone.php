<?php

$title = elgg_echo('e_portfolio:all');
elgg_pop_breadcrumb();
elgg_push_breadcrumb(elgg_echo('e_portfolios'));

elgg_register_title_button();

$offset = get_input('offset');          
if (empty($offset)) {
   $offset = 0;
}               
$limit = 10;

$e_portfolios = elgg_get_entities(array('type'=>'object','subtype'=>'e_portfolio','limit'=>false,'order_by'=>'e.time_created desc'));

if (empty($e_portfolios)) {
   $num_e_portfolios=0;
} else {
   $num_e_portfolios=count($e_portfolios);
}

$k=0;
$item=$offset;
$e_portfolios_range=array();
while (($k<$limit)&&($item<$num_e_portfolios)){
   $e_portfolios_range[$k]=$e_portfolios[$item];
   $k=$k+1;
   $item=$item+1;
}
   
if ($num_e_portfolios>0){
   $vars=array('count'=>$num_e_portfolios,'limit'=>$limit,'offset'=>$offset,'full_view'=>false);
   $content .= elgg_view_entity_list($e_portfolios_range,$vars);
} else {
   $content = '<p>' . elgg_echo('e_portfolio:none') . '</p>';
}

$body = elgg_view_layout('content', array('filter_context' => 'all','content' => $content,'title' => $title));

echo elgg_view_page($title, $body);

?>