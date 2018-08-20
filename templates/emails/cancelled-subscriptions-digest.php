<?php
/**
 * New subscribers email digest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p>Here is a list of the <strong>cancelled subscribers</strong> during the period of <?php echo $subscribers->getTimeRangeString(); ?>.</p>

<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th>End Date</th>
			<th>Link</th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach( $subscribers->getSubscribers() as $subscriber ) {
		echo '<tr>';
			echo "<td>{$subscriber['billing_full_name']}</td>";
			echo "<td>{$subscriber['billing_email']}</td>";
			echo "<td>{$subscriber['date_end']}</td>";
			echo '<td><a href=' . admin_url('post.php?post=' . $subscriber['id'] . '&action=edit') . '>View</a></td>';
		echo '</tr>';
	}
	?>
	</tbody>
</table>

<?php
/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
