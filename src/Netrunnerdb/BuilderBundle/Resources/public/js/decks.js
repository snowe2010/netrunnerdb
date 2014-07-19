NRDB.data_loaded.add(function() {
	NRDB.data.sets({code:"alt"}).remove();
	NRDB.data.cards({set_code:"alt"}).remove();
});

$(function() {
	$('#btn-group-deck').on('click', 'button[id],a[id]', do_action_deck);
	$('#btn-group-selection').on('click', 'button[id],a[id]', do_action_selection);
	$('#btn-group-sort').on('click', 'button[id],a[id]', do_action_sort);
	
	$('#menu-sort').on({
		change: function(event) {
			if($(this).attr('id').match(/btn-sort-(\w+)/)) {
				DisplaySort = RegExp.$1;
				update_deck();
			}
		}
	}, 'a');
	
	$('#tag_toggles').on('click', 'button', function (event) {
		var button = $(this);
		if(!event.shiftKey) {
			$('#tag_toggles button').each(function (index, elt) {
				if($(elt).text() != button.text()) $(elt).removeClass('active');
			});
		}
		setTimeout(filter_decks, 0);
	});
	update_tag_toggles();
	
	$('#decks').on('click', 'a.deck-list-group-item', function (event) {
		if(!event.shiftKey) {
			$('#decks a.deck-list-group-item.selected').removeClass('selected');
			$(this).addClass('selected');
		} else {
			$(this).toggleClass('selected');
		}
		var deck_id = $(this).data('id').toString();
		display_deck(deck_id, !event.shiftKey);
		return false;
	});
	$('#decks').on('dblclick', 'a.deck-list-group-item', function (event) {
		
	});
});

function filter_decks() {
	var buttons = $('#tag_toggles button.active');
	var list_id = [];
	buttons.each(function (index, button) {
		list_id = list_id.concat($(button).data('deck_id').split(/\s+/));
	});
	list_id = list_id.filter(function (itm,i,a) { return i==a.indexOf(itm); });
	$('#decks a.deck-list-group-item').each(function (index, elt) {
		$(elt).removeClass('selected');
		var id = $(elt).attr('id').replace('deck_', '');
		if(list_id.length && list_id.indexOf(id) === -1) $(elt).hide();
		else $(elt).show();
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

function do_action_selection(event) {
	var action_id = $(this).attr('id');
	var ids = [];
	$('#decks a.deck-list-group-item.selected').each(function (index, elt) { ids.push($(elt).data('id')) });
	if(!action_id || !ids.length) return;
	switch(action_id) {
		case 'btn-tag-add': tag_add(ids); break;
		case 'btn-tag-remove-one': tag_remove(ids); break;
		case 'btn-tag-remove-all': tag_clear(ids); break;
		case 'btn-delete-selected': confirm_delete_all(ids); break;
		case 'btn-download-text': download_text_selection(ids); break;
		case 'btn-download-octgn': download_octgn_selection(ids); break;
	}
}

function do_action_sort(event) {
	var action_id = $(this).attr('id');
	if(!action_id) return;
	switch(action_id) {
		case 'btn-sort-update': sort_list('lastupdate desc'); break;
		case 'btn-sort-creation': sort_list('creation desc'); break;
		case 'btn-sort-identity': sort_list('identity_title,name'); break;
		case 'btn-sort-faction': sort_list('faction_code,name'); break;
		case 'btn-sort-lastpack': sort_list('cycle_id desc,pack_number desc'); break;
		case 'btn-sort-name': sort_list('name'); break;
	}
}

function download_text_selection(ids) 
{
	window.location = Routing.generate('deck_export_text_list', { ids: ids });
}

function download_octgn_selection(ids)
{
	window.location = Routing.generate('deck_export_octgn_list', { ids: ids });
}

function sort_list(type)
{
	var sorted_list_id = DeckDB().order(type).select('id');
	var first_id = sorted_list_id.shift();
	var deck_elt = $('#deck_'+first_id);
	
	var container = $('#decks');
	container.prepend(deck_elt);
	sorted_list_id.forEach(function (id) {
		deck_elt = $('#deck_'+id).insertAfter(deck_elt);
	})
	
}


function update_tag_toggles()
{

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
	var container = $('#tag_toggles').empty();
	tags.sort().forEach(function (tag) {
		$('<button type="button" class="btn btn-default btn-xs" data-toggle="button">'+tag+'</button>').data('deck_id', tag_dict[tag].join(' ')).appendTo(container);
	});
	
}

function set_tags(id, tags)
{
	var elt = $('#deck_'+id);
	var div = elt.find('.deck-list-tags').empty();
	tags.forEach(function (tag) {
		div.append($('<span class="label label-default tag-'+tag+'">'+tag+'</span>'));
	})
	
	for(var i=0; i<Decks.length; i++) {
		if(Decks[i].id == id) {
			Decks[i].tags = tags;
			break;
		}
	}
	
	update_tag_toggles();
}

function tag_add(ids) {
    $('#tag_add_ids').val(ids);
	$('#tagAddModal').modal('show');
    setTimeout(function() { $('#tag_add_tags').focus() }, 500);
}
function tag_add_process(event) {
    event.preventDefault();
    var ids = $('#tag_add_ids').val().split(/,/);
    var tags = $('#tag_add_tags').val().split(/\s+/);
    if(!ids.length || !tags.length) return;
	$.ajax(Routing.generate('tag_add'), {
		type: 'POST',
		data: { ids: ids, tags: tags },
		dataType: 'json',
		success: function(data, textStatus, jqXHR) {
			var response = jqXHR.responseJSON;
			if(!response.success) {
				alert('An error occured while updating the tags.');
				return;
			}
			$.each(response.tags, function (id, tags) {
				set_tags(id, tags);
			});
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert('An error occured while updating the tags.');
		}
	});
}

function tag_remove(ids) {
    $('#tag_remove_ids').val(ids);
	$('#tagRemoveModal').modal('show');
    setTimeout(function() { $('#tag_remove_tags').focus() }, 500);
}
function tag_remove_process(event) {
    event.preventDefault();
    var ids = $('#tag_remove_ids').val().split(/,/);
    var tags = $('#tag_remove_tags').val().split(/\s+/);
    if(!ids.length || !tags.length) return;
	$.ajax(Routing.generate('tag_remove'), {
		type: 'POST',
		data: { ids: ids, tags: tags },
		dataType: 'json',
		success: function(data, textStatus, jqXHR) {
			var response = jqXHR.responseJSON;
			if(!response.success) {
				alert('An error occured while updating the tags.');
				return;
			}
			$.each(response.tags, function (id, tags) {
				set_tags(id, tags);
			});
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert('An error occured while updating the tags.');
		}
	});
}

function tag_clear(ids) {
    $('#tag_clear_ids').val(ids);
	$('#tagClearModal').modal('show');
}
function tag_clear_process(event) {
    event.preventDefault();
    var ids = $('#tag_clear_ids').val().split(/,/);
    if(!ids.length) return;
	$.ajax(Routing.generate('tag_clear'), {
		type: 'POST',
		data: { ids: ids },
		dataType: 'json',
		success: function(data, textStatus, jqXHR) {
			var response = jqXHR.responseJSON;
			if(!response.success) {
				alert('An error occured while updating the tags.');
				return;
			}
			$.each(response.tags, function (id, tags) {
				set_tags(id, tags);
			});
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert('An error occured while updating the tags.');
		}
	});
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

function confirm_delete_all(ids) {
	$('#delete-deck-list-id').val(ids.join('-'));
	$('#deleteListModal').modal('show');
}

function display_deck(deck_id, backTop) {
	NRDB.draw_simulator.reset();
	$('#no-deck-selected').hide();
	NRDB.data.cards().update({indeck:0});
	var deck = DeckDB({id:deck_id}).first();
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
	if(backTop) location.href = "#top";
}
