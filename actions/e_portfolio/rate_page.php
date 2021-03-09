<?php

gatekeeper();

$e_portfolio_page_guid = get_input('e_portfolio_page_guid');
$e_portfolio_page = get_entity($e_portfolio_page_guid);
$owner = $e_portfolio_page->getOwnerEntity();
$owner_guid = $owner->getGUID();
$e_portfolio = get_entity($e_portfolio_page->container_guid);
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

if ($e_portfolio_page->getSubtype() == "e_portfolio_page") {

   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
   $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
   $e_portfolio_group_setup = $e_portfolio_group_setup[0];

   if (!$e_portfolio_group_setup->qualify_opened) {
      register_error(elgg_echo('e_portfolio:error_rating_closed'));
   } else {
  
   if (strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0){
      if (strcmp($e_portfolio_page->type_mark,'e_portfolio_type_mark_numerical')==0){
         $max_rating = $e_portfolio_page->max_mark;
      } else {
         $max_rating = 10;
      }
   } elseif ((strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')!=0)&&($e_portfolio_page->use_rubric))
{
      $max_rating=$e_portfolio_page->max_game_points;
   }

   if (!$e_portfolio_page->use_rubric){
      $rating = get_input('rating');
      elgg_make_sticky_form('rate_page_e_portfolio');
      $good_rating = true;
      if (strcmp($rating,"")==0){
         $rating = "not_qualified";
      } else {
         if (($rating==-1)&&(strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($e_portfolio_page->type_mark,'e_portfolio_type_mark_numerical')!=0)) {
	    $rating = "not_qualified";
	 } else {
	    if ($rating<0){
	       $good_rating = false;
	    } else {
               $is_number=true;
               if ((strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($e_portfolio_page->type_mark,'e_portfolio_type_mark_numerical')==0)){
                  $is_number=is_numeric($rating);
               } elseif(strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')!=0){
                  $mask_integer='^([[:digit:]]+)$';                           
                  if (ereg($mask_integer,$rating,$same)){
                     if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
                        $is_number=false;
                     }
                  } else {
                     $is_number=false;
                  } 
               }
               if (!$is_number) {
                  $good_rating=false;
                  } else {
                     if (((strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($e_portfolio_page->type_mark,'e_portfolio_type_mark_numerical')==0))&&($rating>$max_rating)){
                     $good_rating=false;
                  }
               }
            }
 	 }
      } 
      if (!$good_rating){
         register_error(elgg_echo("e_portfolio:bad_rating"));
         //Forward
         forward($_SERVER['HTTP_REFERER']);
      }
      $e_portfolio_page->rating = $rating;
      elgg_clear_sticky_form('rate_page_e_portfolio');       
   } else {
      $rating_rubric = socialwire_rubric_get_rating($owner_guid,$e_portfolio_page_guid);
      if (!$rating_rubric){
         register_error(elgg_echo("e_portfolio:not_rating_rubric"));
         //Forward
         forward($_SERVER['HTTP_REFERER']);
      } else {
         $percentage = $rating_rubric->percentage;
         $rating = ($percentage*$max_rating*1.0)/100;
         if (strcmp($e_portfolio_page->rating_type,'e_portfolio_rating_type_marks')!=0)
            $rating = round($rating);
      }
      $e_portfolio_page->rating = $rating;
   }

   system_message(elgg_echo("e_portfolio:page_rated"));
   
}
}
//Forward
forward($_SERVER['HTTP_REFERER']);

?>

