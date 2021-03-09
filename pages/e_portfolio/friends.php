<?php

$owner = elgg_get_page_owner_entity();
if (!$owner) {
   forward('e_portfolio/all');
}

elgg_push_breadcrumb($owner->name, "e_portfolio/owner/$owner->username");
elgg_push_breadcrumb(elgg_echo('friends'));

elgg_register_title_button();

$offset = get_input('offset');          
if (empty($offset)) {
   $offset = 0;
}
$limit = 10;
                
$e_portfolios = elgg_get_entities_from_relationship(array(
   'type'=>'object',
   'subtype'=>'e_portfolio',
   'limit'=>false,
   'offset'=>0,
   'relationship'=>'friend',
   'relationship_guid'=>$owner->getGUID(),
   'relationship_join_on'=>'container_guid'
));


if ($e_portfolios) {
   $num_e_portfolios = count($e_portfolios);
} else {
   $num_e_portfolios = 0;
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
   $content = elgg_echo('e_portfolio:none');
}

$title = elgg_echo('e_portfolio:user:friends',array($owner->name));

$params = array('filter_context' => 'friends','content' => $content,'title' => $title);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);

?>