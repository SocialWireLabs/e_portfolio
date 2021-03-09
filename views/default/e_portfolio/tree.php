<?php 
	
$e_portfolio = $vars['e_portfolio'];
$container_guid = $e_portfolio->container_guid;
$container = get_entity($container_guid);

$options = array('type_subtype_pairs' => array('object' => 'e_portfolio_page'), 'metadata_case_sensitive' => false, 'limit' => false,'container_guid' => $e_portfolio->getGUID(), 'order_by_metadata' => array('name' => 'page_number', 'direction' => 'asc', 'as' => 'integer'));
$pages = elgg_get_entities_from_metadata($options);

$num_pages = count($pages);

if ($container instanceof ElggGroup) {
   $options = array('type_subtype_pairs' => array('object' => 'e_portfolio_group_setup'), 'limit' => false, 'container_guid' => $container_guid);
   $e_portfolio_group_setup = elgg_get_entities_from_metadata($options);
   $e_portfolio_group_setup = $e_portfolio_group_setup[0];
   if (($e_portfolio_group_setup)&&(!$e_portfolio_group_setup->var_pages)&&($e_portfolio_group_setup->num_pages<$num_pages))
      $num_pages = $e_portfolio_group_setup->num_pages;
}

?>
<div class="contentWrapper">
    <div class="e_portfolio_tree">
        <div class="e_portfolio_title">
            <a href="<?php echo $e_portfolio->getURL(); ?>"><?php echo $e_portfolio->title; ?></a>
        </div>
        <br />
        <div class="e_portfolio_page_title">
           <?php
	   $i=0;
           foreach ($pages as $page){
              ?>
              <a href="<?php echo $page->getURL(); ?>"><?php echo $page->page_number.'. '.$page->title; ?></a><br />
              <?php
	      $i=$i+1;
	      if ($i==$num_pages)
	         break;
           }
           ?>
        </div>
    </div>
    <div class="clearfloat"></div>
</div>