<?php

gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$e_portfolio_artifact_guid = (int) get_input('e_portfolio_artifact_guid');
$e_portfolio_artifact = get_entity($e_portfolio_artifact_guid);
$e_portfolio_page_guid = get_input('e_portfolio_page_guid');
$e_portfolio_page = get_entity($e_portfolio_page_guid);
$e_portfoliopost = $e_portfolio_page->container_guid;
$e_portfolio = get_entity($e_portfoliopost); 
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);
  
if ($e_portfolio_artifact->getSubtype() == "e_portfolio_artifact" && $e_portfolio_artifact->canEdit()) {
   
   if ($container instanceof ElggGroup) {
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
      $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
      $e_portfolio_group_setup = $e_portfolio_group_setup[0];
   }

   if (($e_portfolio_group_setup) && ($e_portfolio_group_setup->qualify_opened)) {

      register_error(elgg_echo('e_portfolio:error_rating_opened'));
      //Forward
      forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfolio_page_guid);

   } else {

   // Get input data
   $title = strip_tags(get_input('title'));
   $artifact_type = (get_input('artifact_type'));
   $file_counter = count($_FILES['upload']['name']);
   $selected_action = get_input('submit');
        
     
   // Cache to the session
   elgg_make_sticky_form('edit_artifact_e_portfolio');
   
   $previous_files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $e_portfolio_artifact_guid,'inverse_relationship' => FALSE,'types' => 'object','subtypes' => 'e_portfolio_file','limit'=>0));

   // Make sure the title is not blank
   if (empty($title)) {
      register_error(elgg_echo("e_portfolio:artifact_title_blank"));
      forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
   }

   $description = get_input('description');
   $embed = get_input('embed', '', false);
   if ($embed)
      $embed = htmlentities($embed);
			
   switch ($artifact_type) {
      case 'urls_files':
         $urls = get_input('urls');
         $urls = array_map('trim',$urls);
         $urls_names = get_input('urls_names');
         $urls_names = array_map('trim',$urls_names);
         $i=0;
         $comp_urls = "";
         if ((count($urls)>0)&&(strcmp($urls[0],"")!=0)) {
            foreach($urls as $url){
               if ($i!=0)
                  $comp_urls .= Chr(26);
               $comp_urls .= $urls_names[$i] . Chr(24) . $urls[$i];
               $i=$i+1;
            }
         }
         $number_urls = count($urls);
         $blank_url=false;
         $urlsarray=array();
         $i=0;
         foreach($urls as $one_url){
            $urlsarray[$i]=$one_url;
            if (strcmp($one_url,"")==0){
               $blank_url=true;
               break;
            }
            $i=$i+1;
         }    
         if (!$blank_url){
            foreach($urls_names as $one_url_name){
               if (strcmp($one_url_name,"")==0){
                  $blankurl=true;
                  break;
               }
            }
         } 
         if (($blank_url)&&($number_urls>1)){
            register_error(elgg_echo("e_portfolio:artifact_url_blank"));
	    forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
         }
         $same_url=false;
         $i=0;
         while(($i<$number_urls)&&(!$same_url)){
            $j=$i+1;
            while($j<$number_urls){
               if (strcmp($urlsarray[$i],$urlsarray[$j])==0){
                  $same_url=true;
                  break;
               }
               $j=$j+1;
            }
            $i=$i+1;
         }
         if ($same_url){
            register_error(elgg_echo("e_portfolio:artifact_url_repetition"));
	    forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
         }
         if (!$url_blank){
            foreach($urls as $url){             
               $xss = "<a rel=\"nofollow\" href=\"$url\" target=\"_blank\">$url</a>";
               if ($xss != filter_tags($xss)) {
                  register_error(elgg_echo('e_portfolio:artifact_url_failed'));
		  forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
               }
            }
         }  
         break;
      case 'video':
         $video_url = get_input('video_url');
         if (empty($video_url)) {
            register_error(elgg_echo("e_portfolio:artifact_video_url_blank"));
	    forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
         }
         IzapBase::loadLib(array('plugin' => GLOBAL_IZAP_VIDEOS_PLUGIN,'lib' => 'izap_videos_lib'));
         $izap_videos = new IzapVideos();
         $videoValues = $izap_videos->input($video_url, 'url');
         if ($videoValues->success != 'false') {
            if (($videoValues->videosrc == '') || ($videoValues->filecontent == '')) {
               register_error(elgg_echo('izap_videos:error'));
	       forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
            }
            $izap_videos->title = $videoValues->title;
            $izap_videos->description = (is_array($videoValues->description)) ? elgg_echo('izap_videos:noDescription') : $videoValues->description;
            $izap_videos->owner_guid = $user_guid;
            $izap_videos->container_guid = $e_portfolio_page_guid;
            $izap_videos->access_id = $e_portfolio_page->access_id;
            if (isset ($videoValues->videotags)) {
               $izap_videos->tags = string_to_tag_array($videoValues->videotags);
            }
            $izap_videos->videosrc = $videoValues->videosrc;
            $izap_videos->videotype = $videoValues->type;
            $izap_videos->orignal_thumb = "izap_videos/" . $videoValues->type . "/orignal_" . $videoValues->filename;
            $izap_videos->imagesrc = "izap_videos/" . $videoValues->type . "/" . $videoValues->filename;
            $izap_videos->videotype_site = $videoValues->domain;
            $izap_videos->converted = 'yes';
            $izap_videos->setFilename($izap_videos->orignal_thumb);
            $izap_videos->open("write");
            if ($izap_videos->write($videoValues->filecontent)) {
               $thumb = get_resized_image_from_existing_file($izap_videos->getFilenameOnFilestore(),120,90);
               $izap_videos->setFilename($izap_videos->imagesrc);
               $izap_videos->open("write");
               if (!$izap_videos->write($thumb)) {
                  register_error(elgg_echo('izap_videos:error:saving_thumb'));
               }
            } else {
               register_error(elgg_echo('izap_videos:error:saving_thumb'));
            }
         } else {
            register_error($videoValues->message);
	    forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
         }
         if (!$izap_videos->save(false)) {
            register_error(elgg_echo('izap_videos:error:save'));
	    forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
         }
         break;
   }
   if (((strcmp($artifact_type,"image")==0)||(strcmp($artifact_type,"audio")==0))&&(($file_counter==0)||( $_FILES['upload']['name'][0] == ""))){
      register_error(elgg_echo('e_portfolio:not_artifact_files'));
      forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
   }  
   if (!empty($previous_files))
      $previous_file_counter=count($previous_files);
   else 
      $previous_file_counter=0;
   foreach($previous_files as $one_file) {
      $value = get_input($one_file->getGUID());
      if($value == '1'){
         $previous_file_counter = $previous_file_counter-1;
      }
   }
   if ((strcmp($step_type,"urls_files")==0)&&((($file_counter+$previous_file_counter+$number_urls)==0)||((($previous_file_counter+$number_urls)==0)&&($_FILES['upload']['name'][0] == "")))){
      register_error(elgg_echo('e_portfolio:not_artifact_files'));
      forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
   }  
   if($file_counter > 0 && $_FILES['upload']['name'][0] != '') {
      $file_save_well=true;
      $file=array();
      for($i=0; $i<$file_counter; $i++) {
         $file[$i] = new E_portfolioPluginFile();
         $file[$i]->subtype = "e_portfolio_file";
         $prefix = "file/";
         $filestorename = elgg_strtolower(time().$_FILES['upload']['name'][$i]);
         $file[$i]->setFilename($prefix.$filestorename);
         $file[$i]->setMimeType($_FILES['upload']['type'][$i]);
         $file[$i]->originalfilename = $_FILES['upload']['name'][$i];
         $file[$i]->simpletype = elgg_get_file_simple_type($_FILES['upload']['type'][$i]);
         $file[$i]->open("write");
         if (isset($_FILES['upload']) && isset($_FILES['upload']['error'][$i])) {  
            $uploaded_file = file_get_contents($_FILES['upload']['tmp_name'][$i]);
         } else {
            $uploaded_file = false;
         }                  
         $file[$i]->write($uploaded_file);
         $file[$i]->close();
         $file[$i]->title = $_FILES['upload']['name'][$i];
         $file[$i]->owner_guid = $user_guid;
         $file[$i]->container_guid = $e_portfolio_page_guid;
         $file[$i]->access_id = $e_portfolio_page->access_id;
         $file_save = $file[$i]->save();
         if(!$file_save) {
            $file_save_well=false;
            break;
         }
      }
      if (!$file_save_well){
         foreach($file as $one_file){
            $deleted=$one_file->delete();
            if (!$deleted){
               register_error(elgg_echo('e_portfolio:filenotdeleted'));
	       forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
            }
         }
         register_error(elgg_echo('e_portfolio:file_error_save'));
         forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
      }
   }

   $e_portfolio_artifact->access_id = $e_portfolio_page->access_id;
   $e_portfolio_artifact->title = $title;
   $e_portfolio_artifact->description = $description;
   $e_portfolio_artifact->embed = $embed;
   if (!$e_portfolio_artifact->save()) {
      if (($file_counter>0)&&($_FILES['upload']['name'][0] != "")){
         foreach($file as $one_file){
            $deleted=$one_file->delete();
            if (!$deleted){
               register_error(elgg_echo('e_portfolio:filenotdeleted'));
               forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
            }
         }
      } else {
         if (strcmp($artifact_type,"video")==0){
            $deleted=$izap_videos->delete();
            if (!$deleted){
               register_error(elgg_echo('e_portfolio:filenotdeleted'));
               forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
            }
         }
      }
      register_error(elgg_echo("e_portfolio:error_save"));
      forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
   }

   //Delete previous files
   switch($artifact_type){
      case 'urls_files':
         foreach($previous_files as $one_previous_file) {
	    $one_previous_file_guid = $one_previous_file->getGUID();
            $value = get_input($one_previous_file_guid);
            if ($value == '1'){
               $file1 = get_entity($one_previous_file_guid);
               $deleted=$file1->delete();
               if (!$deleted){
                  register_error(elgg_echo('e_portfolio:filenotdeleted'));
                  forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
               }
            }
         }
         break;
      case 'image':   
         $deleted=$previous_files[0]->delete();
         if (!$deleted){
            register_error(elgg_echo('e_portfolio:filenotdeleted'));
	    forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
         }
         break;           
      case 'audio':   
         $deleted=$previous_files[0]->delete();
         if (!$deleted){
            register_error(elgg_echo('e_portfolio:filenotdeleted'));
            forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
         }
         break;      
      case 'video':   
         $deleted=$previous_files[0]->delete();
         if (!$deleted){
            register_error(elgg_echo('e_portfolio:filenotdeleted'));
            forward(elgg_get_site_url() . 'e_portfolio/edit_artifact/' . $e_portfolio_artifact_guid);
         }
         break;
   }

   switch ($artifact_type) {
      case 'urls_files':
         $e_portfolio_artifact->urls = $comp_urls;
         break;
      case 'video':
         $e_portfolio_artifact->video_url = $video_url;
         add_entity_relationship($e_portfolio_artifact_guid,'e_portfolio_artifact_file_link',$izap_videos->getGUID());
         break;
   }
   if (($file_counter>0)&&($_FILES['upload']['name'][0] != "")){
      for($i=0; $i<$file_counter; $i++){
         add_entity_relationship($e_portfolio_artifact_guid,'e_portfolio_artifact_file_link',$file[$i]->getGUID());
      }
   }
   
   // Remove the e_portfolio post cache
   elgg_clear_sticky_form('edit_artifact_e_portfolio');

   // Success message
   system_message(elgg_echo("e_portfolio:artifact_updated"));
                
   // Add to river
   // if (time() - $e_portfolio->time_updated > 1800)
   //    elgg_create_river_item(array(
   //       'view'=>'river/object/e_portfolio/update',
   //       'action_type'=>'update',
   //       'subject_guid'=>$user_guid,
   //       'object_guid'=>$e_portfoliopost,
   //    ));

   $e_portfolio->time_updated = time();

   //Forward
   if (strcmp($selected_action,elgg_echo('e_portfolio:save_artifact'))==0) {
      forward(elgg_get_site_url() . 'e_portfolio/add_artifact/' . $e_portfolio_page_guid);
   } else {
      forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfolio_page_guid);
   }
}                            		

}

?>