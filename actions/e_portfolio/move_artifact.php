<?php

gatekeeper();

// Get input data
$e_portfolio_page_guid = get_input('e_portfolio_page_guid');
$artifact_number = get_input('artifact_number');	
$action = get_input('ac');

$e_portfolio_page = get_entity($e_portfolio_page_guid);	
$e_portfoliopost = $e_portfolio_page->container_guid;
$e_portfolio = get_entity($e_portfoliopost);
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

if ($e_portfolio_page->getSubtype() == "e_portfolio_page" && $e_portfolio_page->canEdit()) {
 
   if ($container instanceof ElggGroup) {
      $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
      $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
      $e_portfolio_group_setup = $e_portfolio_group_setup[0];
   }

   if (($e_portfolio_group_setup) && ($e_portfolio_group_setup->qualify_opened)) {
      register_error(elgg_echo('e_portfolio:error_rating_opened'));

} else {

   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'metadata_case_sensitive' => false,'limit' => false, 'container_guid' => $e_portfolio_page_guid, 'order_by_metadata' => array('name' => 'artifact_number', 'direction' => 'asc', 'as'=> 'integer'));
   $artifacts = elgg_get_entities_from_metadata($options);
   $num_artifacts = count($artifacts);

   $end=0;
   if (strcmp($action,"up")==0){
      foreach ($artifacts as $one_artifact){
         if ($one_artifact->artifact_number==$artifact_number){
	    $one_artifact->artifact_number=$artifact_number-1;
	    $end=$end+1;
         } else if ($one_artifact->artifact_number==($artifact_number-1)){
	    $one_artifact->artifact_number=$artifact_number;
	    $end=$end+1;
         }  
	 if ($end==2)
	    break;
      }
   }
   if (strcmp($action,"down")==0){
      foreach ($artifacts as $one_artifact){
         if ($one_artifact->artifact_number==$artifact_number){
            $one_artifact->artifact_number=$artifact_number+1;
	    $end=$end+1;
         } else if ($one_artifact->artifact_number==($artifact_number+1)){
	    $one_artifact->artifact_number=$artifact_number;
            $end=$end+1;
         } 
         if ($end==2)
	    break;
      }
   }

   if (strcmp($action,"top")==0){
      foreach ($artifacts as $one_artifact){
         if ($one_artifact->artifact_number==$artifact_number){
            $one_artifact->artifact_number=0;
         } else {
	    if ($one_artifact->artifact_number < $artifact_number)
               $one_artifact->artifact_number=$one_artifact->artifact_number + 1;
         }
      }
   }

   if (strcmp($action,"bottom")==0){
      foreach ($artifacts as $one_artifact){
         if ($one_artifact->artifact_number==$artifact_number){
            $one_artifact->artifact_number=$num_artifacts-1;
         } else {
	    if ($one_artifact->artifact_number > $artifact_number)
               $one_artifact->artifact_number=$one_artifact->artifact_number - 1;
         }
      }
   }  
}
}
forward($_SERVER['HTTP_REFERER']);
?>