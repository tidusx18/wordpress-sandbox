<?php

/*
 * This plugin contains hooks that allow you to edit, add and move content without needing to edit template files. This method protects against upgrade issues.
 * Alternatively, you can copy template files from './templates/' folder to '/your-theme/hotel-booking/' to override them.
 */

HotelBookingPlugin::setPluginDirPathAndUrl( MPHB_PLUGIN_FILE, ( isset( $plugin ) ? $plugin : null ), ( isset( $network_plugin ) ? $network_plugin : null ) );

class HotelBookingPlugin {

	/**
	 *
	 * @var \MPHB\HotelBookingPlugin
	 */
	private static $instance = null;
	private static $_pluginFile;
	private static $_pluginDirPath;
	private static $_pluginDirUrl;

	/**
	 * Fix for symlinked plugin
	 *
	 * @global string $wp_version
	 * @param string $file
	 * @param string|null $plugin
	 * @param string|null $network_plugin
	 */
	public static function setPluginDirPathAndUrl( $file, $plugin, $network_plugin ){
		global $wp_version;
		if ( version_compare( $wp_version, '3.9', '<' ) && isset( $network_plugin ) ) {
			self::$_pluginFile = $network_plugin;
		} else {
			self::$_pluginFile = MPHB_PLUGIN_FILE;
		}

		$realDirName	 = basename( dirname( self::$_pluginFile ) );
		$symlinkDirName	 = isset( $plugin ) ? basename( dirname( $plugin ) ) : $realDirName;

		self::$_pluginDirPath = plugin_dir_path( self::$_pluginFile );

		if ( version_compare( $wp_version, '3.9', '<' ) ) {
			self::$_pluginDirUrl = plugin_dir_url( $symlinkDirName . '/' . basename( self::$_pluginFile ) );
		} else {
			self::$_pluginDirUrl = plugin_dir_url( self::$_pluginFile );
		}
	}

	private $name;
	private $author;
	private $version;
	private $slug; // "motopress-hotel-booking"
	private $productDir; // "motopress-hotel-booking" or "motopress-hotel-booking-lite"
	private $prefix;
	private $pluginDir;
	private $pluginDirUrl;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\SettingsMenuPage
	 */
	private $settingsMenuPage;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\ShortcodesMenuPage
	 */
	private $shortcodesMenuPage;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\LanguageMenuPage
	 */
	private $languageMenuPage;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\RoomsGeneratorMenuPage
	 */
	private $roomsGeneratorMenuPage;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\CalendarMenuPage
	 */
	private $calendarMenuPage;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\BookingRulesMenuPage
	 */
	private $bookingRulesPage;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\TaxesAndFeesMenuPage
	 */
	private $taxesAndFeesPage;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\iCalMenuPage
	 */
	private $iCalMenuPage;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\iCalImportMenuPage
	 */
	private $iCalImportMenuPage;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\iCalSyncProgressMenuPage
	 */
	private $iCalSyncProgressMenuPage;

	/**
	 *
	 * @var \MPHB\Admin\MenuPages\CreateBookingMenuPage
	 */
	private $createBookingMenuPage;


	/**
	 *
	 * @var \MPHB\CustomPostTypes
	 */
	private $postTypes;

	/**
	 *
	 * @var \MPHB\Session
	 */
	private $session;

	/**
	 *
	 * @var \MPHB\Ajax
	 */
	private $ajax;

	/**
	 *
	 * @var MPHB\Upgrader
	 */
	private $upgrader;

	/**
	 *
	 * @var MPHB\CalendarFeed
	 */
	private $calendarFeed;

	/**
	 *
	 * @var \MPHB\Wizard
	 */
	private $wizard;

	/**
	 *
	 * @var \MPHB\Importer
	 */
	private $importer;

	/**
	 *
	 * @var \MPHB\iCal\BackgroundProcesses\BackgroundSynchronizer
	 */
	private $iCalSynchronizer;

	/**
	 *
	 * @var MPHB\iCal\BackgroundProcesses\SyncRoomsQueueHandler
	 */
	private $syncRoomsQueueHandler;

	/**
	 *
	 * @var \MPHB\iCal\BackgroundProcesses\BackgroundUploader
	 */
	private $iCalUploader;

	/**
	 *
	 * @var \MPHB\ScriptManagers\PublicScriptManager
	 */
	private $publicScriptManager;

	/**
	 *
	 * @var \MPHB\ScriptManagers\AdminScriptManager
	 */
	private $adminScriptManager;

	/**
	 *
	 * @var \MPHB\Emails\Emails
	 */
	private $emails;

	/**
	 *
	 * @var \MPHB\Shortcodes
	 */
	private $shortcodes;

	/**
	 *
	 * @var \MPHB\UserActions\UserActions
	 */
	private $userActions;

	/**
	 *
	 * @var \MPHB\Entities\RoomType
	 */
	private $currentRoomType;

	/**
	 *
	 * @var \MPHB\BookingRules\RulesChecker
	 */
	private $rulesChecker;

	/**
	 *
	 * @var \MPHB\SearchParametersStorage
	 */
	private $searchParametersStorage;

	/**
	 *
	 * @var \MPHB\Settings\SettingsRegistry
	 */
	private $settings;

	/**
	 *
	 * @var \MPHB\Admin\NoticeManager
	 */
	private $notices;

	/**
	 *
	 * @var \MPHB\Admin\Menus
	 */
	private $menus;

	/**
	 *
	 * @var \MPHB\Payments\Gateways\GatewayManager
	 */
	private $gatewayManager;

	/**
	 *
	 * @var \MPHB\Payments\PaymentManager
	 */
	private $paymentManager;
	private $ratePersistence;
	private $roomTypePersistence;
	private $roomPersistence;
	private $attributesPersistence;
	private $bookingPersistence;
	private $servicePersistence;
	private $seasonPersistence;
	private $paymentPersistence;
	private $reservedRoomPersistence;
	private $couponPersistence;
	private $bookingRepository;
	private $rateRepository;
	private $roomRepository;
	private $roomTypeRepository;
	private $seasonRepository;
	private $paymentRepository;
	private $reservedRoomRepository;
	private $couponRepository;

	/**
	 *
	 * @var \MPHB\Crons\CronManager
	 */
	private $cronManager;

	public static function getInstance(){
		if ( !isset( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->afterConstruct();
		}
		return self::$instance;
	}

	private function __construct(){
		$this->pluginDir		 = self::$_pluginDirPath;
		$this->classesBasePath	 = trailingslashit( $this->getPluginPath( 'includes' ) );
		$this->pluginDirUrl		 = self::$_pluginDirUrl;
		$this->slug				 = 'motopress-hotel-booking';
		$this->productDir		 = basename( dirname( MPHB_PLUGIN_FILE ) );
		$this->prefix			 = 'mphb';

		$pluginData		 = $this->getPluginData();
		$this->author	 = isset( $pluginData['Author'] ) ? $pluginData['Author'] : '';
		$this->name		 = isset( $pluginData['Name'] ) ? $pluginData['Name'] : '';
		$this->version	 = isset( $pluginData['Version'] ) ? $pluginData['Version'] : '';
	}

	public function afterConstruct(){
		$this->includeFiles();
		$this->addActions();

		$globalNamespace	 = 'MPHB\\';
		$this->autoloader	 = new \MPHB\Autoloader( $globalNamespace, $this->classesBasePath );

		// Settings
		$this->settings = new \MPHB\Settings\SettingsRegistry();

		$this->notices = new \MPHB\Admin\NoticeManager();

		// Session
		$this->session = new \MPHB\Session();

		$this->translation = new \MPHB\Translation();

		$this->publicScriptManager	 = new \MPHB\ScriptManagers\PublicScriptManager;
		$this->adminScriptManager	 = new \MPHB\ScriptManagers\AdminScriptManager;

		$this->paymentManager	 = new \MPHB\Payments\PaymentManager();
		$this->gatewayManager	 = new \MPHB\Payments\Gateways\GatewayManager();

		$this->postTypes = new \MPHB\CustomPostTypes();

		$this->initRepositories();
		$this->menus = new MPHB\Admin\Menus();

		$this->createPages();
		$this->initBookingRules();

		$this->shortcodes = new \MPHB\Shortcodes();

		$this->wizard	 = new \MPHB\Wizard();
		$this->importer	 = new \MPHB\Importer();

		$this->iCalSynchronizer		 = new \MPHB\iCal\BackgroundProcesses\BackgroundSynchronizer();
		$this->syncRoomsQueueHandler = new MPHB\iCal\BackgroundProcesses\SyncRoomsQueueHandler( $this->iCalSynchronizer );
		$this->iCalUploader			 = new \MPHB\iCal\BackgroundProcesses\BackgroundUploader();

		$this->emails		 = new \MPHB\Emails\Emails();
		$this->userActions	 = new \MPHB\UserActions\UserActions;

		$this->cronManager = new MPHB\Crons\CronManager();

		new \MPHB\Fixes();
		new \MPHB\Views\ViewActions();

		// Widgets
		$this->initWidgets();

		$this->searchParametersStorage = new \MPHB\SearchParametersStorage();

		$this->ajax = new \MPHB\Ajax();

		$this->upgrader = new MPHB\Upgrader();

		$this->calendarFeed = new MPHB\CalendarFeed();
	}

	private function initRepositories(){

		$this->ratePersistence			 = new \MPHB\Persistences\RatePersistence( $this->postTypes->rate()->getPostType() );
		$this->roomTypePersistence		 = new \MPHB\Persistences\RoomTypePersistence( $this->postTypes->roomType()->getPostType() );
		$this->roomPersistence			 = new \MPHB\Persistences\RoomPersistence( $this->postTypes->room()->getPostType() );
		$this->attributesPersistence	 = new \MPHB\Persistences\AttributesPersistence( $this->postTypes->attributes()->getPostType() );
		$this->bookingPersistence		 = new \MPHB\Persistences\BookingPersistence( $this->postTypes->booking()->getPostType() );
		$this->servicePersistence		 = new \MPHB\Persistences\CPTPersistence( $this->postTypes->service()->getPostType() );
		$this->seasonPersistence		 = new \MPHB\Persistences\CPTPersistence( $this->postTypes->season()->getPostType() );
		$this->paymentPersistence		 = new \MPHB\Persistences\PaymentPersistence( $this->postTypes->payment()->getPostType() );
		$this->reservedRoomPersistence	 = new \MPHB\Persistences\ReservedRoomPersistence( $this->postTypes->reservedRoom()->getPostType() );
		$this->couponPersistence		 = new \MPHB\Persistences\CouponPersistence( $this->postTypes->coupon()->getPostType() );

		$this->roomTypeRepository		 = new \MPHB\Repositories\RoomTypeRepository( $this->roomTypePersistence );
		$this->roomRepository			 = new \MPHB\Repositories\RoomRepository( $this->roomPersistence );
		$this->rateRepository			 = new \MPHB\Repositories\RateRepository( $this->ratePersistence );
		$this->bookingRepository		 = new \MPHB\Repositories\BookingRepository( $this->bookingPersistence );
		$this->serviceRepository		 = new \MPHB\Repositories\ServiceRepository( $this->servicePersistence );
		$this->seasonRepository			 = new \MPHB\Repositories\SeasonRepository( $this->seasonPersistence );
		$this->paymentRepository		 = new \MPHB\Repositories\PaymentRepository( $this->paymentPersistence );
		$this->reservedRoomRepository	 = new \MPHB\Repositories\ReservedRoomRepository( $this->reservedRoomPersistence );
		$this->couponRepository			 = new \MPHB\Repositories\CouponRepository( $this->couponPersistence );
	}

	private function initBookingRules(){
		$reservationRules	 = $this->settings->bookingRules()->getReservationRules();
		$reservationRules	 = new MPHB\BookingRules\Reservation\ReservationRules( $reservationRules );

		$customRules = $this->settings->bookingRules()->getCustomRules();
		$customRules = new MPHB\BookingRules\Custom\CustomRules( $customRules );

		$this->rulesChecker = new MPHB\BookingRules\RulesChecker( $reservationRules, $customRules );
	}

	private function initWidgets(){
		\MPHB\Widgets\RoomsWidget::init();
		\MPHB\Widgets\SearchAvailabilityWidget::init();
	}

	/**
	 *
	 * @return \MPHB\BookingRules\RulesChecker
	 */
	public function getRulesChecker(){
		return $this->rulesChecker;
	}

	private function createPages(){

		$roomGeneratorAtts = array(
			'capability'	 => 'edit_posts',
			'parent_menu'	 => MPHB()->postTypes()->roomType()->getMenuSlug(),
			'order'			 => 20
		);

		$this->roomsGeneratorMenuPage = new \MPHB\Admin\MenuPages\RoomsGeneratorMenuPage( 'mphb_rooms_generator', $roomGeneratorAtts );

		$settingsAtts = array(
			'parent_menu'	 => MPHB()->postTypes()->roomType()->getMenuSlug(),
			'order'			 => 30
		);

		$this->settingsMenuPage = new \MPHB\Admin\MenuPages\SettingsMenuPage( 'mphb_settings', $settingsAtts );

		$languageAtts = array(
			'parent_menu'	 => MPHB()->postTypes()->roomType()->getMenuSlug(),
			'order'			 => 35,
		);

		$this->languageMenuPage = new \MPHB\Admin\MenuPages\LanguageMenuPage( 'mphb_language', $languageAtts );

		$shortcodesAtts = array(
			'capability'	 => 'edit_pages',
			'parent_menu'	 => MPHB()->postTypes()->roomType()->getMenuSlug(),
			'order'			 => 40,
		);

		$this->shortcodesMenuPage = new \MPHB\Admin\MenuPages\ShortcodesMenuPage( 'mphb_shortcodes', $shortcodesAtts );

		$calendarAtts = array(
			'order' => 50
		);

		$this->calendarMenuPage = new \MPHB\Admin\MenuPages\CalendarMenuPage( 'mphb_calendar', $calendarAtts );

		$bookingRulesSettings = array(
			'order' => 70
		);

		$this->bookingRulesPage = new \MPHB\Admin\MenuPages\BookingRulesMenuPage( 'mphb_booking_rules', $bookingRulesSettings );

		$taxesAndFeesSettings = array(
			'order' => 90
		);

		$this->taxesAndFeesPage = new \MPHB\Admin\MenuPages\TaxesAndFeesMenuPage( 'mphb_taxes_and_fees', $taxesAndFeesSettings );

		$iCalSettings = array(
			'order' => 110
		);

		$this->iCalMenuPage = new \MPHB\Admin\MenuPages\iCalMenuPage( 'mphb_ical', $iCalSettings );

		$iCalImportSettings = array(
			'order'			 => 120,
			'parent_menu'	 => 'none'
		);

		$this->iCalImportMenuPage = new \MPHB\Admin\MenuPages\iCalImportMenuPage( 'mphb_ical_import', $iCalImportSettings );

		$iCalSyncProgressSettings = array(
			'order'			 => 130,
			'parent_menu'	 => 'none'
		);

		$this->iCalSyncProgressMenuPage = new \MPHB\Admin\MenuPages\iCalSyncProgressMenuPage( 'mphb_ical_sync_progress', $iCalSyncProgressSettings );

		$createBookingSettings = array(
			'order'			 => 140,
			'parent_menu'	 => 'none'
		);

		$this->createBookingMenuPage = new \MPHB\Admin\MenuPages\CreateBookingMenuPage( 'mphb_add_new_booking', $createBookingSettings );


	}

	public function requireOnce( $relativePath ){
		require_once $this->getPluginPath( $relativePath );
	}

	public function includeFiles(){

		// Functions
		$this->requireOnce( 'functions.php' );
		$this->requireOnce( 'includes/attribute-functions.php' );
		$this->requireOnce( 'template-functions.php' );
		$this->requireOnce( 'includes/autoloader.php' );
		$this->requireOnce( 'includes/libraries/wp-session-manager/wp-session.php' );

		// WP Backgound Processing
		$this->requireOnce( 'includes/libraries/wp-background-processing/wp-background-processing.php' );
	}

	public function addActions(){
		add_action( 'plugins_loaded', array( $this, 'loadTextdomain' ) );
		add_action( 'admin_init', array( $this, 'initAutoUpdater' ), 9 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueuePublicScripts' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ), 11 );
		add_action( 'the_post', array( $this, 'setCurrentRoomType' ) );
	}

	public function enqueuePublicScripts(){
		if ( is_singular( $this->postTypes()->roomType()->getPostType() ) ) {
			$this->getPublicScriptManager()->enqueue();
		}

		if ( mphb_is_checkout_page() ) {
			$this->getPublicScriptManager()->enqueue();
		}
	}

	public function enqueueAdminScripts(){
		if ( mphb_is_attribute_taxonomy_edit_page() ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			$this->getAdminScriptManager()->enqueue();
		}
	}

	public function loadTextDomain(){

		$locale = $this->isWPVersion( '4.7', '>=' ) ? get_user_locale() : get_locale();

		$locale = apply_filters( 'plugin_locale', $locale, $this->slug );

		// wp-content/languages/motopress-hotel-booking/motopress-hotel-booking-{lang}_{country}.mo
		$customerMoFile = sprintf( '%1$s/%2$s/%2$s-%3$s.mo', WP_LANG_DIR, $this->slug, $locale );

		load_textdomain( $this->slug, $customerMoFile );

		load_plugin_textdomain( $this->slug, false, $this->productDir . '/languages' );
	}

	public function getPrefix(){
		return $this->prefix;
	}

	public function addPrefix( $str, $separator = '-' ){
		return $this->getPrefix() . $separator . $str;
	}

	/**
	 * Retrieve slug of plugin (basename of plugin main file)
	 *
	 * @return string
	 */
	public function getSlug(){
		return $this->slug;
	}

	/**
	 * Retrieve path to plugin directory
	 *
	 * @return string
	 */
	public function getPluginDir(){
		return $this->pluginDir;
	}

	/**
	 * Retrieve full path for the relative to plugin root path.
	 *
	 * @param string $relativePath
	 * @return string
	 */
	public function getPluginPath( $relativePath = '' ){
		return $this->pluginDir . $relativePath;
	}

	public function getPluginUrl( $relativePath = '' ){
		return $this->pluginDirUrl . $relativePath;
	}

	/**
	 *
	 * @return string
	 */
	public function getAjaxUrl(){
		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * Retreive version of plugin
	 *
	 * @return string
	 */
	public function getVersion(){
		return $this->version;
	}

	/**
	 *
	 * @return \MPHB\Settings\SettingsRegistry
	 */
	public function settings(){
		return $this->settings;
	}

	/**
	 *
	 * @return \MPHB\Admin\NoticeManager
	 */
	public function notices(){
		return $this->notices;
	}

	/**
	 *
	 * @return \MPHB\UserActions\UserActions
	 */
	public function userActions(){
		return $this->userActions;
	}

	/**
	 *
	 * @return \MPHB\Crons\CronManager
	 */
	public function cronManager(){
		return $this->cronManager;
	}

	/**
	 * @return \MPHB\Session
	 */
	public function getSession(){
		return $this->session;
	}

	/**
	 * Retrieve relative to theme root path to templates.
	 *
	 * @return string
	 */
	public function getTemplatePath(){
		return apply_filters( 'mphb_template_path', 'hotel-booking/' );
	}

	/**
	 *
	 * @param \WP_Post|int $post
	 */
	public function setCurrentRoomType( $post ){
		$this->currentRoomType = null;

		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		if ( !empty( $post->post_type ) && $post->post_type === MPHB()->postTypes()->roomType()->getPostType() ) {
			$this->currentRoomType = MPHB()->getRoomTypeRepository()->findById( $post->ID );
		}
	}

	/**
	 *
	 * @return \MPHB\Entities\RoomType
	 */
	public function getCurrentRoomType(){
		return $this->currentRoomType;
	}

	/**
	 *
	 * @return \MPHB\CustomPostTypes
	 */
	public function postTypes(){
		return $this->postTypes;
	}

	/**
	 *
	 * @return \MPHB\Admin\Menus
	 */
	public function menus(){
		return $this->menus;
	}

	/**
	 *
	 * @return \MPHB\Shortcodes
	 */
	public function getShortcodes(){
		return $this->shortcodes;
	}

	/**
	 *
	 * @return \MPHB\Ajax
	 */
	public function getAjax(){
		return $this->ajax;
	}

	/**
	 *
	 * @return MPHB\Upgrader
	 */
	public function upgrader(){
		return $this->upgrader;
	}

	/**
	 *
	 * @return \MPHB\Admin\MenuPages\SettingsMenuPage
	 */
	public function getSettingsMenuPage(){
		return $this->settingsMenuPage;
	}

	/**
	 *
	 * @return \MPHB\Admin\MenuPages\ShortcodesMenuPage
	 */
	public function getShortcodesMenuPage(){
		return $this->shortcodesMenuPage;
	}

	/**
	 *
	 * @return \MPHB\Admin\MenuPages\RoomsGeneratorMenuPage
	 */
	public function getRoomsGeneratorMenuPage(){
		return $this->roomsGeneratorMenuPage;
	}

	/**
	 *
	 * @return \MPHB\Admin\MenuPages\CalendarMenuPage
	 */
	public function getCalendarMenuPage(){
		return $this->calendarMenuPage;
	}

	/**
	 *
	 * @return \MPHB\Admin\MenuPages\BookingRulesMenuPage
	 */
	public function getBookingRulesPage(){
		return $this->bookingRulesPage;
	}

	/**
	 *
	 * @return \MPHB\Admin\MenuPages\TaxesAndFeesMenuPage
	 */
	public function getTaxesAndFeesPage(){
		return $this->taxesAndFeesPage;
	}

	/**
	 *
	 * @return \MPHB\Admin\MenuPages\iCalMenuPage
	 */
	public function getICalMenuPage(){
		return $this->iCalMenuPage;
	}

	/**
	 *
	 * @return MPHB\Admin\MenuPages\iCalImportMenuPage
	 */
	public function getICalImportMenuPage(){
		return $this->iCalImportMenuPage;
	}

	/**
	 *
	 * @return \MPHB\Admin\MenuPages\iCalSyncProgressMenuPage
	 */
	public function getICalSyncProgressMenuPage(){
		return $this->iCalSyncProgressMenuPage;
	}

	/**
	 *
	 * @return \MPHB\Admin\MenuPages\CreateBookingMenuPage
	 */
	public function getCreateBookingMenuPage(){
		return $this->createBookingMenuPage;
	}

	/**
	 *
	 * @return \MPHB\Importer
	 */
	public function getImporter(){
		return $this->importer;
	}

	/**
	 *
	 * @return \MPHB\iCal\BackgroundProcesses\BackgroundUploader
	 */
	public function getICalUploader(){
		return $this->iCalUploader;
	}

	/**
	 *
	 * @return \MPHB\iCal\BackgroundProcesses\BackgroundSynchronizer
	 */
	public function getICalSynchronizer(){
		return $this->iCalSynchronizer;
	}

	/**
	 *
	 * @return MPHB\iCal\BackgroundProcesses\SyncRoomsQueueHandler
	 */
	public function getSyncRoomsQueueHandler(){
		return $this->syncRoomsQueueHandler;
	}

	/**
	 *
	 * @return \MPHB\UserActions
	 */
	public function getUserActions(){
		return $this->userActions;
	}

	/**
	 *
	 * @return \MPHB\ScriptManagers\PublicScriptManager
	 */
	public function getPublicScriptManager(){
		return $this->publicScriptManager;
	}

	/**
	 *
	 * @return \MPHB\ScriptManagers\AdminScriptManager
	 */
	public function getAdminScriptManager(){
		return $this->adminScriptManager;
	}

	/**
	 *
	 * @return \MPHB\Emails\Emails
	 */
	public function emails(){
		return $this->emails;
	}

	/**
	 *
	 * @return \MPHB\SearchParametersStorage
	 */
	public function searchParametersStorage(){
		return $this->searchParametersStorage;
	}

	/**
	 *
	 * @param string $version version to compare with wp version
	 * @param string $operator Optional. Possible operators are: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne respectively. Default =.
	  This parameter is case-sensitive, values should be lowercase.
	 * @return bool
	 */
	public function isWPVersion( $version, $operator = '=' ){
		global $wp_version;
		return version_compare( $wp_version, $version, $operator );
	}

	static public function activate(){
		// This method will be called only once with first activated plugin - Premium or Lite
		MPHB()->postTypes()->flushRewriteRules();
	}

	static public function deactivate(){
		$mphbActiveCount = (int)\MPHB\Utils\ThirdPartyPluginsUtils::isActiveMphb();
		$mphbActiveCount += (int)\MPHB\Utils\ThirdPartyPluginsUtils::isActiveMphbLite();

		// Check bulk actions
		if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'deactivate-selected' || $_POST['action'] == 'delete-selected' ) ) {
			$removedPlugins = is_array( $_POST['checked'] ) ? mphb_clean( $_POST['checked'] ) : array();

			foreach ( $removedPlugins as $removedPlugin ) {
				if ( strpos( $removedPlugin, 'motopress-hotel-booking.php' ) !== false ) {
					$mphbActiveCount--;
				}
			}
		}

		if ( $mphbActiveCount <= 1 ) {
			flush_rewrite_rules();
			MPHB()->cronManager()->rescheduleAutoSynchronization( false );
		}
	}

	/**
	 *
	 * @return \MPHB\Persistences\RoomTypePersistence
	 */
	public function getRoomTypePersistence(){
		return $this->roomTypePersistence;
	}

	/**
	 *
	 * @return \MPHB\Persistences\AttributesPersistence
	 */
	public function getAttributesPersistence(){
		return $this->attributesPersistence;
	}

	/**
	 *
	 * @return \MPHB\Persistences\RoomPersistence
	 */
	public function getRoomPersistence(){
		return $this->roomPersistence;
	}

	/**
	 *
	 * @return \MPHB\Persistences\RatePersistence
	 */
	public function getRatePersistence(){
		return $this->ratePersistence;
	}

	/**
	 *
	 * @return \MPHB\Persistences\BookingPersistence
	 */
	public function getBookingPersistence(){
		return $this->bookingPersistence;
	}

	/**
	 *
	 * @return \MPHB\Persistences\CPTPersistence
	 */
	public function getServicePersistence(){
		return $this->servicePersistence;
	}

	/**
	 *
	 * @return \MPHB\Persistences\CPTPersistence
	 */
	public function getSeasonPersistence(){
		return $this->seasonPersistence;
	}

	/**
	 *
	 * @return \MPHB\Persistences\CPTPersistence
	 */
	public function getPaymentPersistence(){
		return $this->paymentPersistence;
	}

	/**
	 *
	 * @return \MPHB\Persistences\ReservedRoomPersistence
	 */
	public function getReservedRoomPersistence(){
		return $this->reservedRoomPersistence;
	}

	/**
	 *
	 * @return \MPHB\Persistences\CouponPersistence
	 */
	public function getCouponPersistence(){
		return $this->couponPersistence;
	}

	/**
	 *
	 * @return \MPHB\Repositories\RoomTypeRepository
	 */
	public function getRoomTypeRepository(){
		return $this->roomTypeRepository;
	}

	/**
	 *
	 * @return \MPHB\Repositories\RoomRepository
	 */
	public function getRoomRepository(){
		return $this->roomRepository;
	}

	/**
	 *
	 * @return \MPHB\Repositories\RateRepository
	 */
	public function getRateRepository(){
		return $this->rateRepository;
	}

	/**
	 *
	 * @return \MPHB\Repositories\BookingRepository
	 */
	public function getBookingRepository(){
		return $this->bookingRepository;
	}

	/**
	 *
	 * @return \MPHB\Repositories\ServiceRepository
	 */
	public function getServiceRepository(){
		return $this->serviceRepository;
	}

	/**
	 *
	 * @return \MPHB\Repositories\SeasonRepository
	 */
	public function getSeasonRepository(){
		return $this->seasonRepository;
	}

	/**
	 *
	 * @return \MPHB\Repositories\PaymentRepository
	 */
	public function getPaymentRepository(){
		return $this->paymentRepository;
	}

	/**
	 *
	 * @return \MPHB\Repositories\ReservedRoomRepository
	 */
	public function getReservedRoomRepository(){
		return $this->reservedRoomRepository;
	}

	/**
	 *
	 * @return \MPHB\Repositories\CouponRepository
	 */
	public function getCouponRepository(){
		return $this->couponRepository;
	}

	/**
	 *
	 * @return \MPHB\Payments\Gateways\GatewayManager
	 */
	public function gatewayManager(){
		return $this->gatewayManager;
	}

	/**
	 *
	 * @return \MPHB\Payments\PaymentManager
	 */
	public function paymentManager(){
		return $this->paymentManager;
	}

	/**
	 *
	 * @return array
	 */
	public function getPluginData(){
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		return get_plugin_data( self::$_pluginFile, false, false );
	}

	public function initAutoUpdater(){

		if ( $this->settings->license()->isEnabled() ) {

			$pluginData = $this->getPluginData();

			$apiData = array(
				'version'	 => $this->getVersion(),
				'license'	 => MPHB()->settings()->license()->getLicenseKey(),
				'item_id'	 => MPHB()->settings()->license()->getProductId(),
				'author'	 => isset( $pluginData['Author'] ) ? $pluginData['Author'] : ''
			);

			new MPHB\Libraries\EDD_Plugin_Updater\EDD_Plugin_Updater( MPHB()->settings()->license()->getStoreUrl(), self::$_pluginFile, $apiData );
			//new MPHB\LicenseNotice();
		}
	}

	/**
	 * Determines whether the current request is a WordPress Ajax request.
	 *
	 * @return bool
	 */
	public function isAjax(){
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * Check if the home URL is https.
	 *
	 * @return bool
	 */
	public function isSiteSSL(){
		return false !== strstr( get_option( 'home' ), 'https:' );
	}

	/**
	 * Table wp_termmeta required, for instance, to add "custom order" feature
	 * for room attributes.
	 *
	 * @return bool
	 *
	 * @see https://codex.wordpress.org/Current_events
	 */
	public function isWpSupportsTermmeta(){
		return ( get_option( 'db_version' ) >= 35700 ); // Since WordPress 4.4
	}

	/**
	 *
	 * @return \MPHB\Translation
	 */
	public function translation(){
		return $this->translation;
	}

}

register_activation_hook( MPHB_PLUGIN_FILE, array( 'HotelBookingPlugin', 'activate' ) );
register_deactivation_hook( MPHB_PLUGIN_FILE, array( 'HotelBookingPlugin', 'deactivate' ) );
HotelBookingPlugin::getInstance();

/**
 *
 * @return \HotelBookingPlugin
 */
function MPHB(){
	return HotelBookingPlugin::getInstance();
}