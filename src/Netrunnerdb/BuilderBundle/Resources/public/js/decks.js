
function when_all_parsed() {
	console.log('when_all_parsed: IsModified '+IsModified);
	if(CardDB && IsModified === false) return;
	var sets_data = SetsData || JSON.parse(localStorage.getItem('sets_data_'+Locale));
	if(!sets_data) {
		console.log('no data');
		return;
	}
	SetDB = TAFFY(sets_data);
	SetDB({name:"Promos"}).remove();

	var cards_data = CardsData || JSON.parse(localStorage.getItem('cards_data_'+Locale));
	CardDB = TAFFY(cards_data);
	CardDB({setname:"Promos"}).remove();
	console.log('when_all_parsed: '+CardDB().count()+' cards in database');
}

$(function() {
	$('#decks').on({
		click: display_deck
	}, 'tr');
	$('#btn-group-deck').on({
		click: do_action_deck
	}, 'button[id],a[id]');
	$('#decks-filter').on({
		change: filter_decks
	}, 'input[type=checkbox]');
	when_all_parsed();
	$.when(promise1, promise2).done(when_all_parsed);
	
});

function filter_decks(event) {
	var display = {};
	$('#decks-filter input[type=checkbox').each(function (n, elt) {
		display[$(elt).val()] = $(elt).prop("checked");
	})
	$('#decks tr').each(function (n, row) {
		$(row)[display[$(row).data('side')] ? "show" : "hide"]();
	});
}

function do_action_deck(event) {
	var action_id = $(this).attr('id');
	if(!action_id || !SelectedDeck) return;
	switch(action_id) {
		case 'btn-edit': location.href=Url_Edit.replace('xxx', SelectedDeck.id); break;
		case 'btn-publish': confirm_publish(); break;
		case 'btn-delete': confirm_delete(); break;
		//case 'btn-mail': confirm_mail(); break;
		case 'btn-download-text': location.href='/deck/export/text/'+SelectedDeck.id; break;
		case 'btn-download-octgn': location.href='/deck/export/octgn/'+SelectedDeck.id; break;
		case 'btn-export-bbcode': export_bbcode(); break;
		case 'btn-export-markdown': export_markdown(); break;
		case 'btn-export-plaintext': export_plaintext(); break;
	}
}

function confirm_publish() {
	$('#publish-form-alert').remove();
	$('#btn-publish-submit').prop('disabled', true);
	$.ajax(Url_CanPublish.replace('xxx', SelectedDeck.id), {
	  success: function() {
	    $('#btn-publish-submit').prop('disabled', false);
	  },
	  error: function( jqXHR, textStatus, errorThrown ) {
	    $('#publish-deck-form').prepend('<div id="publish-form-alert" class="alert alert-danger">'+jqXHR.responseText+'</div>');
	  }
	});
	$('#publish-deck-name').val(SelectedDeck.name);
	$('#publish-deck-id').val(SelectedDeck.id);
	$('#publishModal').modal('show');
}

function confirm_delete() {
	$('#delete-deck-name').text(SelectedDeck.name);
	$('#delete-deck-id').val(SelectedDeck.id);
	$('#deleteModal').modal('show');
}

function display_deck(event) {
	$('#no-deck-selected').hide();
	CardDB().update({indeck:0});
	var deck = DeckDB({id:$(this).data('id').toString()}).first();
	SelectedDeck = deck;
	$(this).closest('tr').siblings().removeClass('active');
	$(this).closest('tr').addClass('active');
	for(var i=0; i<deck.cards.length; i++) {
		var slot = deck.cards[i];
		CardDB({indexkey:slot.card_code}).update({indeck:parseInt(slot.qty,10)});
	}
	$('#deck-name').text(deck.name);
	$('#deck-description').html(deck.description && deck.description.replace(/\n/g, "<br>"));
	update_deck();
	$('#btn-publish').prop('disabled', !!$(this).closest('tr').data('problem'));
}
