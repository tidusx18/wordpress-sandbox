<?php

namespace MPHB;

use \MPHB\Entities;
use \MPHB\Views;
use \MPHB\Repositories;

/**
 * @todo move each ajax controller to separate class
 */
class Ajax {

	private $nonceName		 = 'mphb_nonce';
	private $actionPrefix	 = 'mphb_';
	private $ajaxActions	 = array(
		// Admin
		'recalculate_total'			 => array(
			'method' => 'POST',
			'nopriv' => false
		),
		'get_rates_for_room'		 => array(
			'method' => 'GET',
			'nopriv' => false
		),
		'dismiss_license_notice'	 => array(
			'method' => 'POST',
			'nopriv' => false
		),
		'attributes_custom_ordering' => array(
			'method' => 'POST',
			'nopriv' => false
		),
		// Frontend
		'update_checkout_info'		 => array(
			'method' => 'GET',
			'nopriv' => true
		),
		'update_rate_prices'		 => array(
			'method' => 'GET',
			'nopriv' => true
		),
		'get_billing_fields'		 => array(
			'method' => 'GET',
			'nopriv' => true
		),
		'apply_coupon'				 => array(
			'method' => 'POST',
			'nopriv' => true
		),
		'ical_sync_abort'			 => array(
			'method' => 'POST'
		),
		'ical_sync_clear_all'		 => array(
			'method' => 'POST'
		),
		'ical_sync_remove_item'		 => array(
			'method' => 'POST'
		),
		'ical_sync_get_progress'	 => array(
			'method' => 'POST'
		),
		'ical_upload_get_progress'	 => array(
			'method' => 'GET'
		),
		'ical_upload_abort'			 => array(
			'method' => 'POST'
		),
		'get_accommodations_list'	 => array(
			'method' => 'GET'
		),
		'get_free_accommodations_amount'	 => array(
			'method' => 'GET',
			'nopriv' => true
		)
	);

	public function __construct(){
		foreach ( $this->ajaxActions as $action => $details ) {
			$noPriv = isset( $details['nopriv'] ) ? $details['nopriv'] : false;
			$this->addAjaxAction( $action, $noPriv );
		}
	}

	/**
	 *
	 * @param string $action
	 * @param bool $noPriv
	 */
	public function addAjaxAction( $action, $noPriv ){

		add_action( 'wp_ajax_' . $this->actionPrefix . $action, array( $this, $action ) );

		if ( $noPriv ) {
			add_action( 'wp_ajax_nopriv_' . $this->actionPrefix . $action, array( $this, $action ) );
		}
	}

	/**
	 *
	 * @param string $action
	 * @return bool
	 */
	private function checkNonce( $action ){

		if ( !isset( $this->ajaxActions[$action] ) ) {
			return false;
		}

		$input = $this->retrieveInput( $action );

		$nonce = isset( $input[$this->nonceName] ) ? $input[$this->nonceName] : '';

		return wp_verify_nonce( $nonce, $this->actionPrefix . $action );
	}

	/**
	 *
	 * @param string $action Name of AJAX action without wp prefix.
	 * @return array
	 */
	private function retrieveInput( $action ){

		$method = isset( $this->ajaxActions[$action]['method'] ) ? $this->ajaxActions[$action]['method'] : '';

		switch ( $method ) {
			case 'GET':
				$input	 = $_GET;
				break;
			case 'POST':
				$input	 = $_POST;
				break;
			default:
				$input	 = $_REQUEST;
		}
		return $input;
	}

	/**
	 *
	 * @param string $action
	 */
	private function verifyNonce( $action ){
		if ( !$this->checkNonce( $action ) ) {
			wp_send_json_error( array(
				'message' => __( 'Request does not pass security verification. Please refresh the page and try one more time.', 'motopress-hotel-booking' )
			) );
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getAdminNonces(){
		$nonces = array();
		foreach ( $this->ajaxActions as $actionName => $actionDetails ) {
			$nonces[$this->actionPrefix . $actionName] = wp_create_nonce( $this->actionPrefix . $actionName );
		}
		return $nonces;
	}

	/**
	 *
	 * @return array
	 */
	public function getFrontNonces(){
		$nonces = array();
		foreach ( $this->ajaxActions as $actionName => $actionDetails ) {
			if ( isset( $actionDetails['nopriv'] ) && $actionDetails['nopriv'] ) {
				$nonces['mphb_' . $actionName] = wp_create_nonce( 'mphb_' . $actionName );
			}
		}
		return $nonces;
	}

	public function recalculate_total(){

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		if (
			!isset( $input['formValues'] ) ||
			!is_array( $input['formValues'] ) ||
			!isset( $input['formValues']['post_ID'] )
		) {
			wp_send_json_error( array(
				'message' => __( 'An error has occurred, please try again later.', 'motopress-hotel-booking' ),
			) );
		}

		$bookingId = intval( $input['formValues']['post_ID'] );

		$atts = MPHB()->postTypes()->booking()->getEditPage()->getAttsFromRequest( $input['formValues'] );

		// Check Required Fields
		if (
			empty( $atts['mphb_check_in_date'] ) ||
			empty( $atts['mphb_check_out_date'] )
		) {
			wp_send_json_error( array(
				'message' => __( 'Please complete all required fields and try again.', 'motopress-hotel-booking' )
			) );
		}

		$checkInDate	 = \DateTime::createFromFormat( 'Y-m-d', $atts['mphb_check_in_date'] );
		$checkOutDate	 = \DateTime::createFromFormat( 'Y-m-d', $atts['mphb_check_out_date'] );

		$reservedRooms = MPHB()->getReservedRoomRepository()->findAllByBooking( $bookingId );

		$bookingAtts = array(
			'check_in_date'	 => $checkInDate,
			'check_out_date' => $checkOutDate,
			'reserved_rooms' => $reservedRooms
		);

		$booking = Entities\Booking::create( $bookingAtts );

		if ( MPHB()->settings()->main()->isCouponsEnabled() && !empty( $input['formValues']['mphb_coupon_id'] ) ) {
			$coupon = MPHB()->getCouponRepository()->findById( intval( $input['formValues']['mphb_coupon_id'] ) );
			if ( $coupon ) {
				$booking->applyCoupon( $coupon );
			}
		}

		// array_walk_recursive() not required. wp_send_json_success() adds all
		// required slashes
		$priceBreakdown = $booking->getPriceBreakdown();

		wp_send_json_success( array(
			// [MB-684] Prevent excess number of digits
			'total'					 => round( $booking->calcPrice(), MPHB()->settings()->currency()->getPriceDecimalsCount() ),
			'price_breakdown'		 => json_encode( $priceBreakdown ),
			'price_breakdown_html'	 => \MPHB\Views\BookingView::generatePriceBreakdownArray( $priceBreakdown )
		) );
	}

	/**
	 * @param string $input Date string.
	 *
	 * @return \DateTime
	 */
	private function parseCheckInDate( $input ){
		$checkInDate = \DateTime::createFromFormat( 'Y-m-d', $input );

		if ( !$checkInDate ) {
			wp_send_json_error( array(
				'message' => __( 'Check-in date is not valid.', 'motopress-hotel-booking' )
			) );
		}

		return $checkInDate;
	}

	/**
	 * @param string $input Date string.
	 *
	 * @return \DateTime
	 */
	private function parseCheckOutDate( $input ){
		$checkOutDate = \DateTime::createFromFormat( 'Y-m-d', $input );

		if ( !$checkOutDate ) {
			wp_send_json_error( array(
				'message' => __( 'Check-out date is not valid.', 'motopress-hotel-booking' )
			) );
		}

		return $checkOutDate;
	}

	private function parseAdults( $input, $allowEmptyString = false ){
		$adults = Utils\ValidateUtils::validateInt( $input, 1 );

		if ( $adults === false ) {
			if ( $allowEmptyString ) {
				return '';
			}

			wp_send_json_error( array(
				'message' => __( 'Adults number is not valid.', 'motopress-hotel-booking' )
			) );
		}

		return $adults;
	}

	private function parseChildren( $input, $allowEmptyString = false ){
		$children = Utils\ValidateUtils::validateInt( $input, 0 );

		if ( $children === false ) {
			if ( $allowEmptyString ) {
				return '';
			}

			wp_send_json_error( array(
				'message' => __( 'Children number is not valid.', 'motopress-hotel-booking' )
			) );
		}

		return $children;
	}

	public function update_rate_prices(){

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		if ( !isset( $input['rates'], $input['adults'], $input['children'], $input['check_in_date'], $input['check_out_date'] ) ||
			!is_array( $input['rates'] )
		) {
			wp_send_json_error( array(
				'message' => __( 'An error has occurred. Please try again later.', 'motopress-hotel-booking' ),
			) );
		}

		$rates = \MPHB\Utils\ValidateUtils::validateIds( $input['rates'] );
		$adults = $this->parseAdults( $input['adults'], true );
		$children = $this->parseChildren( $input['children'], true );
		$checkInDate = $this->parseCheckInDate( $input['check_in_date'] );
		$checkOutDate = $this->parseCheckInDate( $input['check_out_date'] );

		$occupancyParams = mphb_occupancy_parameters( array(
			'adults' => $adults,
			'children' => $children,
			'check_in_date' => $checkInDate,
			'check_out_date' => $checkOutDate
		) );

		$prices = array();

		foreach ( $rates as $rateId ) {
			$rate = MPHB()->getRateRepository()->findById( $rateId );

			if ( !$rate ) {
				continue;
			}

			$price = $rate->calcPrice( $checkInDate, $checkOutDate, $occupancyParams );
			$prices[$rateId] = mphb_format_price( $price );
		}

		wp_send_json_success( $prices );
	}

	/**
	 * Parse booking from checkout form values.
	 *
	 * @param array $input
	 * @return Entities\Booking
	 */
	private function parseCheckoutFormBooking( $input ){

		$isSetRequiredFields = isset( $input['formValues'] ) &&
			is_array( $input['formValues'] ) &&
			!empty( $input['formValues']['mphb_room_details'] ) &&
			is_array( $input['formValues']['mphb_room_details'] ) &&
			!empty( $input['formValues']['mphb_check_in_date'] ) &&
			!empty( $input['formValues']['mphb_check_out_date'] );

		if ( $isSetRequiredFields ) {
			foreach ( $input['formValues']['mphb_room_details'] as &$roomDetails ) {
				if (
					!is_array( $roomDetails ) ||
					empty( $roomDetails['room_type_id'] ) ||
					!isset( $roomDetails['adults'] ) ||
					empty( $roomDetails['rate_id'] )
				) {
					$isSetRequiredFields = false;
					break;
				}

				if ( !isset( $roomDetails['children'] ) ) {
					$roomDetails['children'] = 0;
				}
			}
			unset( $roomDetails );
		}

		if ( !$isSetRequiredFields ) {
			wp_send_json_error( array(
				'message' => __( 'An error has occurred. Please try again later.', 'motopress-hotel-booking' ),
			) );
		}

		$atts = $input['formValues'];

		$checkInDate = $this->parseCheckInDate( $atts['mphb_check_in_date'] );
		$checkOutDate = $this->parseCheckOutDate( $atts['mphb_check_out_date'] );

		$reservedRooms = array();

		foreach ( $atts['mphb_room_details'] as $roomDetails ) {

			$roomTypeId	 = Utils\ValidateUtils::validateInt( $roomDetails['room_type_id'], 0 );
			$roomType	 = $roomTypeId ? MPHB()->getRoomTypeRepository()->findById( $roomTypeId ) : null;
			if ( !$roomType ) {
				wp_send_json_error( array(
					'message' => __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' )
				) );
			}

			$roomRateId	 = Utils\ValidateUtils::validateInt( $roomDetails['rate_id'], 0 );
			$roomRate	 = $roomRateId ? MPHB()->getRateRepository()->findById( $roomRateId ) : null;
			if ( !$roomRate ) {
				wp_send_json_error( array(
					'message' => __( 'Rate is not valid.', 'motopress-hotel-booking' )
				) );
			}

			$adults = $this->parseAdults( $roomDetails['adults'] );
			$children = $this->parseChildren( $roomDetails['children'] );

			$allowedServices = $roomType->getServices();

			$services = array();

			if ( !empty( $roomDetails['services'] ) && is_array( $roomDetails['services'] ) ) {
				foreach ( $roomDetails['services'] as $serviceDetails ) {

					if ( empty( $serviceDetails['id'] ) || !in_array( $serviceDetails['id'], $allowedServices ) ) {
						continue;
					}

					$serviceAdults = Utils\ValidateUtils::validateInt( $serviceDetails['adults'] );
					if ( $serviceAdults === false || $serviceAdults < 1 ) {
						continue;
					}

					$services[] = Entities\ReservedService::create( array(
						'id'	 => (int) $serviceDetails['id'],
						'adults' => $serviceAdults
					) );
				}
			}
			$services = array_filter( $services );

			$reservedRoomAtts = array(
				'room_type_id'		 => $roomTypeId,
				'rate_id'			 => $roomRateId,
				'adults'			 => $adults,
				'children'			 => $children,
				'reserved_services'	 => $services
			);

			$reservedRooms[] = Entities\ReservedRoom::create( $reservedRoomAtts );
		}

		$bookingAtts = array(
			'check_in_date'	 => $checkInDate,
			'check_out_date' => $checkOutDate,
			'reserved_rooms' => $reservedRooms,
		);

		$booking = Entities\Booking::create( $bookingAtts );

		if (
			MPHB()->settings()->main()->isCouponsEnabled() &&
			!empty( $input['formValues']['mphb_applied_coupon_code'] )
		) {
			$coupon = MPHB()->getCouponRepository()->findByCode( $input['formValues']['mphb_applied_coupon_code'] );
			if ( $coupon ) {
				$booking->applyCoupon( $coupon );
			}
		}

		return $booking;
	}

	public function update_checkout_info(){

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		$booking = $this->parseCheckoutFormBooking( $input );

		$total = $booking->calcPrice();

		$responseData = array(
			'total'			 => mphb_format_price( $total ),
			'priceBreakdown' => Views\BookingView::generatePriceBreakdown( $booking ),
		);

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$responseData['deposit'] = mphb_format_price( $booking->calcDepositAmount() );

			$responseData['gateways'] = array_map( function($gateway) use ($booking) {
				return $gateway->getCheckoutData( $booking );
			}, MPHB()->gatewayManager()->getListActive() );

			$responseData['isFree'] = $total == 0;
		}

		wp_send_json_success( $responseData );
	}

	public function get_billing_fields(){

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		$gatewayId = !empty( $input['mphb_gateway_id'] ) ? mphb_clean( $input['mphb_gateway_id'] ) : '';

		if ( !array_key_exists( $gatewayId, MPHB()->gatewayManager()->getListActive() ) ) {
			wp_send_json_error( array(
				'message' => __( 'Chosen payment method is not available. Please refresh the page and try one more time.', 'motopress-hotel-booking' ),
			) );
		}

		$booking = $this->parseCheckoutFormBooking( $input );

		ob_start();
		MPHB()->gatewayManager()->getGateway( $gatewayId )->renderPaymentFields( $booking );
		$fields = ob_get_clean();

		wp_send_json_success( array(
			'fields'			 => $fields,
			'hasVisibleFields'	 => MPHB()->gatewayManager()->getGateway( $gatewayId )->hasVisiblePaymentFields()
		) );
	}

	public function get_rates_for_room(){

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		$titlesList = array();

		if (
			isset( $input['formValues'] ) &&
			is_array( $input['formValues'] ) &&
			!empty( $input['formValues']['mphb_room_id'] )
		) {
			$roomId	 = absint( $input['formValues']['mphb_room_id'] );
			$room	 = MPHB()->getRoomRepository()->findById( $roomId );

			if ( !$room ) {
				wp_send_json_success( array(
					'options' => array()
				) );
			}

			foreach ( MPHB()->getRateRepository()->findAllActiveByRoomType( $room->getRoomTypeId() ) as $rate ) {
				$titlesList[$rate->getId()] = $rate->getTitle();
			}
		}

		wp_send_json_success( array(
			'options' => $titlesList
		) );
	}

	public function apply_coupon(){

		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		$booking = $this->parseCheckoutFormBooking( $input );

		$responseData = array();

		if ( MPHB()->settings()->main()->isCouponsEnabled() && isset( $input['mphb_coupon_code'] ) ) {

			$coupon = MPHB()->getCouponRepository()->findByCode( $input['mphb_coupon_code'] );

			if ( $coupon ) {
				$couponApplied = $booking->applyCoupon( $coupon );

				if ( is_wp_error( $couponApplied ) ) {
					$responseData['coupon'] = array(
						'applied_code'	 => '',
						'message'		 => $couponApplied->get_error_message()
					);
				} else {
					$responseData['coupon'] = array(
						'applied_code'	 => $booking->getCouponCode(),
						'message'		 => __( 'Coupon applied successfully.', 'motopress-hotel-booking' )
					);
				}
			} else {
				$responseData['coupon'] = array(
					'applied_code'	 => '',
					'message'		 => __( 'Coupon is not valid.', 'motopress-hotel-booking' )
				);
			}
		}

		$total = $booking->calcPrice();

		$responseData['total']			 = mphb_format_price( $total );
		$responseData['priceBreakdown']	 = Views\BookingView::generatePriceBreakdown( $booking );

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$responseData['deposit'] = mphb_format_price( $booking->calcDepositAmount() );

			$responseData['gateways'] = array_map( function($gateway) use ($booking) {
				return $gateway->getCheckoutData( $booking );
			}, MPHB()->gatewayManager()->getListActive() );

			$responseData['isFree'] = $total == 0;
		}

		wp_send_json_success( $responseData );
	}

	public function dismiss_license_notice(){

		$this->verifyNonce( __FUNCTION__ );

		MPHB()->settings()->license()->setNeedHideNotice( true );

		wp_send_json_success();
	}

	public function attributes_custom_ordering(){

		$this->verifyNonce( __FUNCTION__ );

		$termId = absint( $_POST['term_id'] );
		$nextTermId = ( isset( $_POST['next_term_id'] ) && absint( $_POST['next_term_id'] ) ) ? absint( $_POST['next_term_id'] ) : null;
		$taxonomyName = isset( $_POST['taxonomy_name'] ) ? esc_attr( $_POST['taxonomy_name'] ) : null;

		if ( !$termId || !$taxonomyName) {
			wp_send_json_error();
		}

		if ( !term_exists( $termId, $taxonomyName ) ) {
			wp_send_json_error();
		}

		mphb_reorder_attributes( $termId, $nextTermId, $taxonomyName );

		wp_send_json_success();
	}

	public function ical_sync_abort(){
		$this->verifyNonce( __FUNCTION__ );

		MPHB()->getSyncRoomsQueueHandler()->abort();

		wp_send_json_success();
	}

	public function ical_sync_clear_all(){
		$this->verifyNonce( __FUNCTION__ );

		MPHB()->getSyncRoomsQueueHandler()->clearAll();

		wp_send_json_success();
	}

	public function ical_sync_remove_item(){
		$this->verifyNonce( __FUNCTION__ );

		$roomKey = mphb_clean( $_POST['mphb_room_key'] );

		MPHB()->getSyncRoomsQueueHandler()->removeRoomKey( $roomKey );

		wp_send_json_success();
	}

	public function ical_sync_get_progress(){
		$this->verifyNonce( __FUNCTION__ );

		$logsHandler	 = new \MPHB\iCal\LogsHandler();
		$skipItems		 = isset( $_POST['skip'] ) ? (array) $_POST['skip'] : array();
		$allItems		 = MPHB()->getSyncRoomsQueueHandler()->getFullQueueRoomData();
		$processedItems	 = array();

		foreach ( $allItems as $key => $details ) {
			if ( in_array( $key, $skipItems ) ) {
				continue;
			}

			ob_start();
			$logsHandler->displayLogs( $details['logs'], false );
			$logs = ob_get_clean();

			$processedItem = array(
				'key'	 => $key,
				'status' => $details['status'],
				'stats'	 => $details['stats'],
				'logs'	 => $logs
			);

			if ( $details['status']['value'] == 'in-progress' ) {
				$processedItem['progress'] = MPHB()->getICalSynchronizer()->getProgress();
			}

			$processedItems[] = $processedItem;
		}

		wp_send_json_success( array(
			'items'		 => $processedItems,
			'inProgress' => MPHB()->getSyncRoomsQueueHandler()->isInProgress()
		) );
	}

	public function ical_upload_get_progress(){
		$this->verifyNonce( __FUNCTION__ );

		$logsShown		 = isset( $_GET['logsShown'] ) ? (int) $_GET['logsShown'] : 0;
		$logsHandler	 = new \MPHB\iCal\LogsHandler();
		$processDetails	 = MPHB()->getICalUploader()->getProcessDetails();
		$logs			 = $processDetails['logs'];
		$stats			 = $processDetails['stats'];
		$isFinished		 = !MPHB()->getICalUploader()->isInProgress();
		$notice			 = '';

		// Build notice
		if ( $isFinished ) {
			$notice  = $logsHandler->buildNotice( $stats['succeed'], $stats['failed'] );
		}

		// Remove shown logs
		if ( $logsShown > 0 ) {
			array_splice( $logs, 0, $logsShown );
		}

		// Calculate new "logsShown"
		$logsShown += count( $logs );

		wp_send_json_success( array(
			'total'		 => $stats['total'],
			'succeed'	 => $stats['succeed'],
			'skipped'	 => $stats['skipped'],
			'failed'	 => $stats['failed'],
			'progress'	 => MPHB()->getICalUploader()->getProgress(),
			'logs'		 => $logsHandler->logsToHtml( $logs ),
			'logsShown'	 => $logsShown,
			'notice'	 => $notice,
			'isFinished' => $isFinished
		) );
	}

	public function ical_upload_abort(){
		$this->verifyNonce( __FUNCTION__ );

		MPHB()->getICalUploader()->abort();

		wp_send_json_success();
	}

	public function get_accommodations_list(){
		$this->verifyNonce( __FUNCTION__ );

		$input = $this->retrieveInput( __FUNCTION__ );

		$formValues	 = $input['formValues'];
		$typeId		 = ( isset( $formValues['room_type_id'] ) ) ? (int)$formValues['room_type_id'] : 0;
		$roomsList	 = mphb_get_rooms_select_list( $typeId );

		wp_send_json_success( array( 'options' => $roomsList ) );
	}

	public function get_free_accommodations_amount(){
		$this->verifyNonce( __FUNCTION__ );

		$input		 = $this->retrieveInput( __FUNCTION__ );
		$dateFormat	 = MPHB()->settings()->dateTime()->getDateTransferFormat();
		$checkIn	 = \DateTime::createFromFormat( $dateFormat, $input['checkInDate'] );
		$checkOut	 = \DateTime::createFromFormat( $dateFormat, $input['checkOutDate'] );

		if ( $checkIn === false || $checkOut === false || $checkIn > $checkOut ) {
			wp_send_json_error( array( 'message' => __( 'Nothing found. Please try again with different search parameters.', 'motopress-hotel-booking' ) ) );
		}

		$typeId		 = $input['typeId'];
		$roomType	 = MPHB()->getRoomTypeRepository()->findById( $typeId );

		if ( !MPHB()->getRulesChecker()->reservationRules()->verify( $checkIn, $checkOut, $typeId ) ) {
			wp_send_json_error( array( 'message' => __( 'Nothing found. Please try again with different search parameters.', 'motopress-hotel-booking' ) ) );
		}

		$availableRooms = MPHB()->getRoomPersistence()->searchRooms( array(
			'availability'	 => 'free',
			'from_date'		 => $checkIn,
			'to_date'		 => $checkOut,
			'room_type_id'	 => $typeId
		) );
		$unavailableRooms = MPHB()->getRulesChecker()->customRules()->getUnavailableRooms( $checkIn, $checkOut, $roomType->getOriginalId() );
		$unavailableRooms = array_intersect( $availableRooms, $unavailableRooms ); // Filter not available rooms
		$freeCount = count( $availableRooms ) - count( $unavailableRooms );

		if ( $freeCount > 0 ) {
			wp_send_json_success( array( 'freeCount' => $freeCount ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Nothing found. Please try again with different search parameters.', 'motopress-hotel-booking' ) ) );
		}
	}

}
