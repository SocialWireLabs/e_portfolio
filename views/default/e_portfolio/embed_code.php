<?php
if(!empty($vars['entity']->embed)){
    $embed_code = $vars['entity']->embed;
    if ($embed_code)
        echo html_entity_decode($embed_code);
}
?>