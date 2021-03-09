<?php

define('APTO', 5);

function e_portfolio_rating_input($entity,$rating,$name_rating) {
   if (((strcmp($entity->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($entity->type_mark,'e_portfolio_type_mark_numerical')==0))||(strcmp($entity->rating_type,'e_portfolio_rating_type_marks')!=0)){
       $rating_input = "<input type=\"text\"  name=\"" . $name_rating . "\" value=\"" . $rating . "\"  style=\"width: 80px\"/>";
   } elseif ((strcmp($entity->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($entity->type_mark,'e_portfolio_type_mark_textual')==0)) {
      if (strcmp($rating,"")!=0){
         if ($rating >= HONOURS){
            $rating = HONOURS;
         } elseif ($rating >= OUTSTANDING){
            $rating = OUTSTANDING;
         } elseif ($rating >= VERYGOOD){
            $rating = VERYGOOD;
         } elseif ($rating >= SUFFICIENT){
            $rating = SUFFICIENT;
         } else {
            $rating = INSUFFICIENT;
         }
      } else {
         $rating = -1;
      }
      $options = array('name' => $name_rating, 'value' => $rating, 'options_values' => array('-1' => '', HONOURS => elgg_echo('mark:honours'), OUTSTANDING => elgg_echo('mark:outstanding'), VERYGOOD => elgg_echo('mark:verygood'), SUFFICIENT => elgg_echo('mark:sufficient'), INSUFFICIENT => elgg_echo('mark:insufficient')));
      $rating_input = elgg_view('input/dropdown',$options); 
   } elseif ((strcmp($entity->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($entity->type_mark,'e_portfolio_type_mark_apto')==0)){
      if (strcmp($rating,"")!=0){
         if ($rating>=APTO){
            $rating = PASS;
         } else {
            $rating = FAIL;
         }
      } else {
         $rating = -1;
      }
      $options = array('name' => $name_rating, 'value' => $rating, 'options_values' => array('-1' => '', PASS => elgg_echo('mark:pass'), FAIL => elgg_echo('mark:fail')));
      $rating_input = elgg_view('input/dropdown',$options); 
   }
   return $rating_input;
}


function e_portfolio_rating_output($entity,$rating) {
   if (((strcmp($entity->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($entity->type_mark,'e_portfolio_type_mark_numerical')==0))||(strcmp($entity->rating_type,'e_portfolio_rating_type_marks')!=0)){
      $rating_output = $rating;
   } elseif ((strcmp($entity->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($entity->type_mark,'e_portfolio_type_mark_textual')==0)) {
      if ($rating >= HONOURS){
         $rating_output = elgg_echo('mark:honours');
      } elseif ($rating >= OUTSTANDING){
         $rating_output = elgg_echo('mark:outstanding');
      } elseif ($rating >= VERYGOOD){
         $rating_output = elgg_echo('mark:verygood');
      } elseif ($rating >= SUFFICIENT){
         $rating_output = elgg_echo('mark:sufficient');
      } else {
         $rating_output = elgg_echo('mark:insufficient');
      }
   } elseif ((strcmp($entity->rating_type,'e_portfolio_rating_type_marks')==0)&&(strcmp($entity->type_mark,'e_portfolio_type_mark_apto')==0)){
      if ($rating>=APTO)
         $rating_output = elgg_echo('mark:pass');
      else
         $rating_output = elgg_echo('mark:fail');
   }
   return $rating_output;
}

?>