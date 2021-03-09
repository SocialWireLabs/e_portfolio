<?php

elgg_load_library('e_portfolio');

$full = elgg_extract('full_view', $vars, FALSE);
$e_portfolio_page = elgg_extract('entity', $vars, FALSE);
$e_portfolio_page_guid = $e_portfolio_page->getGUID();
$page_number = $e_portfolio_page->page_number;

if (!$e_portfolio_page) {
   return TRUE;
}

$owner = $e_portfolio_page->getOwnerEntity();
$owner_icon = elgg_view_entity_icon($owner, 'tiny');
$owner_link = elgg_view('output/url', array('href' => $owner->getURL(),'text' => $owner->name,'is_trusted' => true));
$author_text = elgg_echo('byline', array($owner_link));
$tags = elgg_view('output/tags', array('tags' => $e_portfolio_page->tags));
$date = elgg_view_friendly_time($e_portfolio_page->time_created);

if ($e_portfolio_page->allow_comments) {
   $comments_count = $e_portfolio_page->countComments();
   //only display if there are commments
   if ($comments_count != 0) {
      $text = elgg_echo("comments") . " ($comments_count)";
   } else {
      $text = elgg_echo("comments");
   }
   $name_div = $e_portfolio_page_guid;
   $comments_link.= "<p align=\"left\"><a onclick=\"e_portfolio_page_show_general_comments($name_div);\" style=\"cursor:hand;\">$text</a></p>";
   $comments_link .= "<div id=\"" . $name_div . "\" style=\"display:none;\">";
   $comments_link .= elgg_view_comments($e_portfolio_page);
   $comments_link .= "</div>";
} else {
   $comments_link = '';
}

$metadata = elgg_view_menu('entity', array('entity' => $e_portfolio_page,'handler' => 'e_portfolio','sort_by' => 'priority','class' => 'elgg-menu-hz'));
$subtitle = "$author_text $date $comments_link";

//////////////////////////////////////////////////
//E_Portfolio information

$owner_guid = $owner->getGUID();
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);
$e_portfolio = get_entity($e_portfolio_page->container_guid);
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

///////////////////////////////////////////////////////////////////
//Links to actions      

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

if (($e_portfolio_page->canEdit())&&(($owner_guid==$user_guid)||(($container instanceof ElggGroup)&&($operator)&&($owner_operator)))) { 
   if ($container instanceof ElggGroup) {        
         $open_msg = elgg_echo('e_portfolio:open_page');
	 $close_msg = elgg_echo('e_portfolio:close_page');
         $img_open = sprintf($img_template,$close_msg,$close_msg,"eye_open.png");
	 $img_close = sprintf($img_template,$open_msg,$open_msg,"eye_close.png");
      if ($e_portfolio_page->access_id == $container->group_acl) {
         //Close page
         $url_open_close_page = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/close_page?page_guid=" . $e_portfolio_page_guid);
         $word_open_close_page = $img_open;
      } else {  
         //Open page
         $url_open_close_page = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/open_page?page_guid=" . $e_portfolio_page_guid);
         $word_open_close_page = $img_close;     
      }
   }
   //Add artifact
   $url_add_artifact = elgg_add_action_tokens_to_url(elgg_get_site_url() . "e_portfolio/add_artifact/$e_portfolio_page_guid");
   $word_add_artifact = elgg_echo("e_portfolio:add_artifact"); 
}

if ($full) {

   $body = "";

   //Links to actions
   if (($e_portfolio_page->canEdit())&&(($owner_guid==$user_guid)||(($container instanceof ElggGroup)&&($operator)&&($owner_operator)))) { 
      if ($container instanceof ElggGroup) {   
         $body .= "<a href=\"{$url_open_close_page}\">{$word_open_close_page}</a>" . " ";
      }
      $body .= "<a href=\"{$url_add_artifact}\">{$word_add_artifact}</a>";
   }

   //Move artifacts
   if (($e_portfolio_page->canEdit())&&(($owner_guid==$user_guid)||(($container instanceof ElggGroup)&&($operator)&&($owner_operator)))) {
      $text = elgg_echo("e_portfolio:move_artifacts");
      $move_artifacts_link = "<p align=\"left\"><a onclick=\"e_portfolio_move_artifacts();\" style=\"cursor:hand;\">$text</a></p>";
      $move_artifacts_link .= "<div id=\"moveartifactsDiv\" style=\"display:none;\">";
      $move_artifacts_link .= elgg_view('e_portfolio/e_portfolio_artifacts_table', array('entity' => $e_portfolio_page));
      $move_artifacts_link .= "<br></div>";
      $body .= $move_artifacts_link;
   }

   $body .= "<br>";

   $skills = $e_portfolio_page->skills;
   if (strcmp($skills,"")!=0) {
      $body .= "<div class=\"task_frame_green\">";
      $body .= "<b>" . elgg_echo('e_portfolio:skills') . "</b><br><br>";
      $body .= "<div class=\"e_portfolio_frame\">";
      $body .= elgg_view('output/longtext', array('value' => $skills));
      $body .= "</div>";
      $body .= "</div>";
      $body .= "<br>";
   }

   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'metadata_case_sensitive' => false,'limit' => false, 'container_guid' => $e_portfolio_page->getGUID(), 'order_by_metadata' => array('name' => 'artifact_number', 'direction' => 'asc', 'as'=>'integer'));
   $artifacts = elgg_get_entities_from_metadata($options);

   if ($artifacts) {
      $body .= "<b>" . elgg_echo('e_portfolio:artifacts') . "</b><br><br>";

      $edit_msg = elgg_echo("edit");
      $delete_msg = elgg_echo("delete");
      $img_edit = sprintf($img_template,$edit_msg,$edit_msg,"edit.jpeg"); 
      $img_delete = sprintf($img_template,$delete_msg,$delete_msg,"delete.jpeg"); 
     
      foreach ($artifacts as $artifact) {

         $artifact_guid = $artifact->getGUID();

	 $artifact_body = "";

	 if (($e_portfolio_page->canEdit())&&(($owner_guid==$user_guid)||(($container instanceof ElggGroup)&&($operator)&&($owner_operator)))) { 
            $url_edit_artifact = elgg_add_action_tokens_to_url(elgg_get_site_url() . "e_portfolio/edit/" . $artifact_guid);
	    $url_delete_artifact = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/delete?guid=" . $artifact_guid);
	    $artifact_body = "<a href=\"{$url_edit_artifact}\">{$img_edit}</a>  <a href=\"{$url_delete_artifact}\">{$img_delete}</a> ";
	 }

	 $artifact_body .= "<b>" . elgg_view('output/text', array('value' => $artifact->title)). "</b>";
         if ($artifact->description){
            $artifact_body .= elgg_view('output/longtext', array('value' => $artifact->description));
            if ($artifact->embed){
               $artifact_body .= "<br>";
               $artifact_body .= elgg_view('e_portfolio/embed_code',array('entity' => $artifact));
            }
         }
	 if (strcmp($artifact->artifact_type,"simple")!=0)
            $artifact_body .= "<br>";
         switch ($artifact->artifact_type) {
            case 'video':
               $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $artifact_guid,'inverse_relationship' => FALSE,'types' => 'object','subtypes' => 'izap_videos','limit'=>0));
               if ($files) {
                  $artifact_body .= elgg_view('e_portfolio/video', array('video' => $files[0]));
               }
               break;
            case 'audio':
               $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $artifact_guid,'inverse_relationship' => FALSE,'types' => 'object','subtypes' => 'e_portfolio_file','limit'=>0));
               $artifact_body .= "<script type=\"text/javascript\" src=\"".elgg_get_site_url()."mod/zaudio/audioplayer/audio-player.js\"></script><script type=\"text/javascript\">AudioPlayer.setup(\"".elgg_get_site_url()."mod/zaudio/audioplayer/player.swf\", { width: 290});</script><div style=\"margin:10px 0 10px 10px;\"><p id=\"audioplayer_".$files[0]->guid."\">Alternative content</p><script type=\"text/javascript\">AudioPlayer.embed(\"audioplayer_".$files[0]->guid."\", {soundFile: \"".elgg_get_site_url()."mod/e_portfolio/download.php?e_portfolio_file_guid=".$files[0]->guid."\"});</script></div>";
               break;
            case 'image':
               $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $artifact_guid,'inverse_relationship' => FALSE,'types' => 'object','subtypes' => 'e_portfolio_file','limit'=>0));
               $artifact_body .= "<div class=\"image_view\"><img src=\"".elgg_get_site_url()."mod/e_portfolio/download.php?e_portfolio_file_guid=".$files[0]->guid."\" /></div>";
               break;
            case 'urls_files':
               $urls = explode(Chr(26),$artifact->urls);
               $urls = array_map('trim',$urls);
               if ((count($urls)>0)&&(strcmp($urls[0],"")!=0)) {
                  foreach ($urls as $one_url){  
                     $comp_url = explode(Chr(24),$one_url);
                     $comp_url = array_map('trim',$comp_url);
                     $url_name = $comp_url[0];
                     $url_value = $comp_url[1];
                     $artifact_body .= "<a rel=\"nofollow\" href=\"$url_value\" target=\"_blank\">$url_name</a><br>";
                  }
               }
               $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $artifact_guid,'inverse_relationship' => FALSE,'types' => 'object','subtypes' => 'e_portfolio_file'));
               foreach($files as $file) {
	          $artifact_body .= "<a href=\"".elgg_get_site_url()."mod/e_portfolio/download.php?e_portfolio_file_guid=".$file->guid."\">".$file->title."</a></br>";
              }
              break;
         }
         $body .= $artifact_body;
	 $body .= "<br>";
      }
      
      $body .= "<br>";
   }

   $reflections = $e_portfolio_page->reflections;
   if (strcmp($reflections,"")!=0) {
      $body .= "<div class=\"task_frame_green\">";
      $body .= "<b>" . elgg_echo('e_portfolio:reflections') . "</b><br><br>";
      $body .= "<div class=\"e_portfolio_frame\">";
      $body .= elgg_view('output/longtext', array('value' => $reflections));
      $body .= "</div>";
      $body .= "</div><br>";
   }
  
   if ($container instanceof ElggGroup) {

      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
      $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
      $e_portfolio_group_setup = $e_portfolio_group_setup[0];

      if ($e_portfolio_group_setup) {

         if (($operator) || ($user_guid==$owner_guid) || (($e_portfolio_group_setup->public_global_marks)&&(strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0)) || (strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')!=0)) {

         $body .= "<div class=\"task_frame_red\">";
      
         $body .= "<b>" . elgg_echo('e_portfolio:rating_label') . "</b><br><br>";

         if ((strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($e_portfolio_page->type_mark,'e_portfolio_type_mark_numerical')==0)){
            $max_rating_label = elgg_echo("e_portfolio:max_mark_label");
            $max_rating = number_format($e_portfolio_page->max_mark,2);
         } elseif ((strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')!=0)&&($e_portfolio_page->use_rubric)){
            $max_rating_label = elgg_echo("e_portfolio:max_game_points_label");
            $max_rating = $e_portfolio_page->max_game_points;
         }

         if (strcmp($e_portfolio_page->rating_type,"e_portfolio_rating_type_marks")==0) {
            $rating_label = elgg_echo('e_portfolio:mark');
         } else {
	    $rating_label = elgg_echo('e_portfolio:game_points');
         }
         if ($e_portfolio_page->use_rubric) {
            $rating_rubric = socialwire_rubric_get_rating($owner_guid, $e_portfolio_page_guid);
            $rubric = get_entity($e_portfolio_page->rubric_guid);
            if (($operator) && ($e_portfolio_group_setup->qualify_opened)){
               if (!$rating_rubric)
                  $view_type = 'rate';
               else
                  $view_type = 'edit_rated';
            } else {
               if (!$rating_rubric)
                  $view_type = 'show';
               else
                  $view_type = 'rated';
            }
	 
            $body .= elgg_view('rubric/show_rubric', array('entity' => $rubric, 'view_type' => $view_type, 'url' => elgg_get_site_url(), 'student_guid' => $owner_guid, 'task_guid' => $e_portfolio_page_guid, 'rating' => $rating_rubric,'container_guid' => $container_guid));
	    if (((strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($e_portfolio_page->type_mark,'e_portfolio_type_mark_numerical')==0))||(strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')!=0)){
	       $body .= $max_rating_label . ": " . $max_rating;
	       $body .= "<br>";
            } 
	    $rating = $e_portfolio_page->rating;
	    if (strcmp($rating,"not_qualified")!=0){
               $rating_output=e_portfolio_rating_output($e_portfolio_page,$rating);
               $body .= $rating_label . ": " . $rating_output;
            } else {
               $body .= $rating_label . ": " . elgg_echo('e_portfolio:page_not_rated');
            }    
	    if (($operator) && ($e_portfolio_group_setup->qualify_opened)){
	       $rate_page_form = elgg_view('input/hidden', array('name' => 'e_portfolio_page_guid', 'value' => $e_portfolio_page_guid));
	       $rate_page_form .= elgg_view("input/submit", array('value' => elgg_echo('e_portfolio:rate_page')));
	       $body .= "<br><br>";
          $vars_url = elgg_get_site_url();
          $body .= elgg_view('input/form', array('action' => "{$vars_url}action/e_portfolio/rate_page", 'body' => $rate_page_form, 'name' => 'rate_page_e_portfolio', 'enctype' => 'multipart/form-data'));
	    }
         } else {
            if ((strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($e_portfolio_page->type_mark,'e_portfolio_type_mark_numerical')==0)){
	       $body .= $max_rating_label . ": " . $max_rating;
	       $body .= "<br>";
            } 
	    if (!elgg_is_sticky_form('rate_page_e_portfolio')) {
               $rating = $e_portfolio_page->rating;
	    } else {
	       $rating = elgg_get_sticky_value('rate_page_e_portfolio','rating');
	    }
            if (($operator) && ($e_portfolio_group_setup->qualify_opened)){
	       elgg_clear_sticky_form('rate_page_e_portfolio'); 
	       $rate_page_form = $rating_label . ": ";
	       if (strcmp($rating,"not_qualified")==0)   
	          $rating = "";
	       $rating_input = e_portfolio_rating_input($e_portfolio_page,$rating,"rating");
               $rate_page_form .= $rating_input;
	       $rate_page_form .= "<br><br>";
	       $rate_page_form .= elgg_view('input/hidden', array('name' => 'e_portfolio_page_guid', 'value' => $e_portfolio_page_guid));
	       $rate_page_form .= elgg_view("input/submit", array('value' => elgg_echo('e_portfolio:rate_page')));
          $vars_url = elgg_get_site_url();
          $body .= elgg_view('input/form', array('action' => "{$vars_url}action/e_portfolio/rate_page", 'body' => $rate_page_form, 'name' => 'rate_page_e_portfolio', 'enctype' => 'multipart/form-data'));
	    } else {
	       if (strcmp($rating,"not_qualified")!=0){
                  $rating_output=e_portfolio_rating_output($e_portfolio_page,$rating);
                  $body .= $rating_label . ": " . $rating_output;
               } else {
                  $body .= $rating_label . ": " . elgg_echo('e_portfolio:page_not_rated');
               }   
	    }
         }

          if (($operator) && ($e_portfolio_group_setup->qualify_opened)){
            $body .= "<br>";
            //Assign marks or game points
            if ((strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0)&&(!$e_portfolio_page->var_pages)){
               $url_assign_marks=elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/assign_marks?task_guid=" . $e_portfolio_group_setup->getGUID() . "&rating=" . $rating . "&mark_type=" . $e_portfolio_page->mark_type . "&page_number=" . $e_portfolio_page->page_number . "&owner_guid=" . $owner_guid);
               $text_assign_marks=elgg_echo("e_portfolio:assign_marks");
               $link_assign_marks="<a href=\"{$url_assign_marks}\">{$text_assign_marks}</a>";
               $body .= $link_assign_marks;
            } elseif (strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_game_points')==0) {
               $url_assign_game_points=elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/assign_game_points?e_portfolio_page_guid=" . $e_portfolio_page_guid);
               $text_assign_game_points=elgg_echo("e_portfolio:assign_game_points");
               $link_assign_game_points="<a href=\"{$url_assign_game_points}\">{$text_assign_game_points}</a>";
               $body .= $link_assign_game_points;
            }
         }
         $body .= "</div><br>";
	 }
      }
   }

   //"Paginación"
   $previous_next = "<br />";
   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'metadata_case_sensitive' => false,'limit' => false, 'container_guid' => $e_portfolio->getGUID(), 'count' => true);
   $count_pages = elgg_get_entities_from_metadata($options);
   
   //botón anterior
   if ($page_number != 1) {
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'metadata_case_sensitive' => false,'limit' => false, 'container_guid' => $e_portfolio->getGUID(),'metadata_name_value_pairs' => array('name' => 'page_number', 'value' => $page_number-1));
      $e_portfolio_previous_page = elgg_get_entities_from_metadata($options);
      $e_portfolio_previous_page = $e_portfolio_previous_page[0];
      if ($e_portfolio_previous_page)
         $previous_next .= elgg_view('output/url', array('href' => $e_portfolio_previous_page->getURL(),'text' => elgg_echo('e_portfolio:previous_page'))).'&nbsp;&nbsp;';
   }

   //botón siguiente
   if ($page_number != $count_pages){
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'metadata_case_sensitive' => false,'limit' => false, 'container_guid' => $e_portfolio->getGUID(),'metadata_name_value_pairs' => array('name' => 'page_number', 'value' => $page_number+1));
      $e_portfolio_next_page = elgg_get_entities_from_metadata($options);
      $e_portfolio_next_page = $e_portfolio_next_page[0];
      if ($e_portfolio_next_page)
         $previous_next .= elgg_view('output/url', array('href' => $e_portfolio_next_page->getURL(), 'text' => elgg_echo('e_portfolio:next_page')));
   }
   $body .= $previous_next."<br /><br />";
  
   if (($container instanceof ElggGroup)&&($operator)&&(!$owner_operator))
      $metadata = "";
   $params = array('entity' => $e_portfolio_page,'title' => $title,'metadata' => $metadata,'subtitle' => $subtitle,'tags' => $tags);
   $params = $params + $vars;
   $summary = elgg_view('object/elements/summary', $params);
   
   echo elgg_view('object/elements/full', array('summary' => $summary,'icon' => $owner_icon,'body' => $body));

} else {
   if (($container instanceof ElggGroup)&&($operator)&&(!$owner_operator))
      $metadata = "";
   $params = array('entity' => $e_portfolio_page,'title' => $title,'metadata' => $metadata,'subtitle' => $subtitle,'tags' => $tags,'content' => $description);
   $params = $params + $vars;
   $list_body = elgg_view('object/elements/summary', $params);

   $body = "";
   //Links to actions
   if (($e_portfolio_page->canEdit())&&(($owner_guid==$user_guid)||(($container instanceof ElggGroup)&&($operator)&&($owner_operator)))) {
      if ($container instanceof ElggGroup) {        
         $body .= "<a href=\"{$url_open_close_page}\">{$word_open_close_page}</a>" . " ";
      }
      $body .= "<a href=\"{$url_add_artifact}\">{$word_add_artifact}</a>";
   }

   $list_body .= $body;

   echo elgg_view_image_block($owner_icon, $list_body);
}

?>

<script type="text/javascript">
   function e_portfolio_page_show_general_comments(name_div){
      var commentsDiv = document.getElementById(name_div);
      if (commentsDiv.style.display == 'none'){
         commentsDiv.style.display = 'block';
      } else {       
         commentsDiv.style.display = 'none';
      }
   }    
   function e_portfolio_move_artifacts(){
      var moveartifactsDiv = document.getElementById('moveartifactsDiv');
      if (moveartifactsDiv.style.display == 'none'){
         moveartifactsDiv.style.display = 'block';
      } else {       
         moveartifactsDiv.style.display = 'none';
      }
   }    
</script>









