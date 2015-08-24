(function($){

"use strict";

    $(function() {

        // try something else besides heartbeat, use our own interval
        var counter = setInterval(function(){
            
            // if logged in on last check, check again
            if(LL.logged_in == true){
                check_logged_in_status();
            } else { // not logged in, don't need to check
                clearInterval(counter);
            }
        }, 300000); // runs every 5 minutes
    });

    function check_logged_in_status(){

        // use ajax to check logged in status
        console.log('gonna run some ajax');
        $.get(
            LL.ajaxurl,
            {
                // trigger LL_state on backend
                action : 'LL_state'
            }
        ).done(function(data){

            // check if response says user is no longer logged in
            if(!data.user_logged_in){

                console.log(data);
                LL.logged_in = false;
                showDialog();
            }
        });


    }

    function showDialog(){
    	var count = 30; // logout timer count
		$('body').append('<div id="ll-dialog-message"><i class="icon-exclamation-sign icon-4x pull-left"></i>\
				<p>You have been logged out. This is likely because your username has logged in from another device.</p>\
				<p id="ll-timer-redirect">Redirecting to login page in <span id="ll-timer">' + count + '</span></p>\
			</div>');
    	$('#ll-dialog-message').dialog({
    		modal: true,
    		buttons: {
    			Login: function(){
    				// redirect to login page w/ redirect back to current page
    				location.href=LL.login_url;
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
				location.href=LL.login_url;
			}
  			$('#ll-timer').html(count);
		}, 1000); //1000 will run it every 1 second
    }
}(jQuery));
