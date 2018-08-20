<?php
namespace WCEmailDigest;

class Subscribers {
	
	/**
	 * Start time
	 * @var DateTime
	 */
	public $startTime;

	/**
	 * End time
	 * @var DateTime
	 */
	public $endTime;

	/**
	 * WP Query arguments
	 * @var array
	 */
	public $queryArgs;

	/**
	 * List of subscribers
	 * @var array Array of WC_Subscription objects
	 */
	public $subscribers = [];

	/**
	 * Constructor
	 * @param array       $statuses  Post statuses to include
	 * @param string      $startTime strtotime compatible string
	 * @param string      $endTime   Strtotime compatible string
	 * @param int|integer $number    Maximum number of posts
	 */
	public function __construct(array $statuses = ['active'], string $startTime = '-24 hours', $endTime = 'now', int $number = -1) {

		$this->startTime = new \DateTime($startTime);
		$this->endTime = new \DateTime($endTime);
		$statuses = empty($statuses) ? ['wc-active'] : $statuses;
		$number = $number <= 0 ? -1 : $number;

		foreach( $statuses as $key => $status ) {
			if( substr($status, 0, 3) == 'wc-') continue;
			$statuses[$key] = 'wc-' . $status;
		}

		$this->queryArgs = [
			'post_type' => 'shop_subscription',
			'posts_per_page' => $number,
			'post_status' => $statuses,
			'orderby' => 'post_date',
			'order' => 'ASC',
			'date_query' => [
				[
					'after' => $startTime,
					'before' => $endTime
				],
				'inclusive' => true
			]
		];

		$this->setSubscribers();

	}

	/**
	 * Check if any subscribers returned from query
	 * @return boolean True if subscribers available
	 */
	public function hasSubscribers() {
		return !empty($this->subscribers);
	}

	/**
	 * Perform the subscriber query
	 */
	public function setSubscribers() {
		$posts = new \WP_Query($this->queryArgs);
		if( !$posts->have_posts() ) return;
		foreach( $posts->posts as $post ) {
			$this->subscribers[] = new \WC_Subscription($post->ID);
		}
	}

	/**
	 * Get subscribers
	 * @return array Each element contains customer's data
	 */
	public function getSubscribers() {
		$customers = [];
		foreach( $this->subscribers as $subscriber ) {
			$customers[] = [
				'id' => $subscriber->get_id(),
				'billing_first_name' => $subscriber->get_billing_first_name(),
				'billing_last_name' => $subscriber->get_billing_last_name(),
				'billing_full_name' => $subscriber->get_formatted_billing_full_name(),
				'billing_email' => $subscriber->get_billing_email(),
				'date_created' => date('m/d/y', $subscriber->get_time('date_created', get_option('timezone_string'))),
				'date_end' => date('m/d/y', $subscriber->get_time('end', get_option('timezone_string'))),
				'status' => $subscriber->get_status()
			];
		}
		return $customers;
	}

	/**
	 * Get a time range string based on queried date range
	 * @param  string $dateFormat Valid PHP date format
	 * @param  string $timeFormat Valid PHP date format
	 * @return string
	 */
	public function getTimeRangeString($dateFormat = 'M. jS, Y', $timeFormat = 'g:ia') {
		return $this->startTime->format($dateFormat) . ' at ' . $this->startTime->format($timeFormat) . ' to ' . $this->endTime->format($dateFormat) . ' at ' . $this->endTime->format($timeFormat);
	}

}