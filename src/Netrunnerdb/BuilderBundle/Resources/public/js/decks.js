NRDB.data_loaded.add(function() {
	NRDB.data.sets({code:"alt"}).remove();
	NRDB.data.cards({set_code:"alt"}).remove();
});

$(function() {
	$('#decks').on({
		click: display_deck
	}, 'tr');
	$('#btn-group-deck').on({
		click: do_action_deck
	}, 'button[id],a[id]');
	$('#menu-sort').on({
		change: function(event) {
			if($(this).attr('id').match(/btn-sort-(\w+)/)) {
				DisplaySort = RegExp.$1;
				update_deck();
			}
		}
	}, 'a');
	
	//
	// tags is an object where key is tag and value is array of deck ids
	var tag_dict = Decks.reduce(function (p, c) {
		c.tags.forEach(function (t) {
			if(!p[t]) p[t] = [];
			p[t].push(c.id);
		});
		return p;
	}, {});
	var tags = [];
	for(var tag in tag_dict) {
		tags.push(tag);
	}
	tags.sort().forEach(function (tag) {
		$('#tag_toggles').append('<a href="#" class="label label-default tag-'+tag+'">'+tag+'</a>');
	});
	
});

function filter_decks(event) {
	var display = {};
	$('#decks tr').each(function (n, row) {
		$(row)[display[$(row).data('faction')] ? "show" : "hide"]();
	});
}

function do_action_deck(event) {
	var action_id = $(this).attr('id');
	if(!action_id || !SelectedDeck) return;
	switch(action_id) {
		case 'btn-view': location.href=Routing.generate('deck_view', {deck_id:SelectedDeck.id}); break;
		case 'btn-edit': location.href=Routing.generate('deck_edit', {deck_id:SelectedDeck.id}); break;
		case 'btn-publish': confirm_publish(); break;
		case 'btn-delete': confirm_delete(); break;
		//case 'btn-mail': confirm_mail(); break;
		case 'btn-download-text': location.href=Routing.generate('deck_export_text', {deck_id:SelectedDeck.id}); break;
		case 'btn-download-octgn': location.href=Routing.generate('deck_export_octgn', {deck_id:SelectedDeck.id}); break;
		case 'btn-export-bbcode': export_bbcode(); break;
		case 'btn-export-markdown': export_markdown(); break;
		case 'btn-export-plaintext': export_plaintext(); break;
	}
}

function confirm_publish() {
	$('#publish-form-alert').remove();
	$('#btn-publish-submit').text("Checking...").prop('disabled', true);
	$.ajax(Routing.generate('deck_publish', {deck_id:SelectedDeck.id}), {
	  success: function( response ) {
		  if(response == "") {
			  $('#btn-publish-submit').text("Go").prop('disabled', false);
		  }
		  else 
		  {
			  $('#publish-deck-form').prepend('<div id="publish-form-alert" class="alert alert-danger">That deck cannot be published because <a href="'+response+'">another decklist</a> already has the same composition.</div>');
			  $('#btn-publish-submit').text("Refused");
		  }
	  },
	  error: function( jqXHR, textStatus, errorThrown ) {
	    $('#publish-deck-form').prepend('<div id="publish-form-alert" class="alert alert-danger">'+jqXHR.responseText+'</div>');
	  }
	});
	$('#publish-deck-name').val(SelectedDeck.name);
	$('#publish-deck-id').val(SelectedDeck.id);
	$('#publish-deck-description').val(SelectedDeck.description);
	$('#publishModal').modal('show');
}

function confirm_delete() {
	$('#delete-deck-name').text(SelectedDeck.name);
	$('#delete-deck-id').val(SelectedDeck.id);
	$('#deleteModal').modal('show');
}

function display_deck(event) {
	NRDB.draw_simulator.reset();
	$('#no-deck-selected').hide();
	NRDB.data.cards().update({indeck:0});
	var deck = DeckDB({id:$(this).data('id').toString()}).first();
	SelectedDeck = deck;
	$(this).closest('tr').siblings().removeClass('active');
	$(this).closest('tr').addClass('active');
	for(var i=0; i<deck.cards.length; i++) {
		var slot = deck.cards[i];
		NRDB.data.cards({code:slot.card_code}).update({indeck:parseInt(slot.qty,10)});
	}
	$('#deck-name').text(deck.name);
	

	var converter = new Markdown.Converter();
	$('#deck-description').html(converter.makeHtml(deck.description));
	update_deck();
	$('#btn-publish').prop('disabled', !!$(this).closest('tr').data('problem'));
}
