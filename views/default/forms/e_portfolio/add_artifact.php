<div class="contentWrapper">

<?php

$action = "e_portfolio/add_artifact";
$e_portfolio_page = $vars['entity'];
$e_portfolio_page_guid = $e_portfolio_page->getGUID();

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

   $rating_opened = elgg_echo('e_portfolio:rating_opened');
   $form_body .= "<p>" . $rating_opened . "</p>";
   echo elgg_echo($form_body);

} else {

if (!elgg_is_sticky_form('add_artifact_e_portfolio')) {
   $title = "";
   $description = "";
   $embed = "";
   $artifact_type = $vars['artifact_type'];
   switch($artifact_type){
      case 'urls_files':
         $comp_urls = array();
         break;
      case 'video':   
         $video_url = "";
         break;
   }
} else {
   $title = elgg_get_sticky_value('add_artifact_e_portfolio','title');
   $description = elgg_get_sticky_value('add_artifact_e_portfolio','description');
   $embed = elgg_get_sticky_value('add_artifact_e_portfolio','embed');
   $artifact_type = elgg_get_sticky_value('add_artifact_e_portfolio','artifact_type');
   switch($artifact_type){
      case 'urls_files':
         $urls_names = elgg_get_sticky_value('add_artifact_e_portfolio','urls_names');
         $urls = elgg_get_sticky_value('add_artifact_e_portfolio','urls');
         $i=0;
         $comp_urls = array();
         foreach ($urls as $url){
            $comp_urls[$i] = $urls_names[$i] . Chr(24) . $urls[$i]; 
            $i=$i+1;
         }  
         break;
      case 'video':   
         $video_url =  elgg_get_sticky_value('add_artifact_e_portfolio','video_url');
         break;
   }
}

elgg_clear_sticky_form('add_artifact_e_portfolio');

$submit_input_save = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('e_portfolio:save_artifact')));
$submit_input_finish = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('e_portfolio:save_artifact_finish')));
                
?>
<br/>
<?php

$tabs = array('simple' => array('title' => elgg_echo('e_portfolio:simple'),'url' => '?artifact_type=simple','selected' => $artifact_type == 'simple'),
              'urls_files' => array('title' => elgg_echo('e_portfolio:urls_files'),'url' => '?artifact_type=urls_files','selected' => $artifact_type == 'urls_files'),
              'image' => array('title' => elgg_echo('e_portfolio:image'),'url' => '?artifact_type=image','selected' => $artifact_type == 'image'),
              'audio' => array('title' => elgg_echo('e_portfolio:audio'),'url' => '?artifact_type=audio','selected' => $artifact_type == 'audio'));

if(elgg_is_active_plugin('izap-videos'))
   $tabs = array_merge($tabs, array('video' => array('title' => elgg_echo('e_portfolio:video'),'url' => '?artifact_type=video','selected' => $artifact_type == 'video')));

echo elgg_view('navigation/tabs', array('tabs' => $tabs));
   
?>
<br/>

<form action="<?php echo elgg_get_site_url()."action/".$action?>" name="add_artifact_e_portfolio" enctype="multipart/form-data" method="post">

<?php echo elgg_view('input/securitytoken'); ?>

<p>
<b><?php echo elgg_echo("e_portfolio:title"); ?></b><br>
<?php echo elgg_view("input/text", array('name' => 'title', 'value' => $title)); ?>
</p>
<p>
<b><?php echo elgg_echo("e_portfolio:form:description"); ?></b><br>
<?php echo elgg_view("input/longtext", array('name' => 'description', 'value' => $description)); ?>
</p>
<p>
<b><?php echo elgg_echo("e_portfolio:form:embed"); ?></b><br>
<?php echo elgg_view("input/text", array('name' => 'embed', 'value' => $embed));?>
</p>

<?php
switch ($artifact_type) {
   case 'urls_files':
      ?>
      <p>
      <b> <?php echo elgg_echo("e_portfolio:form:urls"); ?></b><br>
      <?php
      if ((count($comp_urls)>0)&&(strcmp($comp_urls[0],"")!=0)) {
         $i=0;
         foreach ($comp_urls as $url) {
            ?>
            <p class="clone_urls">
            <?php
	    $comp_url = explode(Chr(24),$url);
            $comp_url = array_map('trim',$comp_url);
            $url_name = $comp_url[0];
            $url_value = $comp_url[1];
            echo ("<b>" . elgg_echo("e_portfolio:form:url_name") . "</b>");
            echo elgg_view("input/text", array("name" => "urls_names[]","value" => $url_name));
            echo ("<b>" . elgg_echo("tutorial:form:url") . "</b>");
            echo elgg_view("input/text", array("name" => "urls[]","value" => $url_value));
	    if ($i>0){	
	       ?>
               <!-- remove url -->
               <a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete"); ?></a>          
               <?php
	    }
	    ?>
	    </p>
	    <?php 
	    $i=$i+1;
         }
      } else {
         ?>
         <p class="clone_urls">
         <?php
         $comp_url = explode(Chr(24),$comp_urls);
         $comp_url = array_map('trim',$comp_url);
         $url_name = $comp_url[0];
         $url_value = $comp_url[1];
         echo ("<b>" . elgg_echo("e_portfolio:form:url_name") . "</b>");
         echo elgg_view("input/text", array("name" => "urls_names[]","value" => $url_name));
         echo ("<b>" . elgg_echo("e_portfolio:form:url") . "</b>");
         echo elgg_view("input/text", array("name" => "urls[]","value" => $url_value));
         ?>
         </p>         
         <?php
      }
      ?>
      <!-- add link to add more urls which triggers a jquery clone function -->
      <a href="#" class="add" rel=".clone_urls"><?php echo elgg_echo("e_portfolio:add_url"); ?></a>
      <br><br>
      </p>                 
      <p>
      <b><?php echo elgg_echo("e_portfolio:form:files"); ?></b><br>
      <?php echo elgg_view("input/file",array('name' => 'upload[]', 'class' => 'multi'));?>
      </p>
      <?php
      break;
   case 'image':
      ?>
      <p>
      <b><?php echo elgg_echo("e_portfolio:form:image"); ?></b><br>
      <?php echo elgg_view("input/file",array('name' => 'upload[]'));?>
      </p>
      <?php
      break;
   case 'audio':
      ?>
      <p>
      <b><?php echo elgg_echo("e_portfolio:form:audio"); ?></b><br>
      <?php echo elgg_view("input/file",array('name' => 'upload[]'));?>
      </p>
      <?php
      break;
   case 'video':
      ?>
      <p>
      <b><?php echo elgg_echo("e_portfolio:form:video"); ?></b><br>
      <?php echo elgg_view("input/text", array('name' => 'video_url', 'value' => $video_url));?>
      </p>
      <?php
      break;    
}
?>

<!-- add the add/delete functionality  -->
<script type="text/javascript">
// remove function for the jquery clone plugin
$(function(){
   var removeLink = '<a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete");?></a>';
   $('a.add').relCopy({ append: removeLink});
});
</script>
 
<br>

<?php
echo "$submit_input_save $submit_input_finish";
?>

<input type="hidden" name="e_portfolio_page_guid" value="<?php echo $e_portfolio_page_guid; ?>">
<input type="hidden" name="artifact_type" value="<?php echo $artifact_type; ?>">

</form>

<?php
}
?>

<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/e_portfolio/lib/jquery.MultiFile.js"></script><!-- multi file jquery plugin -->
<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/e_portfolio/lib/reCopy.js"></script><!-- copy field jquery plugin -->
<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/e_portfolio/lib/js_functions.js">
</script>

</div>