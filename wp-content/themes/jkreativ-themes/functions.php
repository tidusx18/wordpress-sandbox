<?php

/**
 * @author jegbagus
 */

// section navigator need here to be global
$sectionnavigator = array();
$jkreativplugincompatible = '1.2.1';

require_once get_template_directory() . '/lib/init.php';								// initialize
require_once get_template_directory() . '/lib/init-widget.php';						// widget
require_once get_template_directory() . '/lib/init-image.php';						// image functionality
require_once get_template_directory() . '/lib/themes-functionality.php';				// additional themes functionality
require_once get_template_directory() . '/lib/build-shortcode.php';					// build shortcode
require_once get_template_directory() . '/lib/init-menu.php';						// initialize menu
require_once get_template_directory() . '/lib/admin.php';							// back end
require_once get_template_directory() . '/lib/ajax-response.php';					// response ajax
require_once get_template_directory() . '/lib/scriptstyle.php';						    // loading style & script
require_once get_template_directory() . '/lib/jkreativ-customizer.php';				// customizer
require_once get_template_directory() . '/tgm/class-tgm-plugin-activation.php';		// tgm plugin
require_once get_template_directory() . '/tgm/plugin-list.php';						// tgm plugin list
require_once get_template_directory() . '/lib/update-notice.php';					// jkreativ plugin check

/** for demo purpose
require_once locate_template('/demo/demo.php');
 */