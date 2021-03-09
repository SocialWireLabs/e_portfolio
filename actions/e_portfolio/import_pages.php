<?php

gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$e_portfoliopost = get_input('e_portfoliopost');
$e_portfolio = get_entity($e_portfoliopost);
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

if ($container instanceof ElggGroup) {
   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
   $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
   $e_portfolio_group_setup = $e_portfolio_group_setup[0];
}

if (($e_portfolio_group_setup) && ($e_portfolio_group_setup->qualify_opened)) {

   register_error(elgg_echo('e_portfolio:error_rating_opened'));
   //Forward
   forward($_SERVER['HTTP_REFERER']);

} else {

$access_id = get_input('access_id');
$selected_pages_guids = get_input('selected_pages');

if ($selected_pages_guids){
    
   $new_page = array();
   $file=array();
   $izap_videos = array();

   foreach ($selected_pages_guids as $page_guid) {
     
      $page = get_entity($page_guid);
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'limit' => false, 'count' => true, 'container_guid' => $e_portfoliopost);
      $count_pages = elgg_get_entities_from_metadata($options);

      if (($e_portfolio_group_setup) && (!$e_portfolio_group_setup->var_pages) && ($count_pages == $e_portfolio_group_setup->num_pages)) {
         register_error(elgg_echo('e_portfolio:error_num_pages_exceeded'));
         //Forward
         forward($_SERVER['HTTP_REFERER']);
      }

      $new_page[$page_guid] = new ElggObject();
      $new_page[$page_guid]->subtype = "e_portfolio_page";
      $new_page[$page_guid]->owner_guid = $user_guid;
      $new_page[$page_guid]->container_guid = $e_portfoliopost;
      if ($container instanceof ElggGroup) {
         $new_page[$page_guid]->group_guid = $container_guid;
      }
      $new_page[$page_guid]->access_id = $access_id;
      $page_number = $count_pages + 1;
      $new_page[$page_guid]->page_number = $page_number;
      
      $new_page[$page_guid]->title = $page->title;
      $new_page[$page_guid]->skills = $page->skills;
      $new_page[$page_guid]->reflections = $page->reflections;
      $new_page[$page_guid]->allow_comments = $page->allow_comments;

      $new_page[$page_guid]->group_guid = $e_portfolio->container_guid;

      if (($e_portfolio_group_setup) && (((!$e_portfolio_group_setup->var_pages) && ($page_number<=$e_portfolio_group_setup->num_pages)) || ($e_portfolio_group_setup->var_pages)) ) {
         $new_page[$page_guid]->var_pages = $e_portfolio_group_setup->var_pages;
	 $new_page[$page_guid]->rating_type = $e_portfolio_group_setup->rating_type;
	 $new_page[$page_guid]->use_rubric = $e_portfolio_group_setup->use_rubric;
	 if (strcmp($new_page[$page_guid]->rating_type,'e_portfolio_rating_type_marks')==0) {
            $new_page[$page_guid]->mark_type = $e_portfolio_group_setup->mark_type;
            $new_page[$page_guid]->type_mark = $e_portfolio_group_setup->type_mark;
            $new_page[$page_guid]->max_mark = $e_portfolio_group_setup->max_mark;
         } 
	 if (!$new_page[$page_guid]->var_pages) {
	    if (strcmp($new_page[$page_guid]->rating_type,'e_portfolio_rating_type_marks')==0) {
	       $mark_weight_stream = $e_portfolio_group_setup->mark_weight;
	       $mark_weight_array = explode(Chr(26),$mark_weight_stream);
               $new_page[$page_guid]->mark_weight = $mark_weight_array[$page_number-1];
	    } else {
	       if ($new_page[$page_guid]->use_rubric) {
	          $max_game_points_stream = $e_portfolio_group_setup->max_game_points;
		  $max_game_points_array = explode(Chr(26),$max_game_points_stream);
	          $new_page[$page_guid]->max_game_points = $max_game_points_array[$page_number-1];
	       }
	    }
            if ($new_page[$page_guid]->use_rubric) {
	       $rubric_guid_stream = $e_portfolio_group_setup->rubric_guid;
	       $rubric_guid_array = explode(Chr(26),$rubric_guid_stream); 
               $new_page[$page_guid]->rubric_guid = $rubric_guid_array[$page_number-1];
	    }
	 } else {
	    if (strcmp($new_page[$page_guid]->rating_type,'e_portfolio_rating_type_marks')==0) {
	       $new_page[$page_guid]->mark_weight = $e_portfolio_group_setup->mark_weight;
	    } else {
	       if ($new_page[$page_guid]->use_rubric)
	          $new_page[$page_guid]->max_game_points = $e_portfolio_group_setup->max_game_points;
	    }
            if ($new_page[$page_guid]->use_rubric) 
               $new_page[$page_guid]->rubric_guid = $e_portfolio_group_setup->rubric_guid;
	 }
      }

      $new_page[$page_guid]->rating = "not_qualified"; 

      if (!$new_page[$page_guid]->save()) {
         register_error(elgg_echo("e_portfolio:error_importing"));
         forward($_SERVER['HTTP_REFERER']);
      }

      $new_page_guid = $new_page[$page_guid]->getGUID();

      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'limit' => false, 'container_guid' => $page_guid);
      $artifacts = elgg_get_entities_from_metadata($options);
      
      foreach ($artifacts as $artifact) {
         $artifact_guid=$artifact->getGUID();
	 $index = $page_guid . "_" . $artifact_guid; 
        
         if ($artifact->artifact_type == 'video') {
            IzapBase::loadLib(array('plugin' => GLOBAL_IZAP_VIDEOS_PLUGIN,'lib' => 'izap_videos_lib'));
            $izap_videos[$index] = new IzapVideos();
            $videoValues = $izap_videos[$index]->input($artifact->video_url, 'url');
            if ($videoValues->success != 'false') {
               if (($videoValues->videosrc == '') || ($videoValues->filecontent == '')) {
                  register_error(elgg_echo('izap_videos:error'));
		  forward($_SERVER['HTTP_REFERER']);
               }
               $izap_videos[$index]->title = $videoValues->title;
               $izap_videos[$index]->description = (is_array($videoValues->description)) ? elgg_echo('izap_videos:noDescription') : $videoValues->description;
               $izap_videos[$index]->owner_guid = $user_guid;
               $izap_videos[$index]->container_guid = $new_page_guid;
               $izap_videos[$index]->access_id = $access_id;
               if (isset ($videoValues->videotags)) {
                  $izap_videos[$index]->tags = string_to_tag_array($videoValues->videotags);
               }
               $izap_videos[$index]->videosrc = $videoValues->videosrc;
               $izap_videos[$index]->videotype = $videoValues->type;
               $izap_videos[$index]->orignal_thumb = "izap_videos/" . $videoValues->type . "/orignal_" . $videoValues->filename;
               $izap_videos[$index]->imagesrc = "izap_videos/" . $videoValues->type . "/" . $videoValues->filename;
               $izap_videos[$index]->videotype_site = $videoValues->domain;
               $izap_videos[$index]->converted = 'yes';
               $izap_videos[$index]->setFilename($izap_videos[$index]->orignal_thumb);
               $izap_videos[$index]->open("write");
               if ($izap_videos[$index]->write($videoValues->filecontent)) {
                  $thumb = get_resized_image_from_existing_file($izap_videos[$index]->getFilenameOnFilestore(),120,90);
                  $izap_videos[$index]->setFilename($izap_videos[$index]->imagesrc);
                  $izap_videos[$index]->open("write");
                  if (!$izap_videos[$index]->write($thumb)) {
                     register_error(elgg_echo('izap_videos:error:saving_thumb'));
                  }
               } else {
                  register_error(elgg_echo('izap_videos:error:saving_thumb'));
               }
            } else {
               register_error($videoValues->message);
	       forward($_SERVER['HTTP_REFERER']);
            }
            if (!$izap_videos[$index]->save(false)) {
               register_error(elgg_echo('izap_videos:error:save'));
	       forward($_SERVER['HTTP_REFERER']);
            }

         } else {

	    $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link', 'types' => 'object','relationship_guid' => $artifact_guid, 'inverse_relationship' => FALSE, 'subtypes' => 'e_portfolio_file','limit'=>0));
	    $file_save_well=true;

	    $i=0;
	    $file_counter=0;
            foreach($files as $one_file) {
	       $index_file = $index . "_" . $i;
	       $file[$index_file] = new E_portfolioPluginFile();
               $file[$index_file]->subtype = "e_portfolio_file";
               $prefix = "file/";
	       $name = $one_file->title;
	       $mimetype = $one_file->mimetype;
               $filestorename = elgg_strtolower(time().$name);
               $file[$index_file]->setFilename($prefix.$filestorename);
               $file[$index_file]->setMimeType($mimetype);
               $file[$index_file]->originalfilename = $name;
               $file[$index_file]->simpletype = $one_file->simpletype;
	       $file[$index_file]->open("write");
	       $file_owner = $one_file->getOwnerEntity();
               $file_owner_time_created = date('Y/m/d',$file_owner->time_created);
               $file_dir_root = elgg_get_config('dataroot');
               $filename = $file_dir_root . $file_owner_time_created . "/" . $file_owner->guid . "/" . $one_file->filename;
               $content = file_get_contents($filename);
               $file[$index_file]->write($content);
               $file[$index_file]->close();
               $file[$index_file]->title = $name;
	       $file[$index_file]->owner_guid = $user_guid;
	       $file[$index_file]->container_guid = $new_page_guid;
               $file[$index_file]->access_id = $access_id;
               $file_save = $file[$index_file]->save();
               if(!$file_save) {
                  $file_save_well=false;
	          break;
	       }
	       $i=$i+1;
            }
            if (!$file_save_well){
	       $j = 0;
               while ($j < $i){
	          $index_file = $index . "_" . $j;
	          $deleted=$file[$index_file]->delete();
	          if (!$deleted){
                     register_error(elgg_echo('e_portfolio:filenotdeleted'));
		     forward($_SERVER['HTTP_REFERER']);
	          }
		  $j=$j+1;
	       }
	       register_error(elgg_echo('e_portfolio:file_error_save'));
               forward($_SERVER['HTTP_REFERER']);
            } else {
	       $file_counter = $i;
	    }
         }

         $new_artifact[$index] = new ElggObject();
         $new_artifact[$index]->subtype = "e_portfolio_artifact";
	 $new_artifact[$index]->owner_guid = $user_guid;
         $new_artifact[$index]->container_guid = $new_page_guid;
         $new_artifact[$index]->access_id = $access_id;
	 $new_artifact[$index]->title = $artifact->title;
         $new_artifact[$index]->description = $artifact->description;
         $new_artifact[$index]->embed = $artifact->embed;
         $new_artifact[$index]->artifact_number = $artifact->artifact_number;
	 $new_artifact[$index]->artifact_type = $artifact->artifact_type;
         if (!$new_artifact[$index]->save()) {
	    if ($file_counter>0){
	       $i = 0;
               while ($i < $file_counter){
	          $index_file = $index . "_" . $i;
                  $deleted=$file[$index_file]->delete();
                  if (!$deleted){
                     register_error(elgg_echo('e_portfolio:filenotdeleted'));
	    	     forward($_SERVER['HTTP_REFERER']);
                  }
		  $i=$i+1;
               }
            } elseif (strcmp($artifact->artifact_type,"video")==0){
               $deleted=$izap_videos[$index]->delete();
               if (!$deleted){
                  register_error(elgg_echo('e_portfolio:filenotdeleted'));
	    	  forward($_SERVER['HTTP_REFERER']);
               }
            }
            register_error(elgg_echo("e_portfolio:error_importing"));
            forward($_SERVER['HTTP_REFERER']);
         }

	 switch ($artifact->artifact_type) {
	    case 'urls_files':
	       $new_artifact[$index]->urls = $artifact->urls;
               break;
	    case 'video':
               $new_artifact[$index]->video_url = $artifact->video_url;
               add_entity_relationship($new_artifact[$index]->getGUID(),'e_portfolio_artifact_file_link',$izap_videos[$index]->getGUID());
               break;
         }
	 if ($file_counter>0){
	    $i=0;
            while ($i<$file_counter){
	       $index_file = $index . "_" . $i;
	       add_entity_relationship($new_artifact[$index]->getGUID(),'e_portfolio_artifact_file_link',$file[$index_file]->getGUID());
	       $i=$i+1;
            }
         }
      }
   }

   // System message                
   system_message(elgg_echo("e_portfolio:pages_imported"));

   // Add to river
   if (time() - $e_portfolio->time_updated > 1800)
      elgg_create_river_item(array(
         'view'=>'river/object/e_portfolio/update',
         'action_type'=>'update',
         'subject_guid'=>$user_guid,
         'object_guid'=>$e_portfoliopost,
      ));

   $e_portfolio->time_updated = time();

   //Forward
   forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfoliopost);

} else {
   register_error(elgg_echo("e_portfolio:not_selected_pages"));
   forward($_SERVER['HTTP_REFERER']);
}

}

?>