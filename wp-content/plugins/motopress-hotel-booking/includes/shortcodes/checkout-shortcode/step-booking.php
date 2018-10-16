<?php

namespace MPHB\Shortcodes\CheckoutShortcode;

use \MPHB\Entities;
use \MPHB\Shortcodes\CheckoutShortcode;

class StepBooking extends Step {

	/**
	 *
	 * @var Entities\Customer
	 */
	protected $customer;

	/**
	 *
	 * @var string
	 */
	protected $gatewayId;

	/**
	 *
	 * @var boolean
	 */
	protected $isCorrectPaymentData = false;

	/**
	 *
	 * @var boolean
	 */
	protected $isCorrectData = false;

	/**
	 *
	 * @var boolean
	 */
	protected $isAlreadyBooked = false;

	/**
	 *
	 * @var boolean
	 */
	protected $unableToCreateBooking = false;

	/**
	 *
	 * @var Entities\ReservedRoom[]
	 */
	private $reservedRooms = array();

	/**
	 *
	 * @var Entities\Booking
	 */
	private $booking;

	/**
	 * Booked before clicking "Back" button in browser (reserved, but not
	 * completed). We can use it's reserved rooms.
	 *
	 * @var Entities\Booking|null
	 */
	private $unfinishedBooking = null;

	/**
	 * Rooms from $unfinishedBooking. Still available if the current checkout ID
	 * equals to $unfinishedBooking->checkoutId.
	 *
	 * @var array [%Room type ID% => [%Room ID%]]
	 */
	private $unfinishedRooms = array();

	/**
	 * Checkout ID (maybe checkout ID of $unfinishedBooking).
	 *
	 * @var string
	 */
	private $checkoutId = '';

	public function setup(){

		$this->isCorrectData = false;

		if ( !$this->parseCheckoutId() || !$this->parseUnfinishedBookingData() ) {
			return;
		}

		if ( !$this->parseCheckInDate() || !$this->parseCheckOutDate() ) {
			return;
		}

		if ( !$this->parseCustomerData() || !$this->parseBookingData() ) {
			if ( $this->isAlreadyBooked ) {
				$this->cleanUnfinished();
			}
			return;
		}

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' && !$this->parsePaymentData() ) {
			return;
		}

		if ( apply_filters( 'mphb_block_booking', false ) ) {
			$this->errors[] = __( 'Booking is blocked due to maintenance reason. Please try again later.', 'motopress-hotel-booking' );
			return;
		}

		$this->isCorrectData = true;

		MPHB()->getSession()->set( 'mphb_checkout_step', \MPHB\Shortcodes\CheckoutShortcode::STEP_BOOKING );

		// First delete previously unfinished booking (to free rooms)
		$this->cleanUnfinished();

		$isCreated = MPHB()->getBookingRepository()->save( $this->booking );

		if ( !$isCreated ) {
			$this->unableToCreateBooking = true;
			return;
		}

		do_action( 'mphb_create_booking_by_user', $this->booking );

		// Update price breakdown ("Price Details")
		$priceBreakdown = $this->booking->getPriceBreakdown();
		array_walk_recursive( $priceBreakdown, function( &$value, $key ) {
			$value = addslashes( $value );
		} );
		update_post_meta( $this->booking->getId(), '_mphb_booking_price_breakdown', json_encode( $priceBreakdown ) );

		MPHB()->getSession()->set( 'mphb_checkout_step', \MPHB\Shortcodes\CheckoutShortcode::STEP_COMPLETE );

		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
			$payment = $this->createPayment( $this->booking );
			$this->booking->setExpectPayment( $payment->getId() );
			MPHB()->gatewayManager()->getGateway( $this->gatewayId )->processPayment( $this->booking, $payment );
		}

		$this->stepValid();
	}

	protected function parseCheckoutId(){
		if ( empty( $_POST[CheckoutShortcode::BOOKING_CID_NAME] ) ) {
			$this->errors[] = __( 'Checkout data is not valid.', 'motopress-hotel-booking' );
			return false;
		}

		$this->checkoutId = mphb_clean( $_POST[CheckoutShortcode::BOOKING_CID_NAME] );

		return true;
	}

	protected function parseUnfinishedBookingData(){
		$this->unfinishedBooking = MPHB()->getBookingRepository()->findByCheckoutId( $this->checkoutId );

		if ( $this->isRebooking() ) {
			foreach ( $this->unfinishedBooking->getReservedRooms() as $reservedRoom ) {
				$this->unfinishedRooms[$reservedRoom->getRoomTypeId()][] = $reservedRoom->getRoomId();
			}
		}

		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseBookingData(){

		if ( empty( $_POST['mphb_room_details'] ) ) {
			$this->errors[] = __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' );
			return false;
		}

		$bookingDetails		 = $_POST['mphb_room_details'];
		$bookingRoomsDetails = array();
		$errors				 = array();

		foreach ( $bookingDetails as $key => $roomDetails ) {

			$roomTypeId = isset( $roomDetails['room_type_id'] ) ? \MPHB\Utils\ValidateUtils::validateInt( $roomDetails['room_type_id'] ) : null;
			if ( !$roomTypeId ) {
				$errors[] = __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' );
				break;
			}

			$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
			if ( !$roomType || $roomType->getStatus() !== 'publish' ) {
				$errors[] = __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' );
				break;
			}

			$rateId = isset( $roomDetails['rate_id'] ) ? \MPHB\Utils\ValidateUtils::validateInt( $roomDetails['rate_id'] ) : null;
			if ( !$rateId ) {
				$errors[] = __( 'Rate is not valid.', 'motopress-hotel-booking' );
				break;
			}

			$rateArgs = array(
				'check_in_date'	 => $this->checkInDate,
				'check_out_date' => $this->checkOutDate,
				'mphb_language'	 => 'original'
			);

			$allowedRates	 = MPHB()->getRateRepository()->findAllActiveByRoomType( $roomType->getOriginalId(), $rateArgs );
			$allowedRatesIds = array_map( function( Entities\Rate $rate ) {
				return $rate->getOriginalId();
			}, $allowedRates );

			if ( !in_array( $rateId, $allowedRatesIds ) ) {
				$errors[] = __( 'Rate is not valid.', 'motopress-hotel-booking' );
				break;
			}

			$adults = isset( $roomDetails['adults'] ) ? \MPHB\Utils\ValidateUtils::validateInt( $roomDetails['adults'] ) : 0;
			if ( $adults === false || $adults < MPHB()->settings()->main()->getMinAdults() || $adults > $roomType->getAdultsCapacity() ) {
				$errors[] = __( 'Adults number is not valid.', 'motopress-hotel-booking' );
				break;
			}

			$children = isset( $roomDetails['children'] ) ? \MPHB\Utils\ValidateUtils::validateInt( $roomDetails['children'] ) : 0;
			if ( $children === false || $children < MPHB()->settings()->main()->getMinChildren() || $children > $roomType->getChildrenCapacity() ) {
				$errors[] = __( 'Children number is not valid.', 'motopress-hotel-booking' );
				break;
			}

			if ( !MPHB()->getRulesChecker()->verify( $this->checkInDate, $this->checkOutDate, $roomTypeId ) ) {
				$this->errors[] = sprintf( __( 'Selected dates do not meet booking rules for type %s', 'motopress-hotel-booking' ), $roomType->getTitle() );
				break;
			}

			$reservedServices = array();

			if ( !empty( $roomDetails['services'] ) && is_array( $roomDetails['services'] ) ) {

				foreach ( $roomDetails['services'] as $serviceDetails ) {
					if ( !isset( $serviceDetails['id'], $serviceDetails['adults'] ) ) {
						continue;
					}

					$serviceId		 = \MPHB\Utils\ValidateUtils::validateInt( $serviceDetails['id'] );
					$serviceAdults	 = \MPHB\Utils\ValidateUtils::validateInt( $serviceDetails['adults'] );
					if ( $serviceId !== false && $serviceAdults !== false && in_array( $serviceId, $roomType->getServices() ) && $serviceAdults > 0 ) {
						$reservedServiceAtts = array(
							'id'	 => $serviceId,
							'adults' => $serviceAdults
						);
						$reservedServices[]	 = Entities\ReservedService::create( $reservedServiceAtts );
					}
				}
			}
			$reservedServices = array_filter( $reservedServices );

			$guestName = isset( $roomDetails['guest_name'] ) ? mphb_clean( $roomDetails['guest_name'] ) : '';

			$bookingRoomsDetails[$key] = array(
				'room_type_id'		 => $roomTypeId,
				'rate_id'			 => $rateId,
				'adults'			 => $adults,
				'children'			 => $children,
				'reserved_services'	 => $reservedServices,
				'guest_name'		 => $guestName
			);
		}

		if ( !empty( $errors ) ) {
			$this->errors = array_merge( $this->errors, $errors );
			return false;
		}

		// allocate rooms
		$availableRooms	 = $this->unfinishedRooms;
		$roomTypesCount	 = array_count_values( wp_list_pluck( $bookingRoomsDetails, 'room_type_id' ) );

		foreach ( $roomTypesCount as $roomTypeId => $roomsCount ) {

			$alreadyHave = empty( $availableRooms[$roomTypeId] ) ? 0 : count( $availableRooms[$roomTypeId] );

			if ( $alreadyHave < $roomsCount ) {
				$lockedRooms = MPHB()->getRulesChecker()->customRules()->getUnavailableRooms( $this->checkInDate, $this->checkOutDate, $roomTypeId );

				$searchAtts = array(
					'from_date'		 => $this->checkInDate,
					'to_date'		 => $this->checkOutDate,
					'count'			 => $roomsCount - $alreadyHave,
					'room_type_id'	 => $roomTypeId,
					'exclude_rooms'	 => $lockedRooms
				);

				$foundRooms = MPHB()->getRoomPersistence()->searchRooms( $searchAtts );

				if ( $alreadyHave == 0 ) {
					$availableRooms[$roomTypeId] = $foundRooms;
				} else {
					$availableRooms[$roomTypeId] = array_merge( $availableRooms[$roomTypeId], $foundRooms );
				}
			}

			if ( count( $availableRooms[$roomTypeId] ) < $roomsCount ) {
				$this->isAlreadyBooked = true;
				break;
			}
		}

		if ( $this->isAlreadyBooked ) {
			return false;
		}

		foreach ( $bookingRoomsDetails as &$roomDetails ) {
			$roomDetails['room_id'] = (int) array_shift( $availableRooms[$roomDetails['room_type_id']] );
			unset( $roomDetails['room_type_id'] );
		}

		$this->reservedRooms = array_filter( array_map( array( 'MPHB\Entities\ReservedRoom', 'create' ), $bookingRoomsDetails ) );

		if ( empty( $this->reservedRooms ) ) {
			$this->errors[] = __( 'There are no accommodations selected for reservation.', 'motopress-hotel-booking' );
			return false;
		}

		$note = !empty( $_POST['mphb_note'] ) ? sanitize_textarea_field( $_POST['mphb_note'] ) : '';

		$bookingAtts = array(
			'check_in_date'	 => $this->checkInDate,
			'check_out_date' => $this->checkOutDate,
			'customer'		 => $this->customer,
			'note'			 => $note,
			'status'		 => MPHB()->postTypes()->booking()->statuses()->getDefaultNewBookingStatus(),
			'reserved_rooms' => $this->reservedRooms,
			'checkout_id'	 => $this->checkoutId
		);

		$booking = Entities\Booking::create( $bookingAtts );

		// @todo check is coupon feature is enabled
		if ( !empty( $_POST['mphb_applied_coupon_code'] ) ) {
			$coupon = MPHB()->getCouponRepository()->findByCode( mphb_clean( $_POST['mphb_applied_coupon_code'] ) );
			if ( $coupon ) {
				$booking->applyCoupon( $coupon );
			}
		}

		$this->booking = $booking;

		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	public function parseRoomRate(){
		$roomRateId = filter_input( INPUT_POST, 'mphb_room_rate_id', FILTER_VALIDATE_INT );

		if ( !$roomRateId ) {
			$this->errors[] = __( 'Rate is not valid.', 'motopress-hotel-booking' );
			return false;
		}

		$rate = null;
		foreach ( $this->allowedRates as $allowedRate ) {
			if ( $allowedRate->getId() === $roomRateId ) {
				$rate = $allowedRate;
			}
		}

		if ( is_null( $rate ) ) {
			$this->errors[] = __( 'Rate is not valid.', 'motopress-hotel-booking' );
			return false;
		}

		$this->roomRate = $rate;

		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function parseCustomerData(){

		$customerData = array(
			'first_name' => '',
			'last_name'	 => '',
			'email'		 => '',
			'phone'		 => '',
			'country'	 => '',
			'state'		 => '',
			'city'		 => '',
			'zip'		 => '',
			'address1'	 => ''
		);

		$input	 = $_POST;
		$errors	 = array();

		foreach ( $customerData as $field => $value ) {
			$customerData[$field] = isset( $input["mphb_{$field}"] ) ? $input["mphb_{$field}"] : '';
			if ( $field === 'email' ) {
				$customerData[$field] = sanitize_email( $customerData[$field] );
			} else if ( $field !== 'note' ) {
				$customerData[$field] = sanitize_text_field( $customerData[$field] );
			}
		}

		if ( empty( $customerData['first_name'] ) ) {
			$errors[] = __( 'First name is required.', 'motopress-hotel-booking' );
		}

		if ( empty( $customerData['last_name'] ) ) {
			$errors[] = __( 'Last name is required.', 'motopress-hotel-booking' );
		}

		if ( empty( $customerData['email'] ) ) {
			$errors[] = __( 'Email is required.', 'motopress-hotel-booking' );
		}

		if ( empty( $customerData['phone'] ) ) {
			$errors[] = __( 'Phone is required.', 'motopress-hotel-booking' );
		}

		if ( MPHB()->settings()->main()->isRequireCountry() && empty( $customerData['country'] ) ) {
			$errors[] = __( 'Country is required.', 'motopress-hotel-booking' );
		}

		if ( MPHB()->settings()->main()->isRequireFullAddress() ) {
			if ( empty( $customerData['state'] ) ) {
				$errors[] = __( 'State is required.', 'motopress-hotel-booking' );
			}

			if ( empty( $customerData['city'] ) ) {
				$errors[] = __( 'City is required.', 'motopress-hotel-booking' );
			}

			if ( empty( $customerData['zip'] ) ) {
				$errors[] = __( 'Postcode is required.', 'motopress-hotel-booking' );
			}

			if ( empty( $customerData['city'] ) ) {
				$errors[] = __( 'Address is required.', 'motopress-hotel-booking' );
			}
		}


		if ( !empty( $errors ) ) {
			$this->errors += $errors;
			$this->customer = null;
		} else {
			$this->customer = new Entities\Customer( $customerData );
		}

		return !is_null( $this->customer );
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parsePaymentData(){
		$this->isCorrectPaymentData = $this->parseGatewayId() && $this->parsePaymentMethodFields();
		return $this->isCorrectPaymentData;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function parseGatewayId(){
		if ( !isset( $_POST['mphb_gateway_id'] ) ) {
			return false;
		}
		$gatewayId = mphb_clean( $_POST['mphb_gateway_id'] );

		if ( $this->booking->getTotalPrice() == 0 && $gatewayId == 'manual' ) {
			// avoid process payment gateways on free bookings
			$this->gatewayId = $gatewayId;
			return true;
		}

		if ( !array_key_exists( $gatewayId, MPHB()->gatewayManager()->getListActive() ) ) {
			$this->errors[] = __( 'Payment method is not valid.', 'motopress-hotel-booking' );
			return false;
		}

		$this->gatewayId = $gatewayId;

		return true;
	}

	protected function parsePaymentMethodFields(){
		$errors = array();

		MPHB()->gatewayManager()->getGateway( $this->gatewayId )->parsePaymentFields( $_POST, $errors );

		if ( !empty( $errors ) ) {
			$this->errors = array_merge( $this->errors, $errors );
			return false;
		}

		return true;
	}

	public function render(){

		if ( !$this->isCorrectData ) {
			$this->showErrorsMessage();
		} else if ( $this->isAlreadyBooked ) {
			$this->showAlreadyBookedMessage();
		} else if ( $this->unableToCreateBooking ) {
			_e( 'Unable to create booking. Please try again.', 'motopress-hotel-booking' );
		} else {
			$this->showSuccessMessage();
		}
	}

	/**
	 *
	 * @param Entities\Booking $booking
	 * @return Entities\Payment|null
	 */
	protected function createPayment( $booking ){

		$gateway = MPHB()->gatewayManager()->getGateway( $this->gatewayId );

		$paymentData = array(
			'gatewayId'		 => $gateway->getId(),
			'gatewayMode'	 => $gateway->getMode(),
			'bookingId'		 => $booking->getId(),
			'amount'		 => $booking->calcDepositAmount(),
			'currency'		 => MPHB()->settings()->currency()->getCurrencyCode(),
		);

		$payment	 = Entities\Payment::create( $paymentData );
		$isCreated	 = MPHB()->getPaymentRepository()->save( $payment );

		if ( $isCreated ) {
			$gateway->storePaymentFields( $payment );
			// Re-get payment. Some gateways may update metadata without entity update.
			$payment = MPHB()->getPaymentRepository()->findById( $payment->getId(), true );
		}

		return $isCreated ? $payment : null;
	}

	protected function cleanUnfinished(){
		if ( is_null( $this->unfinishedBooking ) ) {
			return;
		}

		$paymentId = get_post_meta( $this->unfinishedBooking->getId(), '_mphb_wait_payment', true );
		$payment = MPHB()->getPaymentRepository()->findById( $paymentId );

		if ( !is_null( $payment ) ) {
			MPHB()->getPaymentRepository()->delete( $payment );
		}

		MPHB()->getBookingRepository()->delete( $this->unfinishedBooking );
	}

	public function isRebooking(){
		return !is_null( $this->unfinishedBooking );
	}

}
