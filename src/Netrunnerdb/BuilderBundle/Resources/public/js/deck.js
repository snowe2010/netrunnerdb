var InputByTitle = false;

function when_all_parsed() {
	if(CardDB && IsModified === false) return;
	var sets_data = SetsData || JSON.parse(localStorage.getItem('sets_data_'+Locale));
	if(!sets_data) return;
	SetDB = TAFFY(sets_data);
	SetDB({code:"alt"}).remove();

	var cards_data = CardsData || JSON.parse(localStorage.getItem('cards_data_'+Locale));
	CardDB = TAFFY(cards_data);
	CardDB({setcode:"alt"}).remove();
	CardDB({side_en:{"!is":Side}}).remove();
	var sets_in_deck = {};
	CardDB().each(function (record) {
		var max_qty = 3, indeck = 0;
		if(record.type_en == "Identity" || record.indexkey == "03004") max_qty = 1;
		if(Deck[record.indexkey]) {
			indeck = parseInt(Deck[record.indexkey], 10);
			sets_in_deck[record.setcode] = 1;
		}
		CardDB(record.___id).update({indeck:indeck, maxqty:max_qty, factioncost:record.factioncost || 0});
	});
	update_deck();
	
	$('#faction').empty();
	$.each(CardDB().distinct("faction"), function (index, record) {
		$('#faction').append('<li><a href="#"><label><input type="checkbox" name="'+record+'" checked="checked">'+record+'</label></a></li>');
	});

	$('#type').empty();
	$.each(CardDB().distinct("type").sort(), function (index, record) {
		$('#type').append('<li><a href="#"><label><input type="checkbox" name="'+record+'" checked="checked">'+record+'</label></a></li>');
	});

	$('#setcode').empty();
	SetDB({available:{"!is":""}}).each(function (record) {
		$('#setcode').append('<li><a href="#"><label><input type="checkbox" name="'+record.code+'" checked="checked">'+record.name+'</label></a></li>');
	});
	SetDB({available:{"is":""}}).each(function (record) {
		$('#setcode').append('<li><a href="#"><label><input type="checkbox" name="'+record.code+'"'+(sets_in_deck[record.code] == null ? '' : ' checked="checked"')+'>'+record.name+'</label></a></li>');
	});
	
	$('input[name=Identity]').prop("checked", false);
	if(Identity.indexkey == "03002") $('input[name=Jinteki]').prop("checked", false);
	
	$('.filter').each(function (index, div) {
		var columnName = $(div).attr('id');
		var arr = [];
		$(div).find("input[type=checkbox]").each(function (index, elt) {
			if($(elt).prop('checked')) arr.push($(elt).attr('name'));
		});
		Filters[columnName] = arr;
	});
	FilterQuery = {};
	$.each(Filters, function(k) {
		if(Filters[k] != '') {
			FilterQuery[k] = Filters[k];
		}
	});
	updated_filtered();	
	
	$('input[name=title]').typeahead({
		name: 'cardnames',
		local: CardDB().select('title')
	}).on('typeahead:selected typeahead:autocompleted', function (event, data) {
		var card = CardDB({title:data.value}).first();
		fill_modal(card.indexkey);
		$('#cardModal').modal('show');
		InputByTitle = true;
	});
	$('html,body').css('height', 'auto');
	$('#spinner').remove();
	Spinners.removeDetached();
	$('.container').show();
}
$(function() {
	$('html,body').css('height', '100%');
	Spinners.create('#spinner', {
	  radius: 30,
	  height: 10,
	  width: 2.5,
	  dashes: 30,
	  opacity: 1,
	  padding: 3,
	  rotation: 700,
	  color: '#000000'
	}).play().center();
		
	
	$('#search').on({
		change: handle_input_change,
		click: function(event) {
			if(event.shiftKey && $(this).attr('type') == "checkbox") {
				if(!event.altKey) {
					$(this).closest(".filter").find("input[type=checkbox]").prop("checked", false);
					$(this).prop("checked", true).trigger('change');
				} else {
					$(this).closest(".filter").find("input[type=checkbox]").prop("checked", true);
					$(this).prop("checked", false).trigger('change');
				}
			}
			event.stopPropagation();
		}
	}, 'input[type=checkbox],label');
	
	$('#save_form').submit(handle_submit);
	
	
	$('#btn-save-as-copy').on('click', function (event) {
	  $('#deck-save-as-copy').val(1);
	});
	$('#collection').on({
		change: function (event) {
			InputByTitle = false;
			handle_quantity_change.call(this, event);
		}
	}, 'input[type=radio]');
	$('.modal').on({
		change: handle_quantity_change
	}, 'input[type=radio]');
	$('input[name=show-disabled]').on({
		change: function (event) { $('#collection')[$(this).prop('checked') ? 'removeClass' : 'addClass']('hide-disabled'); }
	});
	$('input[name=only-deck]').on({
		change: function (event) { $('#collection')[$(this).prop('checked') ? 'addClass' : 'removeClass']('only-deck'); }
	});	
	$('thead').on({
		click: handle_header_click
	}, 'a[data-sort]');
	$('#cardModal').on({
		keypress: function (event) {
			var num = parseInt(event.which, 10) - 48;
			$('.modal input[type=radio][value='+num+']').trigger('change');
		}
	});
	
	when_all_parsed();
	$.when(promise1, promise2).done(when_all_parsed);
});
function handle_header_click(event) {
	var new_sort = $(this).data('sort');
	if(Sort == new_sort) {
		Order *= -1;
	} else {
		Sort = new_sort;
		Order = 1;
	}
	$(this).closest('tr').find('th').removeClass('dropup').find('span.caret').remove();
	$(this).after('<span class="caret"></span>').closest('th').addClass(Order > 0 ? '': 'dropup');
	updated_filtered();
}
function handle_input_change(event) {
	var div = $(this).closest('.filter');
	var columnName = div.attr('id');
	var arr = [];
	div.find("input[type=checkbox]").each(function (index, elt) {
		if($(elt).prop('checked')) arr.push($(elt).attr('name'));
	});
	Filters[columnName] = arr;
	
	FilterQuery = {};
	$.each(Filters, function(k) {
		if(Filters[k] != '') {
			FilterQuery[k] = Filters[k];
		}
	});
	updated_filtered();	
}

function handle_submit(event) {
	var deck_name = $('input[name=name]').val();
	var deck_content = {};
	CardDB({indeck:{'gt':0}}).each(function (record) { deck_content[record.indexkey] = record.indeck; });
	var deck_json = JSON.stringify(deck_content);
	$('input[name=content]').val(deck_json);
}

function handle_quantity_change(event) {
	var index = $(this).closest('tr').data('index') || $(this).closest('div.modal').data('index');
	var quantity = parseInt($(this).val(), 10);
	$(this).closest('tr')[quantity ? "addClass" : "removeClass"]('in-deck');
	var card = CardDB({indexkey:index}).first();
	CardDB({indexkey:index}).update({indeck:quantity});
	if(card.type_en == "Identity") {
		if(Identity.faction != card.faction) {
			CardDB({indeck:{'gt':0},type_en:'Agenda'}).update({indeck:0});
		}
		CardDB({indeck:{'gt':0},type_en:'Identity',indexkey:{'!==':index}}).update({indeck:0});
	}
	update_deck();
	if(card.type_en == "Identity") {
		$.each(CardDivs, function (index, row) {
			row.removeClass("disabled").find('label').removeClass("disabled").find('input[type=radio]').attr("disabled", false);
		});
		updated_filtered();
	} else {
		CardDivs[index].find('input[name="qty-'+index+'"]').each(function (i, element) {
			if($(element).val() == quantity) $(element).prop('checked', true).closest('label').addClass('active');
			else $(element).prop('checked', false).closest('label').removeClass('active');
		});
	}
	$('div.modal').modal('hide');
	if(InputByTitle) $('input[name=title]').typeahead('setQuery', '').focus().blur();
}

function build_div(record) {
	var faction_colors = {
		"anarch": "orangered",
		"criminal": "royalblue",
		"shaper": "limegreen",
		"haas-bioroid": "blueviolet",
		"jinteki": "crimson",
		"nbn": "darkorange",
		"weyland-consortium": "darkgreen",
		"neutral": "gray",
	};
	var faction = record.faction_en.toLowerCase().replace(' ','-');
	var influ = "";
	for(var i=0; i<record.factioncost; i++) influ+="&bull;";
	
	var radios = '';
	for(var i=0; i<=record.maxqty; i++) {
		radios += '<label class="btn btn-xs btn-default'+(i == record.indeck ? ' active' : '')+'"><input type="radio" name="qty-'+record.indexkey+'" value="'+i+'">'+i+'</label>';
	}
	var imgsrc = record.faction_en == "Neutral" ? "" : '<img src="/web/bundles/netrunnerdbcards/images/factions/16px/'+record.faction_en+'.png">';
	var div = $('<tr><td><div class="btn-group" data-toggle="buttons">'
		+radios
		+'</div></td><td><a class="card" href="#cardModal" data-toggle="modal">'
		+record.title
		+'</a></td><td class="influence-'+faction+'">'
		+influ
		+'</td><td class="type" title="'+record.type+'"><img src="/web/bundles/netrunnerdbbuilder/images/types-gray-light/'+record.type_en+'.png">'
		+'</td><td class="faction" title="'+record.faction+'">'
		+imgsrc
		+'</td></tr>');
		
	return div;
}

function updated_filtered(){
	$('#collection').empty();
	CardDB(FilterQuery).order(Sort+(Order>0 ? " asec" : " desc")+',title').each(function (record) {
		var index = record.indexkey;
		var row = (CardDivs[index] || (CardDivs[index] = build_div(record))).data("index", record.indexkey);
		row[record.indeck ? "addClass" : "removeClass"]('in-deck');
		row.find('input[name="qty-'+record.indexkey+'"]').each(function (i, element) {
			if($(element).val() == record.indeck) $(element).prop('checked', true).closest('label').addClass('active');
			else $(element).prop('checked', false).closest('label').removeClass('active');
		});

		if(Identity.indexkey == "03002" && record.faction_en == "Jinteki") {
			row.addClass("disabled").find('label').addClass("disabled").find('input[type=radio]').attr("disabled", true);
		}
		if(record.type_en == "Agenda" && record.faction_en != "Neutral" && record.faction != Identity.faction) {
			row.addClass("disabled").find('label').addClass("disabled").find('input[type=radio]').attr("disabled", true);
		}
		$('#collection').append(row);
	});
}
