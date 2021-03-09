<?php

$full = elgg_extract('full_view', $vars, FALSE);
$e_portfolio = elgg_extract('entity', $vars, FALSE);

if (!$e_portfolio) {
   return TRUE;
}
$e_portfoliopost = $e_portfolio->getGUID();

$owner = $e_portfolio->getOwnerEntity();
$description = $e_portfolio->description;
$owner_icon = elgg_view_entity_icon($owner, 'tiny');
$owner_link = elgg_view('output/url', array('href' => $owner->getURL(),'text' => $owner->name,'is_trusted' => true));
$author_text = elgg_echo('byline', array($owner_link));
$tags = elgg_view('output/tags', array('tags' => $e_portfolio->tags));
$date = elgg_view_friendly_time($e_portfolio->time_created);

if ($e_portfolio->allow_comments) {
   $comments_count = $e_portfolio->countComments();
   //only display if there are commments
   if ($comments_count != 0) {
      $text = elgg_echo("comments") . " ($comments_count)";
   } else {
      $text = elgg_echo("comments");
   }
   $name_div=$e_portfoliopost;
   $comments_link.= "<p align=\"left\"><a onclick=\"e_portfolio_show_general_comments($name_div);\" style=\"cursor:hand;\">$text</a></p>";
   $comments_link .= "<div id=\"" . $name_div . "\" style=\"display:none;\">";
   $comments_link .= elgg_view_comments($e_portfolio);
   $comments_link .= "</div>";
} else {
   $comments_link = '';
}

$metadata = elgg_view_menu('entity', array('entity' => $e_portfolio,'handler' => 'e_portfolio','sort_by' => 'priority','class' => 'elgg-menu-hz'));
$subtitle = "$author_text $date $comments_link";

//////////////////////////////////////////////////
//E_Portfolio information

$owner_guid = $owner->getGUID();
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

///////////////////////////////////////////////////////////////////

//Links to actions    

$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);
if ($container instanceof ElggGroup) {
   $group_owner_guid = $container->owner_guid;
   $operator=false;
   if (($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$container_guid))){
      $operator=true;
   }
   $owner_operator=false;
   if (($group_owner_guid==$owner_guid)||(check_entity_relationship($owner_guid,'group_admin',$container_guid))){
      $owner_operator=true;
   }
}

$img_template = '<img border="0" width="20" height="20" alt="%s" title="%s" src="'.elgg_get_config('wwwroot').'mod/e_portfolio/graphics/%s" />';

if (($e_portfolio->canEdit())&&(($owner_guid==$user_guid)||(($container instanceof ElggGroup)&&($operator)&&($owner_operator)))) {
   if ($container instanceof ElggGroup) {       
      $open_msg = elgg_echo('e_portfolio:open');
      $close_msg = elgg_echo('e_portfolio:close');
      $img_open = sprintf($img_template,$close_msg,$close_msg,"eye_open.png");
      $img_close = sprintf($img_template,$open_msg,$open_msg,"eye_close.png");
      if ($e_portfolio->access_id == $container->group_acl) {
         //Close
         $url_open_close = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/close?e_portfoliopost=" . $e_portfoliopost);
         $word_open_close = $img_open;
      } else {  
         //Open
         $url_open_close = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/open?e_portfoliopost=" . $e_portfoliopost);
         $word_open_close = $img_close;     
      }
   }
   //Add page
   $url_add_page = elgg_add_action_tokens_to_url(elgg_get_site_url() . "e_portfolio/add_page/$e_portfoliopost");
   $word_add_page = elgg_echo("e_portfolio:add_page");  
   //Import pages
   $url_import_pages = elgg_add_action_tokens_to_url(elgg_get_site_url() . "e_portfolio/import_pages/$e_portfoliopost");
   $word_import_pages = elgg_echo("e_portfolio:import_pages");     
}
if (($e_portfolio->canEdit())&&($container instanceof ElggGroup)&&($operator)&&($owner_guid!=$user_guid)&&(!$owner_operator)) {
   $url_delete = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/delete?guid=" . $e_portfoliopost);
   $word_delete = elgg_echo("delete");
   $delete_confirm_msg = elgg_echo('e_portfolio:delete_confirm');
}

if ($full) {
   $body = "";

   //Links to actions
   if (($e_portfolio->canEdit())&&(($owner_guid==$user_guid)||(($container instanceof ElggGroup)&&($operator)&&($owner_operator)))) {
      if ($container instanceof ElggGroup) {   
         $body .= "<a href=\"{$url_open_close}\">{$word_open_close}</a>" . " ";
      }
      $body .= "<a href=\"{$url_add_page}\">{$word_add_page}</a>" . " " . "<a href=\"{$url_import_pages}\">{$word_import_pages}</a>" . " ";
   }
   if (($e_portfolio->canEdit())&&($container instanceof ElggGroup)&&($operator)&&($owner_guid!=$user_guid)&&(!$owner_operator)) {
      $body .= "<a onclick=\"return confirm('$delete_confirm_msg')\" href=\"{$url_delete}\">{$word_delete}</a>";
   }

   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'metadata_case_sensitive' => false,'limit' => false, 'container_guid' => $e_portfolio->getGUID(), 'order_by_metadata' => array('name' => 'page_number', 'direction' => 'asc', 'as'=>'integer'));
   $pages = elgg_get_entities_from_metadata($options);

   $num_pages = count($pages);

   if ($container instanceof ElggGroup) {

      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
      $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
      $e_portfolio_group_setup = $e_portfolio_group_setup[0];
      if (($e_portfolio_group_setup)&&(!$e_portfolio_group_setup->var_pages)&&($e_portfolio_group_setup->num_pages<$num_pages))
         $num_pages = $e_portfolio_group_setup->num_pages;
   }

   //Move pages
   if (($e_portfolio->canEdit())&&(($owner_guid==$user_guid)||(($container instanceof ElggGroup)&&($operator)&&($owner_operator)))) {
      $text = elgg_echo("e_portfolio:move_pages");
      $move_pages_link = "<p align=\"left\"><a onclick=\"e_portfolio_move_pages();\" style=\"cursor:hand;\">$text</a></p>";
      $move_pages_link .= "<div id=\"movepagesDiv\" style=\"display:none;\">";
      $move_pages_link .= elgg_view('e_portfolio/e_portfolio_pages_table', array('entity' => $e_portfolio,'num_pages' => $num_pages));
      $move_pages_link .= "<br></div>";
      $body .= $move_pages_link;
   }

   $i=0;
   $active_pages = array();
   foreach ($pages as $one_page) {
      $active_pages[$i] = $one_page;
      $i=$i+1;
      if ($i==$num_pages)
         break;
   }

   $body .= elgg_view('output/longtext', array('value' => $description));

   $vars_array =  array('count' => $num_pages, 'limit' => null, 'full_view' => false, 'pagination' => false);
   $body .= elgg_view_entity_list($active_pages, $vars_array);

    if ($container instanceof ElggGroup) {

      if (($e_portfolio_group_setup)&&(strcmp($e_portfolio_group_setup->rating_type,'e_portfolio_rating_type_marks')==0)&&($e_portfolio_group_setup->var_pages)) {

        if (($operator) || ($user_guid==$owner_guid) || ($e_portfolio_group_setup->public_global_marks)) {

         $body .= "<br>";
         $body .= "<div class=\"task_frame_red\">";
         $body .= "<b>" . elgg_echo('e_portfolio:rating_label') . "</b><br><br>";

	 if (strcmp($e_portfolio_group_setup->type_mark,'e_portfolio_type_mark_numerical')==0){
            $max_rating_label = elgg_echo("e_portfolio:max_mark_label");
            $max_rating = number_format($e_portfolio_group_setup->max_mark,2);
	    $body .= $max_rating_label . ": " . $max_rating . "<br>";
         }

	 $rating_label = elgg_echo('e_portfolio:mark');
	 $total_rating = "not_qualified";
	 foreach ($pages as $page) {
	    $rating = $page->rating;
	    if (strcmp($rating,"not_qualified")!=0){
	       if (strcmp($total_rating,"not_qualified")!=0) {
	          $total_rating = $total_rating + $rating;
	       } else {
	          $total_rating = $rating;
	       }
	    }
	 }
	 if (strcmp($total_rating,"not_qualified")!=0){
	    $count_pages = count($pages);
	    $total_rating = ($total_rating * 1.0) / $count_pages;
            $rating_output=e_portfolio_rating_output($e_portfolio_group_setup,$total_rating);
            $body .= $rating_label . ": " . $rating_output;
         } else {
            $body .= $rating_label . ": " . elgg_echo('e_portfolio:not_rated');
         }   

	 $body .= "<br>";
         //Assign marks
	 if (($operator) && ($e_portfolio_group_setup->qualify_opened)){
            $url_assign_marks=elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/assign_marks?task_guid=" . $e_portfolio_group_setup->getGUID() . "&rating=" . $total_rating . "&mark_type=" . $e_portfolio_group_setup->mark_type . "&page_number=-1" . "&owner_guid=" . $owner_guid);
            $text_assign_marks=elgg_echo("e_portfolio:assign_marks");
            $link_assign_marks="<a href=\"{$url_assign_marks}\">{$text_assign_marks}</a>";
            $body .= $link_assign_marks;
         } 
         $body .= "</div><br>";
	 }
      }
   }

   if (($container instanceof ElggGroup)&&($operator)&&(!$owner_operator))
      $metadata = "";
   $params = array('entity' => $e_portfolio,'title' => $title,'metadata' => $metadata,'subtitle' => $subtitle,'tags' => $tags);
   $params = $params + $vars;
   $summary = elgg_view('object/elements/summary', $params);
   
   echo elgg_view('object/elements/full', array('summary' => $summary,'icon' => $owner_icon,'body' => $body));

} else {

   if (($container instanceof ElggGroup)&&($operator)&&(!$owner_operator))
      $metadata = "";
   $params = array('entity' => $e_portfolio,'title' => $title,'metadata' => $metadata,'subtitle' => $subtitle,'tags' => $tags,'content' => "");
   $params = $params + $vars;
   $list_body = elgg_view('object/elements/summary', $params);

   $body = "";
   //Links to actions
   if (($e_portfolio->canEdit())&&(($owner_guid==$user_guid)||(($container instanceof ElggGroup)&&($operator)&&($owner_operator)))) {
      if ($container instanceof ElggGroup) {        
         $body .= "<a href=\"{$url_open_close}\">{$word_open_close}</a>" . " ";
      }
      $body .= "<a href=\"{$url_add_page}\">{$word_add_page}</a>" . " " . "<a href=\"{$url_import_pages}\">{$word_import_pages}</a>" . " ";
   }

   if (($e_portfolio->canEdit())&&($container instanceof ElggGroup)&&($operator)&&($owner_guid!=$user_guid)||(!$owner_operator)) {
      $body .= "<a onclick=\"return confirm('$delete_confirm_msg')\" href=\"{$url_delete}\">{$word_delete}</a>";
   }

   $body .= elgg_view('output/longtext', array('value' => $description));

   $list_body .= $body;

   echo elgg_view_image_block($owner_icon, $list_body);
}

?>

<script type="text/javascript">
   function e_portfolio_show_general_comments(name_div){
      var commentsDiv = document.getElementById(name_div);
      if (commentsDiv.style.display == 'none'){
         commentsDiv.style.display = 'block';
      } else {       
         commentsDiv.style.display = 'none';
      }
   }    
   function e_portfolio_move_pages(){
      var movepagesDiv = document.getElementById('movepagesDiv');
      if (movepagesDiv.style.display == 'none'){
         movepagesDiv.style.display = 'block';
      } else {       
         movepagesDiv.style.display = 'none';
      }
   }    
</script>
