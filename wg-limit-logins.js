(function($){

    // Hook into the heartbeat-send
    $(document).on('heartbeat-send', function(e, data) {
        data['wg_heartbeat'] = 'stay_logged_in';
    });

    // Listen for the custom event "heartbeat-tick" on $(document).
    $(document).on( 'heartbeat-tick', function(e, data) {

        // Check if response says user is still logged in
        if ( data['wp-auth-check'] === false ) {

            if(WGLL.logged_in == true){
            	
            	WGLL.logged_in = false;
            	showDialog();
        	}
        }
    });

    function showDialog(){
    	var count = 30; // logout timer count
		$('body').append('<div id="wg-dialog-message"><i class="icon-exclamation-sign icon-4x pull-left"></i>\
				<p>You have been logged out. This is likely because your username has logged in from another device.</p>\
				<p id="wg-timer-redirect">Redirecting to login page in <span id="wg-timer">' + count + '</span></p>\
			</div>');
    	$('#wg-dialog-message').dialog({
    		modal: true,
    		buttons: {
    			Login: function(){
    				// redirect to login page w/ redirect back to current page
    				location.href=WGLL.login_url;
    			}
    		},
    		draggable: false,
    		closeOnEscape: false,
    		resizable: false,
    		title: 'Logged Out',
    		beforeClose: function (event, ui) { return false; },
    		dialogClass: "noclose"
    	});
    	startCountdown(count);
    }

    function startCountdown(count){

		var counter=setInterval(function(){
			count=count-1;
			if (count <= 0) {
				clearInterval(counter);
				//counter ended, redirect
				location.href=WGLL.login_url;
			}
  			$('#wg-timer').html(count);
		}, 1000); //1000 will run it every 1 second
    }
}(jQuery));
