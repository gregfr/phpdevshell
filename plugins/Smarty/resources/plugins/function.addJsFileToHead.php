
<?php



function smarty_function_addJsFileToHead($params, &$smarty)
{
    $root = $smarty->parent->dependance();

    $template = $root->PHPDS_template();
    $menu = $root->PHPDS_navigation()->currentMenu();

    $template->addJsFileToHead($menu['plugin_folder'].$params['path']);
}
?>
