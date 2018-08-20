<?php
namespace WCEmailDigest;

class CancelledSubscriptionsDigestEmail extends DigestEmail {
	
	/**
	 * Cron hook name
	 * @var string
	 */
	public static $cronHook = 'email_digest_cancelled_subscriptions';

	/**
	 * Subscriber status
	 * @var array
	 */
	public $subscriberStatus;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id = 'wc_digest_cancelled_subscriptions';
		$this->title = 'Cancelled Subscription Digest';
		$this->description = 'Recurring summary of cancelled and expired subscriptions.';
		$this->heading = 'Cancelled Subscription Digest';
		$this->subject = 'Cancelled Subscription Digest';
		$this->template_html = 'emails/cancelled-subscriptions-digest.php';
		$this->template_plain = 'emails/plain/cancelled-subscriptions-digest.php';

		$this->subscriberStatus = ['cancelled', 'expired'];
		
		parent::__construct();

		add_action('woocommerce_update_options_email_' . $this->id, [$this, 'process_admin_options']);
	}
}