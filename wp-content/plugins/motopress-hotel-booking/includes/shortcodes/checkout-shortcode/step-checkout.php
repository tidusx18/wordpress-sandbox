<?php

namespace MPHB\Shortcodes\CheckoutShortcode;

use \MPHB\Entities;

class StepCheckout extends Step {

	/**
	 *
	 * @var Entities\Booking
	 */
	protected $booking;

	/**
	 *
	 * @var Entities\ReservedRoom[]
	 */
	protected $reservedRooms;

	/**
	 *
	 * @var boolean
	 */
	protected $alreadyBooked = false;

	/**
	 *
	 * @var array
	 */
	protected $roomDetails = array();

	public function __construct(){
		add_action( 'init', array( $this, 'addInitActions' ) );
	}

	public function addInitActions(){
		// templates hooks
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderBookingDetails' ), 10, 2 );

		add_action( 'mphb_sc_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderRoomTypeTitle' ), 10, 2 );
		add_action( 'mphb_sc_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderGuestsChooser' ), 20, 3 );
		add_action( 'mphb_sc_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderRateChooser' ), 30, 4 );
		add_action( 'mphb_sc_checkout_room_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderServiceChooser' ), 40, 2 );

		if ( MPHB()->settings()->main()->isCouponsEnabled() ) {
			add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCoupon' ), 20 );
		}
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderPriceBreakdown' ), 30 );
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckoutText' ), 35 );
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCustomerDetails' ), 40 );

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$gateways = MPHB()->gatewayManager()->getListActive();

			// Filter used in mphb-woocommerce to hide WooCommerce gateway, if it is the only one
			if ( count( $gateways ) == 1 && apply_filters( 'mphb_sc_checkout_single_gateway_hide_billing_details', false, reset( $gateways ) ) ) {
				add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderBillingDetailsHidden' ), 5 );
			} else {
				add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderBillingDetails' ), 45 );
			}

		}

		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderTotalPrice' ), 50 );
		add_action( 'mphb_sc_checkout_form', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderTermsAndConditions' ), 60 );

		// Booking Details
		add_action( 'mphb_sc_checkout_form_booking_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckInDate' ) );
		add_action( 'mphb_sc_checkout_form_booking_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderCheckOutDate' ) );
		add_action( 'mphb_sc_checkout_form_booking_details', array( '\MPHB\Views\Shortcodes\CheckoutView', 'renderBookingDetailsInner' ), 10, 2 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function setup(){

		$this->isCorrectBookingData = $this->parseBookingData();

		if ( $this->isCorrectBookingData ) {
			$bookingAtts = array(
				'check_in_date'	 => $this->checkInDate,
				'check_out_date' => $this->checkOutDate,
				'reserved_rooms' => $this->reservedRooms
			);

			$this->booking = Entities\Booking::create( $bookingAtts );

			$this->stepValid();

			if ( MPHB()->settings()->main()->isDirectBooking() ) {
				// We skiped the search page, so save the search parameters here
				MPHB()->searchParametersStorage()->save(
					array(
						'mphb_check_in_date'	 => $this->checkInDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ),
						'mphb_check_out_date'	 => $this->checkOutDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ),
						'mphb_adults'			 => MPHB()->settings()->main()->getMinAdults(),
						'mphb_children'			 => MPHB()->settings()->main()->getMinChildren()
					)
				);
			}

		}
	}

	protected function parseBookingData(){

		$isCorrectCheckInDate	 = $this->parseCheckInDate();
		$isCorrectCheckOutDate	 = $this->parseCheckOutDate();

		if ( !$isCorrectCheckInDate || !$isCorrectCheckOutDate ) {
			return false;
		}

		if ( empty( $_POST['mphb_rooms_details'] ) || !is_array( $_POST['mphb_rooms_details'] ) ) {
			$this->errors[] = __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' );
			return false;
		}

		$this->reservedRooms = array();
		$this->roomDetails = array();
		foreach ( $_POST['mphb_rooms_details'] as $roomTypeId => $roomsCount ) {

			$roomTypeId = filter_var( $roomTypeId, FILTER_VALIDATE_INT );
			if ( !$roomTypeId ) {
				$this->errors[] = __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' );
				continue;
			}

			$roomsCount = filter_var( $roomsCount, FILTER_VALIDATE_INT );
			if ( !$roomsCount ) {
				$this->errors[] = __( 'Accommodation count is not valid.', 'motopress-hotel-booking' );
				continue;
			}

			$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
			if ( !$roomType ) {
				$this->errors[] = __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' );
				continue;
			}

			if ( !MPHB()->getRoomPersistence()->isExistsRooms( $this->checkInDate, $this->checkOutDate, $roomsCount, $roomType->getOriginalId() ) ) {
				$this->alreadyBooked = true;
				break;
			}

			$rateArgs = array(
				'check_in_date'	 => $this->checkInDate,
				'check_out_date' => $this->checkOutDate,
//				'mphb_language'	 => 'original'
			);

			$allowedRates = MPHB()->getRateRepository()->findAllActiveByRoomType( $roomType->getId(), $rateArgs );
			if ( empty( $allowedRates ) ) {
				$this->errors[] = __( 'There are no rates for requested dates.', 'motopress-hotel-booking' );
				continue;
			}

			if ( !MPHB()->getRulesChecker()->verify( $this->checkInDate, $this->checkOutDate, $roomTypeId ) ) {
				$this->errors[] = sprintf( __( 'Selected dates do not meet booking rules for type %s', 'motopress-hotel-booking' ), $roomType->getTitle() );
				continue;
			}

			$defaultRate = reset( $allowedRates );

			$reservedRoomAtts = array(
				'rate_id'	 => $defaultRate->getOriginalId(),
				'adults'	 => $roomType->getAdultsCapacity(),
				'children'	 => $roomType->getChildrenCapacity()
			);

			for ( $i = 1; $i <= $roomsCount; $i++ ) {

				$reservedRoom = Entities\ReservedRoom::create( $reservedRoomAtts );

				$this->reservedRooms[] = $reservedRoom;
				$this->roomDetails[] = array(
					'room_type_id'	 => $roomTypeId,
					'rate_id'		 => $defaultRate->getOriginalId(),
					'allowed_rates'	 => $allowedRates,
					'adults'		 => $roomType->getAdultsCapacity(),
					'children'		 => $roomType->getChildrenCapacity()
				);
			}
		}

		if ( empty( $this->reservedRooms ) ) {
			return false;
		}

		return true;
	}

	public function render(){
		if ( !$this->isCorrectBookingData ) {
			$this->showErrorsMessage();
			return;
		}

		if ( $this->alreadyBooked ) {
			$this->showAlreadyBookedMessage();
			return;
		}

		MPHB()->getSession()->set( 'mphb_checkout_step', \MPHB\Shortcodes\CheckoutShortcode::STEP_CHECKOUT );

		do_action( 'mphb_sc_checkout_before_form' );

		\MPHB\Views\Shortcodes\CheckoutView::renderCheckoutForm( $this->booking, $this->roomDetails );

		do_action( 'mphb_sc_checkout_after_form' );
	}

	public function enqueueScripts(){

		if ( !$this->isValidStep ) {
			return;
		}

		$checkoutData = array();

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$checkoutData['deposit_amount'] = $this->booking->calcDepositAmount();
		}

		$checkoutData['total'] = $this->booking->calcPrice();

		MPHB()->getPublicScriptManager()->addCheckoutData( $checkoutData );

		foreach ( MPHB()->gatewayManager()->getListActive() as $gateway ) {
			MPHB()->getPublicScriptManager()->addGatewayData( $gateway->getId(), $gateway->getCheckoutData( $this->booking ) );
		}

		wp_enqueue_script( 'mphb-jquery-serialize-json' );
		MPHB()->getPublicScriptManager()->enqueue();
	}

}
