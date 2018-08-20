<?php
namespace WCEmailDigest;

class NewSubscriptionsDigestEmail extends DigestEmail {
	
	/**
	 * Cron hook name
	 * @var string
	 */
	public static $cronHook = 'email_digest_new_subscriptions';

	/**
	 * Subscriber status
	 * @var array
	 */
	public $subscriberStatus;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id = 'wc_digest_new_subscription';
		$this->title = 'New Subscription Digest';
		$this->description = 'Recurring summary of new subscriber details.';
		$this->heading = 'New Subscription Digest';
		$this->subject = 'New Subscription Digest';
		$this->template_html = 'emails/new-subscriptions-digest.php';
		$this->template_plain = 'emails/plain/new-subscriptions-digest.php';

		$this->subscriberStatus = ['active'];
		
		parent::__construct();

		add_action('woocommerce_update_options_email_' . $this->id, [$this, 'process_admin_options']);
	}
}