NRDB.data_loaded.add(function() {
	NRDB.data.sets({
		code : "alt"
	}).remove();

	NRDB.data.cards({
		set_code : "alt"
	}).remove();
	
	$('#card').typeahead({
		name : 'cardnames',
		local : NRDB.data.cards().select('title')
	});
});

$(function() {
	$('#card').on('typeahead:selected typeahead:autocompleted', function(event, data) {
		var card = NRDB.data.cards({
			title : data.value
		}).first();
		var line = $('<p class="background-'+card.faction_code+'-20" style="padding: 3px 5px;border-radius: 3px;border: 1px solid silver"><button type="button" class="close" aria-hidden="true">&times;</button><input type="hidden" name="cards[]" value="'+card.code+'">'+
				  card.title + '</p>');
		line.on({
			click: function(event) { line.remove() }
		});
		line.insertBefore($('#card'));
		$(event.target).typeahead('setQuery', '');
	});
})
