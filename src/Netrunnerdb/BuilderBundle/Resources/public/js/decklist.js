function when_all_parsed() {
	console.log('when_all_parsed: IsModified '+IsModified);
	if(CardDB && IsModified === false) return;
	var sets_data = SetsData || JSON.parse(localStorage.getItem('sets_data_'+Locale));
	if(!sets_data) {
		console.log('no data');
		return;
	}
	SetDB = TAFFY(sets_data);
	SetDB({code:"alt"}).remove();

	var cards_data = CardsData || JSON.parse(localStorage.getItem('cards_data_'+Locale));
	CardDB = TAFFY(cards_data);
	CardDB({setcode:"alt"}).remove();
	console.log('when_all_parsed: '+CardDB().count()+' cards in database');
	
	$(this).closest('tr').siblings().removeClass('active');
	$(this).closest('tr').addClass('active');
	for(var i=0; i<Decklist.cards.length; i++) {
		var slot = Decklist.cards[i];
		CardDB({indexkey:slot.card_code}).update({indeck:parseInt(slot.qty,10)});
	}
	update_deck();
}

$(function() {
	when_all_parsed();
	$.when(promise1, promise2).done(when_all_parsed);
	$('#decklist-social-icons>a').tooltip();
	$('#decklist-edit').on('click', edit_form);
	$('#decklist-social-icon-like').on('click', send_like);
	$('#decklist-social-icon-favorite').on('click', send_favorite);
	$('#decklist-social-icon-comment').on('click', function () { $('#comment-form-text').trigger('focus'); });
	$('#markdown-help').popover({
		html: true,
		content: "[a link](http://www.example.net)<br><a href=\"http://www.example.net\">a link</a><br><br>> This is quoted<br><blockquote>This is quoted</blockquote>* This is<br>* a list<ul><li>This is</li><li>a list</li></ul>1. with<br>1. numbers<ol><li>with</li><li>number</li></ol>_italic_ or *italic*<br>__bold__ or **bold**",
		placement: "left",
		container: 'body'
	});
	$('#btn-group-decklist').on({
		click: do_action_decklist
	}, 'button[id],a[id]');
});

function edit_form() {
	$('#editModal').modal('show');
}

function do_action_decklist(event) {
	var action_id = $(this).attr('id');
	if(!action_id || !SelectedDeck) return;
	switch(action_id) {
		case 'btn-copy': location.href=Url_Copy; break;
		case 'btn-download-text': location.href='/decklist/export/text/'+SelectedDeck.id; break;
		case 'btn-download-octgn': location.href='/decklist/export/octgn/'+SelectedDeck.id; break;
		case 'btn-export-bbcode': export_bbcode(); break;
		case 'btn-export-markdown': export_markdown(); break;
		case 'btn-export-plaintext': export_plaintext(); break;
	}
}

function send_like() {
	var obj = $(this);
	$.post(Url_Like, { id: Decklist.id }, function (data, textStatus, jqXHR) {
		obj.find('.num').text(data);
	});
}

function send_favorite() {
	var obj = $(this);
	$.post(Url_Favorite, { id: Decklist.id }, function (data, textStatus, jqXHR) {
		obj.find('.num').text(data);
		var title = obj.data('original-tooltip');
		obj.tooltip('destroy');
		obj.data('original-tooltip', title == "Add to favorites" ? "Remove from favorites" : "Add to favorites");
		obj.attr('title', obj.data('original-tooltip'));
		obj.tooltip('show');
	});
}
