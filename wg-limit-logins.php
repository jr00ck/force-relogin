<?php
/*
Plugin Name: WG Limit Logins
Description: Prevent multiple users from sharing the same account. Using Heartbeat API to kick out old logins.
Version: 2.1
Author: Web Guys
Author URI: http://webguysaz.com
Contributors: jr00ck
*/


/**
 * Stores cookie value in a transient when a user logs in
 *
 * Transient IDs are based on the user ID so that we can track the number of
 * users logged into the same account
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

const VERSION = '2.1';

function wg_set_user_logged_in_status( $logged_in_cookie, $expire, $expiration, $user_id, $status = 'logged_in' ) {

	if ( ! empty( $user_id ) ) :

		$data = get_transient( 'wg_user_logged_in_' . $user_id );

		if( false === $data )
			$data = array();

		$data[] = $logged_in_cookie;

		set_transient( 'wg_user_logged_in_' . $user_id, $data );

	endif;
}
add_action( 'set_logged_in_cookie', 'wg_set_user_logged_in_status', 10, 5 );



/**
 * Checks if a user is allowed to be logged-in
 *
 * The transient related to the user is retrieved and the first cookie in the transient
 * is compared to the LOGGED_IN_COOKIE of the current user.
 *
 * The first cookie in the transient is the oldest, so it is the one that gets logged out
 *
 * We only log a user out if there are more than 2 users logged into the same account
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function wg_can_user_be_logged_in() {
	if ( is_user_logged_in() ) :

		$user_id = get_current_user_id();

		$already_logged_in = get_transient( 'wg_user_logged_in_' . $user_id );

		if( $already_logged_in !== false ) :

			$data = maybe_unserialize( $already_logged_in );

			if( count( $data ) < 2 )
				return; // user still logged in and is only one under that username

			// remove all but the latest user to log in
			$data = array_slice($data, count($data)-1);

			if( ! in_array( $_COOKIE[LOGGED_IN_COOKIE], $data ) ) :

				set_transient( 'wg_user_logged_in_' . $user_id, $data );

				// Log the user out - this is the oldest user logged into this account
				wp_logout();
				wp_safe_redirect( trailingslashit( get_bloginfo( 'wpurl' ) ) . 'wp-login.php?loggedout=true' );

			endif;

		endif;
		
	endif;
}
add_action( 'init', 'wg_can_user_be_logged_in' );

/***********************************************************************
* NEW CODE THAT USES HEARTBEAT API TO LOGOUT AN ALREADY LOGGED IN USER *
************************************************************************/

// Load the heartbeat JS
function wg_heartbeat_enqueue( $hook_suffix ) {
    // Make sure the JS part of the Heartbeat API is loaded.
    wp_enqueue_script( 'heartbeat' );
    wp_enqueue_script( 'wg-limit-logins', plugins_url( 'wg-limit-logins.js', __FILE__ ), array('jquery-ui-dialog'), VERSION );
    wp_enqueue_style( 'wg-limit-logins', plugins_url( 'wg-limit-logins.css', __FILE__ ), false, VERSION );
    // output localized variables
    wp_localize_script( 'wg-limit-logins', 'WGLL', array(
		'logged_in'		=> is_user_logged_in(),
		'login_url'		=> wp_login_url("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]")
		)
	);

    $jQueryUIver = '1.10.1';
    wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/'.$jQueryUIver.'/themes/smoothness/jquery-ui.min.css', false, $jQueryUIver);
}
add_action( 'wp_enqueue_scripts', 'wg_heartbeat_enqueue' );


// Modify the data that goes back with the heartbeat-tick
function wg_heartbeat_received( $response, $data ) {
 
 		// Call function to check if user is still logged in
        wg_can_user_be_logged_in();
        
        // Send back if user is still logged in or not (not needed)
        // $response['wg-logged-in'] = $logged_in ?: false;
 
    // }
    return $response;
}
add_filter( 'heartbeat_received', 'wg_heartbeat_received', 10, 2 );