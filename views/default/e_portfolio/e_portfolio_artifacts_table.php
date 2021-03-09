<?php

$e_portfolio_page = $vars['entity'];
$e_portfolio_page_guid = $e_portfolio_page->getGUID();

$options = array('type_subtype_pairs' => array('object' => 'e_portfolio_artifact'), 'metadata_case_sensitive' => false,'limit' => false, 'container_guid' => $e_portfolio_page_guid, 'order_by_metadata' => array('name' => 'artifact_number', 'direction' => 'asc', 'as' => 'integer'));
$artifacts = elgg_get_entities_from_metadata($options);

if (empty($artifacts))
   $num_artifacts = 0;
else
   $num_artifacts = count($artifacts);
  	
$img_template = '<img border="0" width="16" height="16" alt="%s" title="%s" src="'.elgg_get_config('wwwroot').'mod/e_portfolio/graphics/%s" />';

$up_txt = elgg_echo('e_portfolio:up');
$down_txt = elgg_echo('e_portfolio:down');
$top_txt = elgg_echo('e_portfolio:top');
$bottom_txt = elgg_echo('e_portfolio:bottom');
	
	$body .= <<<EOF
			<SCRIPT>
				function call_mouse_over_function(object){
					$(object).css("background","#E3F1FF");
				}
				function call_mouse_out_function(object){
					$(object).css("background","");
				}
			</SCRIPT>
			<table class="e_portfolio_list_table">
				
EOF;
	
foreach ($artifacts as $artifact) {
        $artifact_number=$artifact->artifact_number;
    	$artifact_guid = $artifact->getGUID();
    	$class = $class == "e_portfolio_list_table_odd" ? "e_portfolio_list_table_even" : "e_portfolio_list_table_odd";
        $moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up.png");
	$movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down.png");
	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top.png");
	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom.png");
	$up_script = "";
    	$top_script = "";
    	$down_script = "";
    	$bottom_script = "";
	$url_up = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/move_artifact?e_portfolio_page_guid=" . $e_portfolio_page_guid . "&ac=up" . "&artifact_number=" . $artifact_number);
	$url_down = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/move_artifact?e_portfolio_page_guid=" . $e_portfolio_page_guid . "&ac=down" . "&artifact_number=" . $artifact_number);
	$url_top = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/move_artifact?e_portfolio_page_guid=" . $e_portfolio_page_guid . "&ac=top" . "&artifact_number=" . $artifact_number);
	$url_bottom = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/move_artifact?e_portfolio_page_guid=" . $e_portfolio_page_guid . "&ac=bottom" . "&artifact_number=" . $artifact_number);

	$text_artifact = elgg_get_excerpt($artifact->title,45);
	
    	if ($num_artifacts == 1) {
    		$up_script = 'Onclick="javascript:return false;"';
        	$top_script = 'Onclick="javascript:return false;"';
        	$down_script = 'Onclick="javascript:return false;"';
        	$bottom_script = 'Onclick="javascript:return false;"';
        	$moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up_dis.png");
        	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top_dis.png");
        	$movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down_dis.png");
        	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom_dis.png");
        	
	} elseif($num_artifacts == 2) {
        	$top_script = 'Onclick="javascript:return false;"';
        	$bottom_script = 'Onclick="javascript:return false;"';
        	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top_dis.png");
        	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom_dis.png");
		if ($artifact->artifact_number==1){
		   $up_script = 'Onclick="javascript:return false;"';
		   $moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up_dis.png");
		} else {
		   $up_script = "";
		}
		if ($artifact->artifact_number == $num_artifacts){
		   $down_script = 'Onclick="javascript:return false;"';
		   $movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down_dis.png");
		} else {
		   $down_script = "";
		}
    	} elseif ($artifact->artifact_number == 1) {
        	$up_script = 'Onclick="javascript:return false;"';
        	$top_script = 'Onclick="javascript:return false;"';
        	$down_script = "";
        	$bottom_script = "";
        	$moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up_dis.png");
        	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top_dis.png");
        } elseif ($artifact->artifact_number == $num_artifacts) {
        	$up_script = "";
        	$top_script = "";
        	$down_script = 'Onclick="javascript:return false;"';
        	$bottom_script = 'Onclick="javascript:return false;"';
        	$movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down_dis.png");
        	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom_dis.png");
        }
        
        $field_template = <<<END
        	<tr class="%s" onmouseover="call_mouse_over_function(this)" onmouseout="call_mouse_out_function(this)">
				<td style="width:345px;">%s</td>
				<td style="text-align:center"><a href="{$url_up}" %s>$moveup_img</a></td>
				<td style="text-align:center"><a href="{$url_down}" %s>$movedown_img</a></td>
				<td style="text-align:center"><a href="{$url_top}" %s >$movetop_img</a></td>
				<td style="text-align:center"><a href="{$url_bottom} %s">$movebottom_img</a></td>
			</tr>
END;
        
$body .= sprintf($field_template,$class,$text_artifact,$up_script,$down_script,$top_script,$bottom_script);

}

$body .= "</table>";
echo $body;

?>
