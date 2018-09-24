<?php
namespace WCEmailDigest;
use WCEmailDigest\Subscribers;

class DigestEmail extends \WC_Email {
	/**
	 * Instance of WCEmailDigest\Subscribers
	 * @var WCEmailDigest\Subscribers
	 */
	public $subscribers;

	/**
	 * Send frequency
	 * @var string
	 */
	public $schedule;

	/**
	 * Message receipient(s)
	 * @var string
	 */
	public $recipient;

	public function __construct() {
		$this->template_base = plugin_dir_path(__DIR__) . 'templates/';

		parent::__construct();

		$this->schedule = $this->get_option('schedule');
		$this->recipient = $this->get_option('recipient') ?: get_option('admin_email');
	}

	/**
	 * Save admin options and maybe set CRON schedule
	 * @return void
	 */
	public function process_admin_options() {
		parent::process_admin_options();

		if( $this->is_enabled() ) {
			$this->maybeSchedule();
		}
		else {
			$this->unschedule();
		}

		if( $this->get_option('send_now') == 'yes' ) {
			$this->trigger();
			$this->update_option( 'send_now', 'no' );
		}
	}

	/**
	 * Form fields to display in admin
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled' => [
				'title' => 'Enable/Disable',
				'type' => 'checkbox',
				'label' => 'Enable this email digest',
				'default' => 'no'
			],
			'recipient' => [
				'title'         => 'Recipient(s)',
				'type'          => 'text',
				'description'   => 'Enter recipients (comma separated) for this email. Defaults to ' . get_option( 'admin_email' ),
				'placeholder'   => '',
				'default'       => '',
			],
			'schedule' => [
				'type' => 'select',
				'title' => 'Scheduled Frequency',
				'description' => 'Weekly emails will be sent on Mondays. All emails are sent at 7:00am.',
				'options' => [
					'daily' => 'Daily',
					'weekly' => 'Weekly'
				],
				'default' => 'daily'
			],
			'email_type' => [
				'title'       => 'Email type',
				'type'        => 'select',
				'description' => 'Choose which format of email to send.',
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			],
			'send_now' => [
				'title' => 'Send now?',
				'type' => 'checkbox',
				'label' => 'Yes, send the email digest on save',
				'default' => 'no'
			]
		];
	}

	/**
	 * Send the email
	 * @return void
	 */
	public function trigger() {
		if( !$this->is_enabled() || !$this->recipient ) return;

		$startTime = $this->schedule == 'daily' ? '-24 hours' : '-1 week';

		if( ENV == 'dev' ) $startTime = '-10 years';

		$this->subscribers = new Subscribers($this->subscriberStatus, $startTime);
		if( !$this->subscribers->hasSubscribers() ) {
			return;
		}

		$this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
	}

	/**
	 * Get HTML email
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template($this->template_html, [
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'	=> false,
			'email'			=> $this,
			'subscribers'	=> $this->subscribers
		], '', $this->template_base);
		return ob_get_clean();
	}

	/**
	 * Get plain text email
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template($this->template_plain, [
			'email_heading'	=> $this->get_heading(),
			'sent_to_admin'	=> true,
			'plain_text'	=> true,
			'email'			=> $this,
			'subscribers'	=> $this->subscribers,
		], '', $this->template_base);
		return ob_get_clean();
	}

	/**
	 * Make sure cron schedule is up to date
	 * @return void
	 */
	public function maybeSchedule() {
		$scheduledTimestamp = wp_next_scheduled(static::$cronHook);
		if( !$scheduledTimestamp ) {
			$this->schedule();
			return;
		}

		$cronJobs = _get_cron_array();
		$job = $cronJobs[$scheduledTimestamp][static::$cronHook];

		//If the schedule doesn't match, reschedule
		if( $job['schedule'] !== $this->get_option('schedule') ) {
			$this->unschedule();
			$this->schedule();
		}
	}

	/**
	 * Get scheduling parameters
	 * @return array
	 */
	public function getCronScheduleParams() {
		$schedule = defined('ENV') && ENV === 'dev' ? 'five_minutes' : $this->schedule;

		$firstRun = current_time('timestamp', 1);
		if( $schedule !== 'five_minutes' ) {
			$firstRun = $schedule == 'daily' ? strtotime('tomorrow 7 a.m.') : strtotime('next monday 7 a.m.');
		}

		return [
			$schedule,
			$firstRun,
			static::$cronHook
		];
	}

	/**
	 * Schedule CRON event
	 * @return void
	 */
	public function schedule() {
		$params = $this->getCronScheduleParams();
		return wp_schedule_event(...$params);
	}

	/**
	 * Unschedule cron event
	 * @return void
	 */
	public function unschedule() {
		$scheduledTimestamp = wp_next_scheduled(static::$cronHook);
		if( !$scheduledTimestamp ) return;
		return wp_unschedule_event($scheduledTimestamp, static::$cronHook);
	}
}
