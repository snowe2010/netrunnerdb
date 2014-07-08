if (typeof NRDB != "object")
	var NRDB = { data_loaded: $.Callbacks() };

NRDB.deck_gallery = {};
(function(deck_gallery) {
	var codes = null;

	deck_gallery.update = function() {

		codes = [ Identity.code ];
		qtys = [ 1 ];
		NRDB.data.cards({
			indeck : {
				'gt' : 0
			},
			type_code : {
				'!is' : 'identity'
			}
		}).order('type_code,title').each(function(record) {
			codes.push(record.code);
			qtys.push(record.indeck);
		});
		for (var i = 0; i < codes.length; i++) {
			var cell = $('<td><div><img src="/web/bundles/netrunnerdbcards/images/cards/en/' + codes[i] + '.png"><div>'+qtys[i]+'</div></div></td>');
			$('#deck_gallery tr').append(cell.data('index', i));
		}
	}

})(NRDB.deck_gallery);

$(function() {

});
