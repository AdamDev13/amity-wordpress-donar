<?php
/**
 * Profile Editor
 *
 * This template is used to display the profile editor with [custom_donation_dashboard]
 *
 * @copyright    Copyright (c) 2016, GiveWP
 * @license      https://opensource.org/licenses/gpl-license GNU Public License
 */


function create_shortcode(){

$current_user = wp_get_current_user();

if ( is_user_logged_in() ) :
	$user_id      = get_current_user_id();
	$first_name   = get_user_meta( $user_id, 'first_name', true );
	$last_name    = get_user_meta( $user_id, 'last_name', true );
	$last_name    = get_user_meta( $user_id, 'last_name', true );
	$display_name = $current_user->display_name;
	$donor        = new Give_Donor( $user_id, true );
	$address      = $donor->get_donor_address( array( 'address_type' => 'personal' ) );
	$company_name = $donor->get_meta( '_give_donor_company', true );

	if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] && ! give_get_errors() ) {
		if ( isset( $_GET['update_code'] ) ) {
			if ( 1 === absint( $_GET['update_code'] ) ) {
				printf( '<p class="give_success"><strong>%1$s</strong> %2$s</p>', esc_html__( 'Success:', 'give' ), esc_html__( 'Your profile has been updated.', 'give' ) );
			}
		}
	}

	Give()->notices->render_frontend_notices( 0 );
?>


farrukh

<?php
print_r(Give());

}

add_shortcode('custom_donation_dashboard', 'create_shortcode');