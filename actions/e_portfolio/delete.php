<?php

gatekeeper();

$entity_guid = get_input('guid');
$entity = get_entity($entity_guid);

$user_guid = elgg_get_logged_in_user_guid();
		
if ($entity->getSubtype() == "e_portfolio"){
   if ($entity->canEdit()) {
      $e_portfoliopost = $entity_guid;
      $e_portfolio = $entity;
      $owner = get_entity($e_portfolio->getOwnerGUID());
      $owner_guid = $owner->getGUID();
      $container_guid = $e_portfolio->container_guid;
      $container = get_entity($container_guid);
      $operator = false;
      $owner_operator=false;
      if ($container instanceof ElggGroup) {
         $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
         $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
         $e_portfolio_group_setup = $e_portfolio_group_setup[0];

	 $group_owner_guid = $container->owner_guid;
         if (($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$container_guid))){
            $operator=true;
         }
         if (($group_owner_guid==$owner_guid)||(check_entity_relationship($owner_guid,'group_admin',$container_guid))){
            $owner_operator=true;
         }
      }
      if (($e_portfolio_group_setup) && ($e_portfolio_group_setup->qualify_opened) && ($owner_guid==$user_guid) && (!$operator)) {
         register_error(elgg_echo('e_portfolio:error_rating_opened'));
	 if ($container instanceof ElggGroup) {
            forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
         } else {
            forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
         }
      } else {
         $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'limit' => false, 'container_guid' => $e_portfoliopost);
         $pages = elgg_get_entities_from_metadata($options);

         $pages_qualified = false;
         if ($container instanceof ElggGroup) {
	    if ($e_portfolio_group_setup) {
               foreach ($pages as $one_page){
                  if (strcmp($one_page->rating,"not_qualified")!=0) {
	             $pages_qualified = true;
	             break;
	          }
	       }
            }
         }

         if ((!$pages_qualified)||($operator)) {

            foreach ($pages as $page) {
               $page_guid = $page->getGUID();
	       $page_owner = $page->getOwnerEntity();
               $page_owner_guid = $page_owner->getGUID();
               $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'limit' => false, 'container_guid' => $page_guid);
               $artifacts = elgg_get_entities_from_metadata($options);
               foreach ($artifacts as $artifact) {
                  //borrar archivos de la evidencia (imagen, audio, video y file general)
                  $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $artifact->getGUID(), 'inverse_relationship' => FALSE, 'type' => 'object','limit'=>0));
                  foreach ($files as $file) {
	             $deleted=$file->delete();
                     if (!$deleted){
                        register_error(elgg_echo("e_portfolio:filenotdeleted"));
	                if ($container instanceof ElggGroup) {
                           forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
                        } else {
                           forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
                        }
                     }
	          }
                  //borrar la evidencia
                  $deleted=$artifact->delete();
	          if (!$deleted){
                     register_error(elgg_echo("e_portfolio:artifactnotdeleted"));
	             if ($container instanceof ElggGroup) {
                        forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
                     } else {
                        forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
                     }
                  }
               }
	       //borrar posibles puntuaciones asociadas a la página
	       if ($container instanceof ElggGroup){
	          $access = elgg_set_ignore_access(true);
	          if ($page->use_rubric){
	             $rubric_rate = socialwire_rubric_get_rating($page_owner_guid,$page_guid);
	             if ($rubric_rate) {
	                $deleted=$rubric_rate->delete();
	                if (!$deleted){
                           register_error(elgg_echo("e_portfolio:ratingrubricnotdeleted"));
	                   if ($container instanceof ElggGroup) {
                              forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
                           } else {
                              forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
                           }
                        }
	             }    
	          }
	    	  if ((strcmp($page->rating_type,'e_portfolio_rating_type_marks')==0)&&($e_portfolio_group_setup)&&(!$page->var_pages)){
		     $marks = socialwire_marks_get_marks(null, $page_owner_guid, $e_portfolio_group_setup->getGUID(), $container_guid, $page->page_number);
		     if ($marks){
		        $deleted=$marks[0]->delete();
	                if (!$deleted){
                           register_error(elgg_echo("e_portfolio:marknotdeleted"));
	                   if ($container instanceof ElggGroup) {
                              forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
                           } else {
                              forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
                           }
                        }
		     }
	          } elseif (strcmp($page->rating_type,'e_portfolio_rating_type_game_points')==0){
	             $game_points = gamepoints_get_entity($page_guid);
	             if ($game_points) {
	                $deleted=$game_points->delete();
	                if (!$deleted){
                           register_error(elgg_echo("e_portfolio:gamepointsnotdeleted"));
	                   if ($container instanceof ElggGroup) {
                              forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
                           } else {
                              forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
                           }
                        }
	             }    
	          }
	          elgg_set_ignore_access($access);    
	       }
               //borrar la página
               $deleted=$page->delete();
               if (!$deleted){
                  register_error(elgg_echo("e_portfolio:pagenotdeleted"));
	          if ($container instanceof ElggGroup) {
                     forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
                  } else {
                     forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
                  }
               }
           }
	   //borrar posibles puntuaciones asociadas a la página
	   if ($container instanceof ElggGroup){
	      $access = elgg_set_ignore_access(true);
      	      if (($e_portfolio_group_setup)&&(strcmp($e_portfolio_group_setup->rating_type,'e_portfolio_rating_type_marks')==0)&&($e_portfolio_group_setup->var_pages)){
	         $marks = socialwire_marks_get_marks(null, $page_owner_guid, $e_portfolio_group_setup->getGUID(), $container_guid, "-1");
		 if ($marks){
		    $deleted=$marks[0]->delete();
	            if (!$deleted){
                       register_error(elgg_echo("e_portfolio:marknotdeleted"));
	               if ($container instanceof ElggGroup) {
                          forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
                       } else {
                          forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
                       }
                    }
		 }
	      }
	      elgg_set_ignore_access($access);
	   }
      
            // Delete it!
            $deleted = $e_portfolio->delete();
            if ($deleted > 0) {
               system_message(elgg_echo("e_portfolio:deleted"));
            } else {
               register_error(elgg_echo("e_portfolio:notdeleted"));
            }
         } else {
            register_error(elgg_echo("e_portfolio:pages_qualified_not_delete_e_portfolio"));
         }
         if ($container instanceof ElggGroup) {
            forward(elgg_get_site_url() . 'e_portfolio/group/' . $container_guid);
         } else {
            forward(elgg_get_site_url() . 'e_portfolio/owner/' . $owner->username);
         }
      }
   }
} elseif ($entity->getSubtype() == "e_portfolio_page") {
   if ($entity->canEdit()) {
      $page_guid = $entity_guid;
      $page = get_entity($entity_guid);
      $page_owner = $page->getOwnerEntity();
      $page_owner_guid = $page_owner->getGUID();
      $e_portfoliopost = $page->container_guid;
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
	 forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfoliopost);
      } else {
        
	 $pages_qualified = false;
         if ($container instanceof ElggGroup) {
	    if ($e_portfolio_group_setup) {
	       $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'limit' => false, 'container_guid' => $e_portfoliopost);
               $pages = elgg_get_entities_from_metadata($options);
               foreach ($pages as $one_page){
                  if (strcmp($one_page->rating,"not_qualified")!=0) {
	             $pages_qualified = true;
	             break;
	          }
	       }
            }
         }
         	 
         if (!$pages_qualified) {

            $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'limit' => false, 'container_guid' => $page_guid);
            $artifacts = elgg_get_entities_from_metadata($options);
            foreach ($artifacts as $artifact) {
               //borrar archivos de la evidencia (imagen, audio, video y file general)
               $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $artifact->getGUID(), 'inverse_relationship' => FALSE, 'types' => 'object','limit'=>0));
               foreach ($files as $file) {
                  $deleted = $file->delete();
	          if (!$deleted){
                     register_error(elgg_echo("e_portfolio:filenotdeleted"));
	             forward("e_portfolio/view/$e_portfoliopost");
                  }
               }
               //borrar la evidencia
               $deleted = $artifact->delete();
               if (!$deleted){
                  register_error(elgg_echo("e_portfolio:artifactnotdeleted"));
	          forward("e_portfolio/view/$e_portfoliopost");
               }
            }
            //si no es la última le cambiamos page_number a las siguientes    
            $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'limit' => false,'count' => true, 'container_guid' => $e_portfoliopost);
            $count_pages = elgg_get_entities_from_metadata($options);
            if ($count_pages != $page->page_number){
               $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'limit' => false,'container_guid' => $e_portfoliopost, 'metadata_case_sensitive' => false,'metadata_name_value_pairs' => array('name'=>'page_number','value' =>$page->page_number, 'operand' => '>'));
               $next_pages = elgg_get_entities_from_metadata($options);
               foreach ($next_pages as $one_page){
                  $one_page_number = $one_page->page_number-1;
                  $one_page->page_number = $one_page_number;
                  if (($e_portfolio_group_setup)&&(!$one_page->var_pages)&&($one_page_number<=$e_portfolio_group_setup->num_pages)) {
                     if (strcmp($one_page->rating_type,'e_portfolio_rating_type_marks')==0) {
	                $mark_weight_stream = $e_portfolio_group_setup->mark_weight;
	                $mark_weight_array = explode(Chr(26),$mark_weight_stream);
                        $one_page->mark_weight = $mark_weight_array[$one_page_number-1];
                     } else {
                        if ($one_page->use_rubric) { 
	                   $max_game_points_stream = $e_portfolio_group_setup->max_game_points;
	                   $max_game_points_array = explode(Chr(26),$max_game_points_stream);
                           $one_page->max_game_points = $max_game_points_array[$one_page_number-1];
	                }
                     }
                     if ($one_page->use_rubric) {
	                 $rubric_guid_stream = $e_portfolio_group_setup->rubric_guid;
	    		 $rubric_guid_array = explode(Chr(26),$rubric_guid_stream);
                         $one_page->rubric_guid = $rubric_guid_array[$one_page_number-1]; 
	             }
                  }
               }
           }
                            
           //borrar possibles puntuaciones asociadas a la página
	   if ($container instanceof ElggGroup) {
              $access = elgg_set_ignore_access(true);
              if ($page->use_rubric){
                 $rubric_rate = socialwire_rubric_get_rating($page_owner_guid,$page_guid);
	         if ($rubric_rate) {
	            $deleted=$rubric_rate->delete();
	            if (!$deleted){
                       register_error(elgg_echo("e_portfolio:ratingrubricnotdeleted"));
	               forward("e_portfolio/view/$e_portfoliopost");
                    }
	         }    
              }
	      if ((strcmp($page->rating_type,'e_portfolio_rating_type_marks')==0)&&($e_portfolio_group_setup)&&(!$page->var_pages)){
                 $marks = socialwire_marks_get_marks(null, $page_owner_guid, $e_portfolio_group_setup->getGUID(), $container_guid, $page->page_number);
                 if ($marks){
	            $deleted=$marks[0]->delete();
	            if (!$deleted){
                       register_error(elgg_echo("e_portfolio:marknotdeleted"));
	               forward("e_portfolio/view/$e_portfoliopost");
                    }
                 }
	      } elseif (strcmp($page->rating_type,'e_portfolio_rating_type_game_points')==0){
	         $game_points = gamepoints_get_entity($page_guid);
	         if ($game_points) {
	            $deleted=$game_points->delete();
	            if (!$deleted){
                       register_error(elgg_echo("e_portfolio:gamepointsnotdeleted"));
	               forward("e_portfolio/view/$e_portfoliopost");
                    }
	         }    
              }
              elgg_set_ignore_access($access);    
	   }
           //borrar la página
           $deleted = $page->delete();
           if ($deleted > 0) {
              system_message(elgg_echo("e_portfolio:pagedeleted"));
           } else {
              register_error(elgg_echo("e_portfolio:pagenotdeleted"));
           }
        } else {
      	   register_error(elgg_echo("e_portfolio:pages_qualified_not_delete_e_portfolio_page"));
        }
        forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfoliopost);
      }
   }
} elseif ($entity->getSubtype() == "e_portfolio_artifact") {
   if ($entity->canEdit()) {
      $artifact_guid = $entity_guid;
      $artifact = get_entity($entity_guid);
      $e_portfolio_page_guid = $artifact->container_guid;
      $e_portfolio_page = get_entity($e_portfolio_page_guid);
      $e_portfoliopost = $e_portfolio_page->container_guid;
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
	 forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfolio_page_guid);
      } else {
      
         //borrar archivos de la evidencia (imagen, video, audio y file general)
         $files = elgg_get_entities_from_relationship(array( 'relationship' => 'e_portfolio_artifact_file_link','relationship_guid' => $artifact_guid, 'inverse_relationship' => FALSE, 'type' => 'object','limit'=>0));
         foreach ($files as $file) {
            $deleted = $file->delete();
            if (!$deleted){
               register_error(elgg_echo("e_portfolio:filenotdeleted"));
	       forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfolio_page_guid);
            }
         }
         //si no es la última le cambiamos artifact_number a las siguientes
         $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'limit' => false,'count' => true, 'container_guid' => $artifact->container_guid);
         $count_artifacts = elgg_get_entities_from_metadata($options);
         if ($count_artifacts != $artifact->artifact_number){
            $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'limit' => false,'container_guid' => $artifact->container_guid, 'metadata_case_sensitive' => false,'metadata_name_value_pairs' => array('name'=>'artifact_number','value' =>$artifact->artifact_number, 'operand' => '>'));
            $next_artifacts = elgg_get_entities_from_metadata($options);
            foreach ($next_artifacts as $one_artifact)
               $one_artifact->artifact_number = $one_artifact->artifact_number-1;
         }
         //borrar la evidencia
         $deleted = $artifact->delete();
         if ($deleted > 0) {
            system_message(elgg_echo("e_portfolio:artifactdeleted"));
         } else {
            register_error(elgg_echo("e_portfolio:artifactnotdeleted"));
         }
         forward(elgg_get_site_url() . 'e_portfolio/view/' . $e_portfolio_page_guid);
      }
   }
}
 
		
?>