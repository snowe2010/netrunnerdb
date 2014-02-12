function when_all_parsed() {
	if(CardDB && IsModified === false) return;
	var sets_data = SetsData || JSON.parse(localStorage.getItem('sets_data_'+Locale));
	if(!sets_data) {
		return;
	}
	SetDB = TAFFY(sets_data);
	SetDB.sort("cyclenumber,number");
	SetDB({code:"alt"}).remove();

	var cards_data = CardsData || JSON.parse(localStorage.getItem('cards_data_'+Locale));
	CardDB = TAFFY(cards_data);
	CardDB({set_code:"alt"}).remove();
	
	$(this).closest('tr').siblings().removeClass('active');
	$(this).closest('tr').addClass('active');
	for(var i=0; i<Decklist.cards.length; i++) {
		var slot = Decklist.cards[i];
		CardDB({code:slot.card_code}).update({indeck:parseInt(slot.qty,10)});
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

	var converter = new Markdown.Converter();
	$('#comment-form-text').on('keyup', function () {
		$('#comment-form-preview').html(converter.makeHtml($('#comment-form-text').val()));
	});
	$('#btn-group-decklist').on({
		click: do_action_decklist
	}, 'button[id],a[id]');
	$('#menu-sort').on({
		change: function(event) {
			if($(this).attr('id').match(/btn-sort-(\w+)/)) {
				DisplaySort = RegExp.$1;
				update_deck();
			}
		}
	}, 'a');
	make_graphs();
});

function edit_form() {
	$('#editModal').modal('show');
}

function do_action_decklist(event) {
	var action_id = $(this).attr('id');
	if(!action_id || !SelectedDeck) return;
	switch(action_id) {
		case 'btn-copy': location.href=Url_Copy; break;
		case 'btn-download-text': location.href=Url_TextExport; break;
		case 'btn-download-octgn': location.href=Url_OctgnExport; break;
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
