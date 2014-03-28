function when_all_parsed() {
	if (CardDB && IsModified === false)
		return;
	var sets_data = SetsData
			|| JSON.parse(localStorage.getItem('sets_data_' + Locale));
	if (!sets_data) {
		return;
	}
	SetDB = TAFFY(sets_data);
	SetDB.sort("cyclenumber,number");
	SetDB({
		code : "alt"
	}).remove();

	var cards_data = CardsData
			|| JSON.parse(localStorage.getItem('cards_data_' + Locale));
	CardDB = TAFFY(cards_data);
	CardDB({
		set_code : "alt"
	}).remove();

	for (var i = 0; i < Decklist.cards.length; i++) {
		var slot = Decklist.cards[i];
		CardDB({
			code : slot.card_code
		}).update({
			indeck : parseInt(slot.qty, 10)
		});
	}
	update_deck();
	NRDB.deck_browser.update();
}

function update_cardsearch_result() {
	$('#card_search_results').empty();
	var query = NRDB.smart_filter.get_query();
	if ($.isEmptyObject(query))
		return;
	CardDB(query).order("title intl").each(
			function(record) {
				$('#card_search_results').append(
						'<tr><td><span class="icon icon-' + record.faction_code
								+ ' ' + record.faction_code
								+ '"></td><td><a href="'
								+ Url_CardPage.replace('00000', record.code)
								+ '" class="card" data-index="' + record.code
								+ '">' + record.title
								+ '</a></td><td class="small">'
								+ record.setname + '</td></tr>');
			});
}

function handle_input_change(event) {
	NRDB.smart_filter.handler($(this).val(), update_cardsearch_result);
}

$(function() {
	when_all_parsed();
	$.when(promise1, promise2).done(when_all_parsed);

	$('#version-popover').popover({
		html : true
	});

	$('#card_search_form').on({
		keyup : debounce(handle_input_change, 500)
	});

});
