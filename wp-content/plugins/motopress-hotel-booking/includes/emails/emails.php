<?php

namespace MPHB\Emails;

use \MPHB\PostTypes\BookingCPT;

class Emails {

	/**
	 *
	 * @var Mailer
	 */
	private $mailer;

	/**
	 *
	 * @var AbstractEmail[]
	 */
	private $emails;

	/**
	 *
	 * @var Templaters\ReservedRoomsTemplater
	 */
	private $reservedRoomsTemplater;

	/**
	 *
	 * @var Templaters\CancellationBookingTemplater
	 */
	private $cancellationTemplater;

	public function __construct(){

		$this->mailer = new Mailer();

		$this->reservedRoomsTemplater	 = new Templaters\ReservedRoomsTemplater();
		$this->cancellationTemplater	 = new Templaters\CancellationBookingTemplater();

		$this->initEmails();

		$this->addActions();
	}

	private function initEmails(){

		$emails = array();

		$emails[] = new Booking\Admin\PendingEmail( array(
			'id' => 'admin_pending_booking'
			), Templaters\EmailTemplater::create( array(
				'booking' => true
			) )
		);

		$emails[] = new Booking\Admin\ConfirmedEmail( array(
			'id' => 'admin_customer_confirmed_booking'
			), Templaters\EmailTemplater::create( array(
				'booking'			 => true,
				'user_cancellation'	 => true
			) )
		);

		$emails[] = new Booking\Admin\CancelledEmail( array(
			'id' => 'admin_customer_cancelled_booking'
			), Templaters\EmailTemplater::create( array(
				'booking' => true
			) )
		);

		$emails[] = new Booking\Admin\ConfirmedByPaymentEmail( array(
			'id' => 'admin_payment_confirmed_booking'
			), Templaters\EmailTemplater::create( array(
				'booking'	 => true,
				'payment'	 => true
			) )
		);

		$emails[] = new Booking\Customer\CancelledEmail( array(
			'id' => 'customer_cancelled_booking'
			), Templaters\EmailTemplater::create( array(
				'booking' => true
			) )
		);

		$emails[] = new Booking\Customer\PendingEmail( array(
			'id' => 'customer_pending_booking'
			), Templaters\EmailTemplater::create( array(
				'booking'			 => true,
				'user_cancellation'	 => true
			) )
		);

		$emails[] = new Booking\Customer\ConfirmationEmail( array(
			'id' => 'customer_confirmation_booking',
			), Templaters\EmailTemplater::create( array(
				'booking'			 => true,
				'user_confirmation'	 => true,
				'user_cancellation'	 => true
			) )
		);

		$emails[] = new Booking\Customer\ApprovedEmail( array(
			'id' => 'customer_approved_booking',
			), Templaters\EmailTemplater::create( array(
				'booking'			 => true,
				'user_cancellation'	 => true
			) )
		);

		array_map( array( $this, 'addEmail' ), $emails );
	}

	/**
	 *
	 * @param \MPHB\Emails\AbstractEmail $email
	 */
	public function addEmail( AbstractEmail $email ){
		$this->emails[$email->getId()] = $email;
	}

	/**
	 *
	 * @param string $id
	 * @return AbstractEmail|null
	 */
	public function getEmail( $id ){
		return isset( $this->emails[$id] ) ? $this->emails[$id] : null;
	}

	private function addActions(){

		add_action( 'mphb_booking_status_changed', array( $this, 'sendBookingMails' ), 10, 2 );

		add_action( 'mphb_booking_confirmed_with_payment', array( $this, 'sendBookingConfirmedWithPaymentEmail' ), 10, 2 );
		add_action( 'mphb_customer_confirmed_booking', array( $this->getEmail( 'admin_customer_confirmed_booking' ), 'trigger' ) );
		add_action( 'mphb_customer_cancelled_booking', array( $this->getEmail( 'admin_customer_cancelled_booking' ), 'trigger' ) );

		add_action( 'current_screen', array( $this, 'showDeprecatedTagsNotice' ) );
	}

	public function showDeprecatedTagsNotice(){

		$notices = array();
		foreach ( $this->emails as $email ) {
			$notices = array_merge( $notices, $email->getDeprecatedNotices() );
		}

		MPHB()->getSettingsMenuPage();

		return $notices;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 */
	public function sendBookingConfirmedWithPaymentEmail( $booking, $payment ){
		$this->getEmail( 'admin_payment_confirmed_booking' )->trigger( $booking, array(
			'payment' => $payment
		) );
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function sendBookingMails( $booking, $oldStatus ){
		switch ( $booking->getStatus() ) {
			case BookingCPT\Statuses::STATUS_PENDING:
				// Send mails only on confirmation-by-admin mode
				if ( MPHB()->settings()->main()->getConfirmationMode() == 'manual' ) {
					$this->getEmail( 'admin_pending_booking' )->trigger( $booking );
					$this->getEmail( 'customer_pending_booking' )->trigger( $booking );
				}
				break;
			case BookingCPT\Statuses::STATUS_PENDING_USER:
				// Send mail only on confirmation-by-customer mode
				if ( MPHB()->settings()->main()->getConfirmationMode() == 'auto' ) {
					$this->getEmail( 'customer_confirmation_booking' )->trigger( $booking );
				}
				break;
			case BookingCPT\Statuses::STATUS_CONFIRMED:
				$this->getEmail( 'customer_approved_booking' )->trigger( $booking );
				break;
			case BookingCPT\Statuses::STATUS_CANCELLED:
				$this->getEmail( 'customer_cancelled_booking' )->trigger( $booking );
				break;
		}
	}

	/**
	 *
	 * @return Mailer
	 */
	public function getMailer(){
		return $this->mailer;
	}

	/**
	 *
	 * @return Templaters\ReservedRoomsTemplater
	 */
	public function getReservedRoomsTemplater(){
		return $this->reservedRoomsTemplater;
	}

	/**
	 *
	 * @return Templaters\CancellationBookingTemplater
	 */
	public function getCancellationTemplater(){
		return $this->cancellationTemplater;
	}

}
