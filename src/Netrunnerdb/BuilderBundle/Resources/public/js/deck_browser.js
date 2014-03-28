NRDB.deck_browser = {};
(function(deck_browser) {
	var codes = null;

	function switch_left() {

		$('#deck_browser_left div:visible').last().hide();
		$('#deck_browser_right div:hidden').first().show();
		var focus = $('#deck_browser_center div:visible');
		focus.prev().show();
		focus.hide();
	
	}

	function switch_right() {

		$('#deck_browser_right div:visible').last().hide();
		$('#deck_browser_left div:hidden').first().show();
		var focus = $('#deck_browser_center div:visible');
		focus.next().show();
		focus.hide();
	
	}

	function focus_to(event) {
		var index = $(this).data('index');
		focus_index(index);
	}

	function focus_index(index) {
		$('#deck_browser_left > div').each(function (i, elt) {
			if(i < index) $(elt).show();
			else $(elt).hide();
		});
		$('#deck_browser_center > div').each(function (i, elt) {
			if(i == index) $(elt).show();
			else $(elt).hide();
		});
		$('#deck_browser_right > div').each(function (i, elt) {
			if(codes.length - 1 - i > index) $(elt).show();
			else $(elt).hide();
		});
	}
	
	deck_browser.update = function() {

		codes = [ Identity.code ];
		CardDB({
			indeck : {
				'gt' : 0
			},
			type_code : {
				'!is' : 'identity'
			}
		}).order('type_code,title').each(function(record) {
			for (var i = 0; i < record.indeck; i++) {
				codes.push(record.code);
			}
		});
		for (var i = 0; i < codes.length; i++) {
			var div = $('<div><img src="/web/bundles/netrunnerdbcards/images/cards/en/' + codes[i] + '.png"></div>');
			$('#deck_browser_left').append(div.data('index', i));
			$('#deck_browser_center').append(div.clone().data('index', i));
			$('#deck_browser_right').prepend(div.clone().data('index', i));
		}
		$('#deck_browser').on({
			click : focus_to
		}, 'div');
		focus_index(0);
	}

})(NRDB.deck_browser);

$(function() {

});
