<?php
/**
* Cancelled subscribers email digest (plain text)
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "= " . $email_heading . " =\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
?>

Here is a list of the CANCELLED subscribers during the period of <?php echo $subscribers->getTimeRangeString(); ?>.

<?php
$count = 1;
foreach( $subscribers->getSubscribers() as $subscriber ) {
	echo "$count. {$subscriber['billing_full_name']}";
			echo " - {$subscriber['billing_email']}";
			echo " - Ended: {$subscriber['date_end']}";
			echo "\n";
	$count++;
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );