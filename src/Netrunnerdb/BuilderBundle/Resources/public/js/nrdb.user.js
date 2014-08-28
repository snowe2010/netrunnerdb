if (typeof NRDB != "object")
	var NRDB = { data_loaded: jQuery.Callbacks() };

NRDB.user = {};
(function(user, $) {

	user.params = {};
	user.deferred = $.Deferred().always(function() {
		if(user.data) {
			user.update();
		} else {
			user.anonymous();
		}
	});
	
	user.query = function () {
		$.ajax(Routing.generate('user_info', user.params), {
			dataType: 'json',
			success: function(data, textStatus, jqXHR) {
				user.data = data;
				user.deferred.resolve();
			},
			error: function(jqXHR, textStatus, errorThrown) {
				user.deferred.reject();
			}
		});
	}
	
	user.retrieve = function () {
		var storedData;
		if(localStorage && (storedData = localStorage.getItem('user'))) {
			user.data = JSON.parse(storedData);
			user.deferred.resolve();
		} else {
			user.query();
		}
	}
	
	user.wipe = function () {
		localStorage.removeItem('user');
	}
	
	user.anonymous = function() {
		user.wipe();
		$('#login').append('<ul class="dropdown-menu"><li><a href="'+Routing.generate('fos_user_security_login')+'">Login or Register</a></li></ul>');
	}
	
	user.update = function() {
		localStorage.setItem('user', JSON.stringify(user.data));
		$('#login').addClass('dropdown').append('<ul class="dropdown-menu"><li><a href="'
				+ Routing.generate('user_profile',{_locale:user.data.locale}) 
				+ '">Profile page</a></li><li><a href="'
				+ user.data.public_profile_url 
				+ '">Decklists</a></li><li><a href="'
				+ Routing.generate('user_comments',{_locale:user.data.locale})
				+ '">Comments</a></li><li><a href="'
				+ Routing.generate('fos_user_security_logout') 
				+ '" onclick="NRDB.user.wipe()">Jack out</a></li></ul>');
	}
	
	$(function() {
		if($.isEmptyObject(user.params)) {
			user.retrieve()
		} else {
			user.query();
		}
	});
	
})(NRDB.user, jQuery);

