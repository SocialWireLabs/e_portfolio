<?php

gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();

$owner = elgg_get_page_owner_entity();
if (!$owner) {
   forward('e_portfolio/all');
}
$owner_guid = $owner->getGUID();
$group_owner_guid = $owner->owner_guid;

if (!elgg_instanceof($owner, 'object')) {
   $group_guid = $owner_guid;
   $group_owner_guid = $owner->owner_guid; 
   $operator=false;
   if (($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
      $operator=true;
   }	
   if ($operator)
      elgg_register_title_button('e_portfolio','setup_group');
}

elgg_push_breadcrumb($owner->name);

elgg_register_title_button('e_portfolio','add');

if (!elgg_instanceof($owner, 'object')) {

   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $owner_guid);
   $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
   $e_portfolio_group_setup = $e_portfolio_group_setup[0];
   if ($e_portfolio_group_setup)
      $e_portfolio_group_setup_guid = $e_portfolio_group_setup->getGUID();

   if (($operator)&&($e_portfolio_group_setup)) {
      $url = elgg_get_site_url();
      $url_qualify_open_close = elgg_add_action_tokens_to_url($url . "action/e_portfolio/open_close_qualify?e_portfolio_group_setup_guid=" . $e_portfolio_group_setup_guid);
      if ($e_portfolio_group_setup->qualify_opened) 
         $word_qualify_open_close = elgg_echo('e_portfolio:rating_close');   
      else
         $word_qualify_open_close = elgg_echo('e_portfolio:rating_open');
   }
   $content .= "<p><a href=\"{$url_qualify_open_close}\">{$word_qualify_open_close}</a></p>";
   if (($e_portfolio_group_setup)&&(strcmp($e_portfolio_group_setup->rating_type,'e_portfolio_rating_type_marks')==0)&&(($operator)||($e_portfolio_group_setup->public_global_marks))) {
      $text = elgg_echo('item:object:socialwire_mark');
      elgg_register_menu_item('title', array('name' => 'socialwire_marks_title','text' => $text,'href' => "e_portfolio/marks/$e_portfolio_group_setup_guid/all",'link_class' => 'elgg-button elgg-button-action'));
   }
   if ((!$e_portfolio_group_setup) && ($operator)) {
      $content .= '<p>' . elgg_echo('e_portfolio:to_qualify_configure') . '</p>';
      $content .= "<br>";
   } 
}

$offset = get_input('offset');
if (empty($offset)) {
   $offset = 0;
}
$limit = 10;

$e_portfolios = elgg_get_entities(array('type'=>'object','subtype'=>'e_portfolio','limit'=>false,'container_guid'=>$owner_guid,'order_by'=>'e.time_created desc'));

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
   $content .= '<p>' . elgg_echo('e_portfolio:none') . '</p>';
}

$title = elgg_echo('e_portfolio:owner', array($owner->name));

$filter_context = '';
if ($owner_guid == $user_guid) {
   $filter_context = 'mine';
}
					
$params = array('filter_context' => $filter_context,'content' => $content,'title' => $title);

if (elgg_instanceof($owner, 'group')) {
   $params['filter'] = '';
}

$body = elgg_view_layout('content', $params);
echo elgg_view_page($title, $body);
		
?>