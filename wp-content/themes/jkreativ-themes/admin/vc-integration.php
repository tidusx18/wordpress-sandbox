<?php
/**
* Force Visual Composer to initialize as "built into the theme". This will hide certain tabs under the Settings->Visual Composer page
*/


/* visual composer integration */
function jeg_vc_update(){
    if(function_exists('vc_set_as_theme')) {
        vc_set_as_theme();
    }

    if (class_exists('WPBakeryVisualComposerAbstract')) {
        locate_template(array('admin/vc/extend.php'), true, true);
        locate_template(array('admin/vc/view.php'), true, true);
        locate_template(array('admin/vc/element.php'), true, true);
        vc_set_default_editor_post_types(array('page', 'portfolio'));
    }
}

add_action( 'init' ,  'jeg_vc_update' , 2 );

function jeg_vc_remove_element() {
    if (class_exists('WPBakeryVisualComposerAbstract')) {
        locate_template(array('admin/vc/remove-element.php'), true, true);
    }
}

add_action('current_screen', 'jeg_vc_remove_element');

/** customize */
function jeg_remove_menu_vc() {
    remove_submenu_page('options-general.php','vc_settings');
}

add_action('admin_menu', 'jeg_remove_menu_vc', 99);