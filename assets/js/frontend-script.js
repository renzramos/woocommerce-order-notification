jQuery(document).ready(function($){

	var refreshIntervalId  = setInterval(rerun,1000);
    var notificationContainerCurrentView = 0;
    var notificationContainer = $('.won-container .notification-container');
    var notificationCount = notificationContainer.find('.notification').length;
    
	function rerun(){
	    
	    console.log(notificationContainerCurrentView);
	    
	    
		clearInterval(refreshIntervalId);
		notificationContainer.toggleClass('show');
		
		notificationContainer.find('.notification').hide();
		notificationContainer.find('.notification-' + notificationContainerCurrentView).show();

		setTimeout(function(){
			notificationContainer.removeClass('show');
			
			notificationContainerCurrentView++;
		
    		if (notificationContainerCurrentView >= notificationCount){
    		    notificationContainerCurrentView = 0;
    		}
    		
			refreshIntervalId  = setInterval(rerun,3000);
		}, 10000 );
		
		
	}
});