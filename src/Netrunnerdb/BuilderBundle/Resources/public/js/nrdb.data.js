if (typeof NRDB != "object")
	var NRDB = { 
		data_loaded: $.Callbacks(), 
		api_url: {
			sets: 'http://netrunnerdb.com/api/sets/',
			cards: 'http://netrunnerdb.com/api/cards/'
		}
	};
NRDB.data = {};
(function(data) {
	data.locale = 'en';
	data.sets = {};
	data.cards = {};

	var sets_data = null;
	var cards_data = null;
	var is_modified = null;

	data.query = function() {
		data.initialize();
		data.promise_sets = $
				.ajax(NRDB.api_url.sets+"?jsonp=NRDB.data.parse_sets&_locale="
						+ data.locale);
		data.promise_cards = $
				.ajax(NRDB.api_url.cards+"?jsonp=NRDB.data.parse_cards&_locale="
						+ data.locale);
		$.when(data.promise_sets, data.promise_cards).done(data.initialize);
	}

	data.initialize = function() {
		if (is_modified === false)
			return;

		sets_data = sets_data
				|| JSON.parse(localStorage
						.getItem('sets_data_' + data.locale));
		if(!sets_data) return;
		data.sets = TAFFY(sets_data);
		data.sets.sort("cyclenumber,number");

		cards_data = cards_data
				|| JSON
						.parse(localStorage
								.getItem('cards_data_' + data.locale));
		if(!cards_data) return;
		data.cards = TAFFY(cards_data);
		data.cards.sort("code");
		
		NRDB.data_loaded.fire();
	}

	data.parse_sets = function(response) {
		var json = JSON.stringify(sets_data = response);
		is_modified = is_modified
				|| json != localStorage.getItem("sets_data_" + data.locale);
		localStorage.setItem("sets_data_" + data.locale, json);
	}

	data.parse_cards = function(response) {
		var json = JSON.stringify(cards_data = response);
		is_modified = is_modified
				|| json != localStorage.getItem("cards_data_" + data.locale);
		localStorage.setItem("cards_data_" + data.locale, json);
	}

})(NRDB.data);

$(function() {
	NRDB.data.query();
})

