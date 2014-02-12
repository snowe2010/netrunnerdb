var InputByTitle = false;
var DisplayColumns = 1;

function when_all_parsed() {
	if(CardDB && IsModified === false) return;
	var sets_data = SetsData || JSON.parse(localStorage.getItem('sets_data_'+Locale));
	if(!sets_data) return;
	SetDB = TAFFY(sets_data);
	SetDB.sort("cyclenumber,number");
	SetDB({code:"alt"}).remove();

	var cards_data = CardsData || JSON.parse(localStorage.getItem('cards_data_'+Locale));
	CardDB = TAFFY(cards_data);
	CardDB({set_code:"alt"}).remove();
	CardDB({side_code:{"!is":Side}}).remove();
	var sets_in_deck = {};
	CardDB().each(function (record) {
		var max_qty = 3, indeck = 0;
		if(record.type_code == "identity" || record.code == "03004") max_qty = 1;
		if(Deck[record.code]) {
			indeck = parseInt(Deck[record.code], 10);
			sets_in_deck[record.set_code] = 1;
		}
		CardDB(record.___id).update({indeck:indeck, maxqty:max_qty, factioncost:record.factioncost || 0});
	});
	update_deck();
	
	$('#faction_code').empty();
	$.each(CardDB().distinct("faction_code").sort(function (a, b) { return b === "neutral" ? -1 : a === "neutral" ? 1 : a < b ? -1 : a > b ? 1 : 0; }), function (index, record) {
		$('#faction_code').append('<label class="btn btn-default btn-sm" data-code="'+record+'"><input type="checkbox" name="'+record+'"><img src="'+Url_FactionImage.replace('xxx', record)+'"></label>')
	});
	$('#faction_code').button();
	$('#faction_code').children('label').each(function (index, elt) {
		$(elt).button('toggle');
	});

	$('#type_code').empty();
	$.each(CardDB().distinct("type_code").sort(), function (index, record) {
		$('#type_code').append('<label title="'+record+'" class="btn btn-default btn-sm" data-code="'+record+'"><input type="checkbox" name="'+record+'"><img src="'+Url_TypeImage.replace('xxx', record)+'"></label>')
	});
	$('#type_code').button();
	$('#type_code').children('label').each(function (index, elt) {
		if($(elt).data('code') !== "identity") $(elt).button('toggle');
	});

	$('#set_code').empty();
	SetDB().each(function (record) {
		var checked = record.available === "" && sets_in_deck[record.code] == null ? '' : ' checked="checked"';
		$('#set_code').append('<li><a href="#"><label><input type="checkbox" name="'+record.code+'"'+checked+'>'+record.name+'</label></a></li>');
	});
	
	$('input[name=Identity]').prop("checked", false);
	if(Identity.code == "03002") $('input[name=Jinteki]').prop("checked", false);
	
	$('.filter').each(function (index, div) {
		var columnName = $(div).attr('id');
		var arr = [], checked;
		$(div).find("input[type=checkbox]").each(function (index, elt) {
			if(columnName == "set_code" && localStorage && ( checked = localStorage.getItem( 'set_code_'+$(elt).attr('name') ) ) !== null) $(elt).prop('checked', checked === "on");
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
	
	var localStorageDisplayColumns;
	if(localStorage && ( localStorageDisplayColumns = parseInt(localStorage.getItem( 'display_columns' ), 10) ) !== null && [1,2,3].indexOf(localStorageDisplayColumns) > -1) {
		DisplayColumns = localStorageDisplayColumns;
	}
	$('input[name=display-column-'+DisplayColumns+']').prop('checked', true);
	
	if(!Update_Incoming) {
		Update_Incoming = true;
		setTimeout(update_filtered, 100);
	}
	
	$('input[name=title]').typeahead({
		name: 'cardnames',
		local: CardDB().select('title')
	}).on('typeahead:selected typeahead:autocompleted', function (event, data) {
		var card = CardDB({title:data.value}).first();
		fill_modal(card.code);
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
		
	
	$('#search,#search2').on({
		change: handle_input_change,
		click: function(event) {
			var dropdown = $(this).closest('ul').hasClass('dropdown-menu');
			if(event.shiftKey) {
				if(!event.altKey) {
					if(dropdown) {
						$(this).closest(".filter").find("input[type=checkbox]").prop("checked", false);
						$(this).children('input[type=checkbox]').prop("checked", true).trigger('change');
					} else {
						$(this).closest(".filter").find("label").button('off', false);
						$(this).button('on', true);
					}
				} else {
					if(dropdown) {
						$(this).closest(".filter").find("input[type=checkbox]").prop("checked", true);
						$(this).children('input[type=checkbox]').prop("checked", false);
					} else {
						$(this).closest(".filter").find("label").button('on', false);
						$(this).button('off', true);
					}
				}
			} else if(!dropdown) {
				$(this).button('toggle', true);
			}
			if(dropdown) event.stopPropagation();
		}
	}, 'label');

	$('#filter-text').on({
		change: handle_smartfilter_change
	});
	
	$('#save_form').submit(handle_submit);
	
	
	$('#btn-save-as-copy').on('click', function (event) {
	  $('#deck-save-as-copy').val(1);
	});
	$('#collection,#collection2').on({
		change: function (event) {
			InputByTitle = false;
			handle_quantity_change.call(this, event);
		}
	}, 'input[type=radio]');
	$('.modal').on({
		change: handle_quantity_change
	}, 'input[type=radio]');
	$('input[name=show-disabled]').on({
		change: function (event) { $('#collection,#collection2')[$(this).prop('checked') ? 'removeClass' : 'addClass']('hide-disabled'); }
	});
	$('input[name=only-deck]').on({
		change: function (event) { $('#collection,#collection2')[$(this).prop('checked') ? 'addClass' : 'removeClass']('only-deck'); }
	});	
	$('input[name=display-column-1]').on({
		change: function (event) { 
			$('input[name=display-column-2]').prop('checked', false);
			$('input[name=display-column-3]').prop('checked', false);
			DisplayColumns = 1;
			if(localStorage) localStorage.setItem( 'display_columns', DisplayColumns );
			if(!Update_Incoming) {
				Update_Incoming = true;
				setTimeout(update_filtered, 100);
			}
		}
	});	
	$('input[name=display-column-2]').on({
		change: function (event) { 
			$('input[name=display-column-1]').prop('checked', false);
			$('input[name=display-column-3]').prop('checked', false);
			DisplayColumns = 2;
			if(localStorage) localStorage.setItem( 'display_columns', DisplayColumns );
			if(!Update_Incoming) {
				Update_Incoming = true;
				setTimeout(update_filtered, 100);
			}
		}
	});	
	$('input[name=display-column-3]').on({
		change: function (event) { 
			$('input[name=display-column-1]').prop('checked', false);
			$('input[name=display-column-2]').prop('checked', false);
			DisplayColumns = 3;
			if(localStorage) localStorage.setItem( 'display_columns', DisplayColumns );
			if(!Update_Incoming) {
				Update_Incoming = true;
				setTimeout(update_filtered, 100);
			}
		}
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
	$('#filter-text-button').tooltip({
		html: true,
		container: 'body',
		placement: 'bottom',
		trigger: 'click',
		title:"<h5>Smart filter syntax</h5><ul style=\"text-align:left\"><li>by default, filters on title</li><li>x &ndash; filters on text</li><li>a &ndash; flavor text</li><li>s &ndash; subtype</li><li>o &ndash; cost</li><li>v &ndash; agenda points</li><li>n &ndash; faction cost</li><li>p &ndash; strength</li><li>g &ndash; advancement cost</li><li>h &ndash; trash cost</li><li>y &ndash; quantity in pack</li></ul><code>s:\"code gate\" x:trace</code> to find code gates with trace"
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
	if(!Update_Incoming) {
		Update_Incoming = true;
		setTimeout(update_filtered, 100);
	}
}
function handle_input_change(event) {
	var div = $(this).closest('.filter');
	var columnName = div.attr('id');
	var arr = [];
	div.find("input[type=checkbox]").each(function (index, elt) {
		if($(elt).prop('checked')) arr.push($(elt).attr('name'));
		if(columnName == "set_code" && localStorage)  localStorage.setItem('set_code_'+$(elt).attr('name'), $(elt).prop('checked') ? "on" : "off");
	});
	Filters[columnName] = arr;
	
	FilterQuery = {};
	$.each(Filters, function(k) {
		if(Filters[k] != '') {
			FilterQuery[k] = Filters[k];
		}
	});
	// 3 change events are fired at the same time, throttling repaint
	if(!Update_Incoming) {
		Update_Incoming = true;
		setTimeout(update_filtered, 100);
	}
}
function handle_smartfilter_change(event) {
	var conditions = filterSyntax($(this).val());
	SmartFilterQuery = {};
	AdditionalFilters = [];
	
	for(var i=0; i<conditions.length; i++) {
		var condition = conditions[i];
		var type = condition.shift();
		var operator = condition.shift();
		var values = condition;

		switch(type) {
		case "e":
		case "c":
		case "f":
		case "t": continue; break;
		case  "": add_string_sf('title', operator, values); break;
		case "x": add_string_sf('text', operator, values); break;
		case "a": add_string_sf('flavor', operator, values); break;
		case "s": add_string_sf('subtype', operator, values); break;
		case "o": add_integer_sf('cost', operator, values); break;
		case "v": add_integer_sf('agendapoints', operator, values); break;
		case "n": add_integer_sf('factioncost', operator, values); break;
		case "p": add_integer_sf('strength', operator, values); break;
		case "g": add_integer_sf('advancementcost', operator, values); break;
		case "h": add_integer_sf('trash', operator, values); break;
		case "y": add_integer_sf('quantity', operator, values); break;
		}
	}

	if(!Update_Incoming) {
		Update_Incoming = true;
		setTimeout(update_filtered, 100);
	}
}
function add_integer_sf(key, operator, values) {
	for(var j=0; j<values.length; j++) values[j] = parseInt(values[j], 10);
	switch(operator) {
	case ":":
		SmartFilterQuery[key] = {'is':values}; break;
	case "<":
		SmartFilterQuery[key] = {'lt':values}; break;
	case ">":
		SmartFilterQuery[key] = {'gt':values}; break;
	case "!":
		SmartFilterQuery[key] = {'!is':values}; break;
	}
}
function add_string_sf(key, operator, values) {
	switch(operator) {
	case ":":
		SmartFilterQuery[key] = {'likenocase':values}; break;
	case "!":
		SmartFilterQuery[key] = {'!likenocase':values}; break;
	}
}
function filterSyntax(query) {
		// renvoie une liste de conditions (array)
	// chaque condition est un tableau à n>1 éléments
	// le premier est le type de condition (0 ou 1 caractère)
	// les suivants sont les arguments, en OR
	
	query = query.replace(/^\s*(.*?)\s*$/, "$1").replace('/\s+/', ' ');

	var list = [];
	var cond = null;
	// l'automate a 3 états : 
	// 1:recherche de type
	// 2:recherche d'argument principal
	// 3:recherche d'argument supplémentaire
	// 4:erreur de parsing, on recherche la prochaine condition
	// s'il tombe sur un argument alors qu'il est en recherche de type, alors le type est vide
	var etat = 1;
	while(query != "") {
		if(etat == 1) {
			if(cond !== null && etat !== 4 && cond.length > 2) {
				list.push(cond);
			}
			// on commence par rechercher un type de condition
			if(query.match(/^(\w)([:<>!])(.*)/)) { // jeton "condition:"
				cond = [ RegExp.$1.toLowerCase(), RegExp.$2 ];
				query = RegExp.$3;
			} else {
				cond = [ "", ":" ];
			}
			etat=2;
		} else {
			if( query.match(/^"([^"]*)"(.*)/) // jeton "texte libre entre guillements"
			 || query.match(/^([\w\-]+)(.*)/) // jeton "texte autorisé sans guillements"
			) {
				if((etat === 2 && cond.length === 2) || etat === 3) {
					cond.push(RegExp.$1);
					query = RegExp.$2;
					etat = 2;
				} else {
					// erreur
					query = RegExp.$2;
					etat = 4;
				}
			} else if( query.match(/^\|(.*)/) ) { // jeton "|"
				if((cond[1] === ':' || cond[1] === '!') && ((etat === 2 && cond.length > 2) || etat === 3)) {
					query = RegExp.$1;
					etat = 3;
				} else {
					// erreur
					query = RegExp.$1;
					etat = 4;
				}
			} else if( query.match(/^ (.*)/) ) { // jeton " "
				query = RegExp.$1;
				etat = 1;
			} else {
				// erreur
				query = query.substr(1);
				etat = 4;
			}
		}
	}
	if(cond !== null && etat !== 4 && cond.length > 2) {
		list.push(cond);
	}
	return list;
}

function handle_submit(event) {
	var deck_name = $('input[name=name]').val();
	var deck_content = {};
	CardDB({indeck:{'gt':0}}).each(function (record) { deck_content[record.code] = record.indeck; });
	var deck_json = JSON.stringify(deck_content);
	$('input[name=content]').val(deck_json);
}

function handle_quantity_change(event) {
	var index = $(this).closest('.card-container').data('index') || $(this).closest('div.modal').data('index');
	var quantity = parseInt($(this).val(), 10);
	$(this).closest('.card-container')[quantity ? "addClass" : "removeClass"]('in-deck');
	var card = CardDB({code:index}).first();
	CardDB({code:index}).update({indeck:quantity});
	if(card.type_code == "identity") {
		if(Identity.faction != card.faction) {
			CardDB({indeck:{'gt':0},type_code:'agenda'}).update({indeck:0});
		}
		CardDB({indeck:{'gt':0},type_code:'identity',code:{'!==':index}}).update({indeck:0});
	}
	update_deck();
	if(card.type_code == "identity") {
		$.each(CardDivs, function (nbcols, rows) {
			$.each(rows, function (index, row) {
				row.removeClass("disabled").find('label').removeClass("disabled").find('input[type=radio]').attr("disabled", false);
			});
		});
		if(!Update_Incoming) {
			Update_Incoming = true;
			setTimeout(update_filtered, 100);
		}
	} else {
		$.each(CardDivs, function (nbcols, rows) {
			rows[index].find('input[name="qty-'+index+'"]').each(function (i, element) {
				if($(element).val() == quantity) $(element).prop('checked', true).closest('label').addClass('active');
				else $(element).prop('checked', false).closest('label').removeClass('active');
			});
		});
	}
	$('div.modal').modal('hide');
	if(InputByTitle) $('input[name=title]').typeahead('setQuery', '').focus().blur();
}

function build_div(record) {
	var faction = record.faction_code;
	var influ = "";
	for(var i=0; i<record.factioncost; i++) influ+="&bull;";
	
	var radios = '';
	for(var i=0; i<=record.maxqty; i++) {
		radios += '<label class="btn btn-xs btn-default'+(i == record.indeck ? ' active' : '')+'"><input type="radio" name="qty-'+record.code+'" value="'+i+'">'+i+'</label>';
	}
	
	var div;
	switch(DisplayColumns) {
	case 1:
		
		var imgsrc = record.faction_code == "neutral" ? "" : '<img src="'+Url_FactionImage.replace('xxx', record.faction_code)+'.png">';
		div = $('<tr class="card-container"><td><div class="btn-group" data-toggle="buttons">'
			+radios
			+'</div></td><td><a class="card" href="#cardModal" data-toggle="modal">'
			+record.title
			+'</a></td><td class="influence-'+faction+'">'
			+influ
			+'</td><td class="type" title="'+record.type+'"><img src="/web/bundles/netrunnerdbbuilder/images/types/'+record.type_code+'.png">'
			+'</td><td class="faction" title="'+record.faction+'">'
			+imgsrc
			+'</td></tr>');
		break;

	case 2:

		div = $('<div class="col-sm-6 card-container">'
		           +'<div class="media">'
		           +'<a class="pull-left" href="#">'
		           +'    <img class="media-object" src="/web/bundles/netrunnerdbcards/images/cards/en/'+record.code+'.png">'
		           +'</a>'
		           +'<div class="media-body">'
		           +'    <h4 class="media-heading"><a class="card" href="#cardModal" data-toggle="modal">'+record.title+'</a></h4>'
		           +'    <div class="btn-group" data-toggle="buttons">'+radios+'</div>'
		           +'    <span class="influence-'+faction+'">'+influ+'</span>'
		           +'</div>'
		           +'</div>'
		           +'</div>');
		break;

	case 3:

		div = $('<div class="col-sm-4 card-container">'
		           +'<div class="media">'
		           +'<a class="pull-left" href="#">'
		           +'    <img class="media-object" src="/web/bundles/netrunnerdbcards/images/cards/en/'+record.code+'.png">'
		           +'</a>'
		           +'<div class="media-body">'
		           +'    <h5 class="media-heading"><a class="card" href="#cardModal" data-toggle="modal">'+record.title+'</a></h5>'
		           +'    <div class="btn-group" data-toggle="buttons">'+radios+'</div>'
		           +'    <span class="influence-'+faction+'">'+influ+'</span>'
		           +'</div>'
		           +'</div>'
		           +'</div>');
		break;
		
	}
	
	return div;
}

function update_filtered(){
	Update_Incoming = false;
	$('#collection-table').empty();
	$('#collection-grid').empty();
	$.extend(SmartFilterQuery, FilterQuery);
	var counter = 0, container = $('#collection-table');
	CardDB(SmartFilterQuery).order(Sort+(Order>0 ? " intl" : " intldesc")+',title').each(function (record) {
		var index = record.code;
		var row = (CardDivs[DisplayColumns][index] || (CardDivs[DisplayColumns][index] = build_div(record))).data("index", record.code);
		row[record.indeck ? "addClass" : "removeClass"]('in-deck');
		row.find('input[name="qty-'+record.code+'"]').each(function (i, element) {
			if($(element).val() == record.indeck) $(element).prop('checked', true).closest('label').addClass('active');
			else $(element).prop('checked', false).closest('label').removeClass('active');
		});

		if(Identity.code == "03002" && record.faction_code == "jinteki") {
			row.addClass("disabled").find('label').addClass("disabled").find('input[type=radio]').attr("disabled", true);
		}
		if(record.type_code == "agenda" && record.faction_code != "neutral" && record.faction != Identity.faction) {
			row.addClass("disabled").find('label').addClass("disabled").find('input[type=radio]').attr("disabled", true);
		}
		if(DisplayColumns > 1 && counter % DisplayColumns === 0) {
			container = $('<div class="row"></div>').appendTo($('#collection-grid'));
		}
		container.append(row);
		counter++;
	});
}
