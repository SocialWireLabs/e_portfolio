<?php

gatekeeper();

// Get input data
$e_portfoliopost = get_input('e_portfoliopost');
$page_number = get_input('page_number');	
$action = get_input('ac');
	
$e_portfolio = get_entity($e_portfoliopost);
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

if ($e_portfolio->getSubtype() == "e_portfolio" && $e_portfolio->canEdit()) {
 
   if ($container instanceof ElggGroup) {
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
      $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
      $e_portfolio_group_setup = $e_portfolio_group_setup[0];
   }

   if (($e_portfolio_group_setup) && ($e_portfolio_group_setup->qualify_opened)) {
      register_error(elgg_echo('e_portfolio:error_rating_opened'));

} else {
 
   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'metadata_case_sensitive' => false,'limit' => false, 'container_guid' => $e_portfoliopost, 'order_by_metadata' => array('name' => 'page_number', 'direction' => 'asc', 'as' => 'integer'));
   $pages = elgg_get_entities_from_metadata($options);
   $num_pages = count($pages);

   $end=0;
   if (strcmp($action,"up")==0){
      foreach ($pages as $one_page){
         if ($one_page->page_number==$page_number){
	    $one_page->page_number=$page_number-1;
	    $end=$end+1;
         } else if ($one_page->page_number==($page_number-1)){
	    $one_page->page_number=$page_number;
	    $end=$end+1;
         }  
	 $one_page_number = $one_page->page_number;
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
	 if ($end==2)
	    break;
      }
   }
   if (strcmp($action,"down")==0){
      foreach ($pages as $one_page){
         if ($one_page->page_number==$page_number){
            $one_page->page_number=$page_number+1;
	    $end=$end+1;
         } else if ($one_page->page_number==($page_number+1)){
	    $one_page->page_number=$page_number;
            $end=$end+1;
         } 
	 $one_page_number = $one_page->page_number;
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
         if ($end==2)
	    break;
      }
   }

   if (strcmp($action,"top")==0){
      foreach ($pages as $one_page){
         if ($one_page->page_number==$page_number){
            $one_page->page_number=0;
         } else {
	    if ($one_page->page_number < $page_number)
               $one_page->page_number=$one_page->page_number +1;
         }
	 $one_page_number = $one_page->page_number;
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

   if (strcmp($action,"bottom")==0){
      foreach ($pages as $one_page){
         if ($one_page->page_number==$page_number){
            $one_page->page_number=$num_pages-1;
         } else {
	    if ($one_page->page_number > $page_number)
               $one_page->page_number=$one_page->page_number -1;
         }
	 $one_page_number = $one_page->page_number;
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
}
}
forward($_SERVER['HTTP_REFERER']);
?>