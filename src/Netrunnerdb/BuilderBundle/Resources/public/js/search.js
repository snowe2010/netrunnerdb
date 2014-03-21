function when_all_parsed() {
	if (CardDB && IsModified === false)
		return;

	var sets_data = SetsData
			|| JSON.parse(localStorage.getItem('sets_data_' + Locale));
	if (!sets_data)
		return;
	SetDB = TAFFY(sets_data);
	SetDB.sort("cyclenumber,number");

	var cards_data = CardsData
			|| JSON.parse(localStorage.getItem('cards_data_' + Locale));
	CardDB = TAFFY(cards_data);

	$('#card').typeahead({
		name : 'cardnames',
		local : CardDB().select('title')
	}).on('typeahead:selected typeahead:autocompleted', function(event, data) {
		var card = CardDB({
			title : data.value
		}).first();
		var line = $('<p class="background-'+card.faction_code+'" style="padding: 3px 5px;border-radius: 3px;border: 1px solid silver"><button type="button" class="close" aria-hidden="true">&times;</button><input type="hidden" name="cards[]" value="'+card.code+'">'+
				  card.title + '</p>');
		line.on({
			click: function(event) { line.remove() }
		});
		line.insertBefore($('#card'));
		$(event.target).typeahead('setQuery', '');
	});
}
$(function() {
	when_all_parsed();
	$.when(promise1, promise2).done(when_all_parsed);
});