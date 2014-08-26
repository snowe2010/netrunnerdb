if (typeof NRDB != "object")
	var NRDB = { data_loaded: jQuery.Callbacks() };

NRDB.user = {};
(function(user, $) {

	user.params = {};

	$(function () {
		
		user.deferred = $.ajax(Routing.generate('user_info', user.params), {
			dataType: 'json',
			success: function(data, textStatus, jqXHR) {
				user.data = data;
			},
			error: function(jqXHR, textStatus, errorThrown) {
			} 
		});
		
		$.when(user.deferred).then(function() {
			if(user.data) {
				user.update();
			} else {
				user.anonymous();
			}
		});

	});
	
	user.anonymous = function() {
		$('#login').append('<ul class="dropdown-menu"><li><a href="'+Routing.generate('fos_user_security_login')+'">Login or Register</a></li></ul>');
	}
	
	user.update = function() {
		$('#login').addClass('dropdown').append('<ul class="dropdown-menu"><li><a href="'
				+ Routing.generate('user_profile',{_locale:user.data.locale}) 
				+ '">Profile page</a></li><li><a href="'
				+ user.data.public_profile_url 
				+ '">Decklists</a></li><li><a href="'
				+ Routing.generate('user_comments',{_locale:user.data.locale})
				+ '">Comments</a></li><li><a href="'
				+ Routing.generate('fos_user_security_logout') 
				+ '">Jack out</a></li></ul>');
	}
	
})(NRDB.user, jQuery);

