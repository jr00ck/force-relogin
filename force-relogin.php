<?php
/*
Plugin Name: Force Re-log In
Description: Uses AJAX to check if user is still logged in and kicks them out via a dialog with a countdown that sends them to the WP login page.
Version: 3.0.2
Plugin URI: https://github.com/jr00ck/force-relogin
Author: FreeUp
Author URI: http://freeupwebstudio.com
Contributors: jr00ck
*/

// Load the heartbeat JS
function ll_heartbeat_enqueue( $hook_suffix ) {

	$plugin_ver 	= '3.0.2';
	$jQueryUIver 	= '1.10.1';

    wp_enqueue_script( 'll-force-relogin-script', plugins_url( 'force-relogin.js', __FILE__ ), array('jquery-ui-dialog'), $plugin_ver );
    wp_enqueue_style( 'll-force-relogin-style', plugins_url( 'force-relogin.css', __FILE__ ), false, $plugin_ver );

    // output localized variables
    wp_localize_script( 'll-force-relogin-script', 'LL', array(
    	// URL to wp-admin/admin-ajax.php to process the request
		'ajaxurl'       => admin_url( 'admin-ajax.php' ),
		'logged_in'		=> is_user_logged_in(),
		'login_url'		=> wp_login_url("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]")
		)
	);

    wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/'.$jQueryUIver.'/themes/smoothness/jquery-ui.min.css', false, $jQueryUIver);
}
add_action( 'wp_enqueue_scripts', 'll_heartbeat_enqueue' );

/*********************************************
	Ajax Handler
**********************************************/

add_action( 'wp_ajax_LL_state', 'LL_ajax_request' );
 
function LL_ajax_request() {

	// ignore the request if the current user isn't logged in
	if ( is_user_logged_in() ) {

		$user_logged_in = is_user_logged_in();

		// generate the response
		$response = json_encode( array( 'user_logged_in' => $user_logged_in ) );

		// response output
		header( "Content-Type: application/json" );
		echo $response;

	}
	// IMPORTANT: don't forget to "exit"
	exit;
}
