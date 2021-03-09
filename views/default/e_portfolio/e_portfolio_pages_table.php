<?php

$e_portfolio = $vars['entity'];
$e_portfoliopost = $e_portfolio->getGUID();
$num_pages = $vars['num_pages'];

$options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'metadata_case_sensitive' => false,'limit' => false, 'container_guid' => $e_portfoliopost, 'order_by_metadata' => array('name' => 'page_number', 'direction' => 'asc', 'as' => 'integer'));
$pages = elgg_get_entities_from_metadata($options);

if (empty($pages))
   $count_pages = 0;
else
   $count_pages = count($pages);
  	
if ($count_pages < $num_pages)
   $num_pages = $count_pages;

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
	
foreach ($pages as $page) {
   if ($page->page_number <= $num_pages) {
        $page_number=$page->page_number;
    	$page_guid = $page->getGUID();
    	$class = $class == "e_portfolio_list_table_odd" ? "e_portfolio_list_table_even" : "e_portfolio_list_table_odd";
        $moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up.png");
	$movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down.png");
	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top.png");
	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom.png");
	$up_script = "";
    	$top_script = "";
    	$down_script = "";
    	$bottom_script = "";
	$url_up = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/move_page?e_portfoliopost=" . $e_portfoliopost . "&ac=up" . "&page_number=" . $page_number);
	$url_down = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/move_page?e_portfoliopost=" . $e_portfoliopost . "&ac=down" . "&page_number=" . $page_number);
	$url_top = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/move_page?e_portfoliopost=" . $e_portfoliopost . "&ac=top" . "&page_number=" . $page_number);
	$url_bottom = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/e_portfolio/move_page?e_portfoliopost=" . $e_portfoliopost . "&ac=bottom" . "&page_number=" . $page_number);

	$text_page = elgg_get_excerpt($page->title,45);
	
    	if ($num_pages == 1) {
    		$up_script = 'Onclick="javascript:return false;"';
        	$top_script = 'Onclick="javascript:return false;"';
        	$down_script = 'Onclick="javascript:return false;"';
        	$bottom_script = 'Onclick="javascript:return false;"';
        	$moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up_dis.png");
        	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top_dis.png");
        	$movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down_dis.png");
        	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom_dis.png");
        	
	} elseif ($num_pages == 2) {
        	$top_script = 'Onclick="javascript:return false;"';
        	$bottom_script = 'Onclick="javascript:return false;"';
        	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top_dis.png");
        	$movebottom_img = sprintf($img_template,$movebottom_msg,$movebottom_msg,"bottom_dis.png");
		if ($page_number==1){
		   $up_script = 'Onclick="javascript:return false;"';
		   $moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up_dis.png");
		} else {
		   $up_script = "";
		}
		if ($page_number == $num_pages){
		   $down_script = 'Onclick="javascript:return false;"';
		   $movedown_img = sprintf($img_template,$movedown_msg,$movedown_msg,"down_dis.png");
		} else {
		   $down_script = "";
		}
    	} elseif ($page_number == 1) {
        	$up_script = 'Onclick="javascript:return false;"';
        	$top_script = 'Onclick="javascript:return false;"';
        	$down_script = "";
        	$bottom_script = "";
        	$moveup_img = sprintf($img_template,$moveup_msg,$moveup_msg,"up_dis.png");
        	$movetop_img = sprintf($img_template,$movetop_msg,$movetop_msg,"top_dis.png");
        } elseif ($page_number == $num_pages) {
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
        
$body .= sprintf($field_template,$class,$text_page,$up_script,$down_script,$top_script,$bottom_script);
   }

}

$body .= "</table>";
echo $body;

?>
