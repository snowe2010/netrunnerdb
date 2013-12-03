function process_deck() {
	
	var bytype = {};
	Identity = CardDB({indeck:{'gt':0},type_code:'identity'}).first();
	if(!Identity) {
		return;
	}

	CardDB({indeck:{'gt':0},type_code:{'!is':'identity'}}).order("type,title").each(function(record) {
		var type = record.type_code, subtypes = record.subtype_code ? record.subtype_code.split(" - ") : [];
		if(type == "ice") {
			 if(subtypes.indexOf("barrier") >= 0) {
				 type = "barrier";
			 }
			 if(subtypes.indexOf("code gate") >= 0) {
				 type = "code-gate";
			 }
			 if(subtypes.indexOf("sentry") >= 0) {
				 type = "sentry";
			 }
		}
		if(type == "program") {
			 if(subtypes.indexOf("icebreaker") >= 0) {
				 type = "icebreaker";
			 }
		}
		var influence = 0, faction_code = '';
		if(record.faction != Identity.faction) {
			faction_code = record.faction_code;
			influence = record.factioncost * record.indeck;
		}
		
		if(bytype[type] == null) bytype[type] = [];
		bytype[type].push({
			card: record,
			qty: record.indeck,
			influence: influence,
			faction: faction_code
		});
	});
	bytype.identity = [{
		card: Identity,
		qty: 1,
		influence: 0,
		faction: ''
	}];
	
	return bytype;
}

function getDisplayDescriptions(sort) {
	var dd = {
	    'type': [
	        [ // first column

	            {
	                id: 'event',
	                label: 'Event',
	                image: 'bundles/netrunnerdbbuilder/images/types/event.png'
	            }, {
	                id: 'hardware',
	                label: 'Hardware',
	                image: 'bundles/netrunnerdbbuilder/images/types/hardware.png'
	            }, {
	                id: 'resource',
	                label: 'Resource',
	                image: 'bundles/netrunnerdbbuilder/images/types/resource.png'
	            }, {
	                id: 'agenda',
	                label: 'Agenda',
	                image: 'bundles/netrunnerdbbuilder/images/types/agenda.png'
	            }, {
	                id: 'asset',
	                label: 'Asset',
	                image: 'bundles/netrunnerdbbuilder/images/types/asset.png'
	            }, {
	                id: 'upgrade',
	                label: 'Upgrade',
	                image: 'bundles/netrunnerdbbuilder/images/types/upgrade.png'
	            }, {
	                id: 'operation',
	                label: 'Operation',
	                image: 'bundles/netrunnerdbbuilder/images/types/operation.png'
	            },

	        ],
	        [ // second column
	            {
	                id: 'icebreaker',
	                label: 'Icebreaker',
	                image: 'bundles/netrunnerdbbuilder/images/types/program.png'
	            }, {
	                id: 'program',
	                label: 'Program',
	                image: 'bundles/netrunnerdbbuilder/images/types/program.png'
	            }, {
	                id: 'barrier',
	                label: 'Barrier',
	                image: 'bundles/netrunnerdbbuilder/images/types/ice.png'
	            }, {
	                id: 'code-gate',
	                label: 'Code Gate',
	                image: 'bundles/netrunnerdbbuilder/images/types/ice.png'
	            }, {
	                id: 'sentry',
	                label: 'Sentry',
	                image: 'bundles/netrunnerdbbuilder/images/types/ice.png'
	            }, {
	                id: 'ice',
	                label: 'ICE',
	                image: 'bundles/netrunnerdbbuilder/images/types/ice.png'
	            }
	        ]
	    ],
	    'faction': [
	        [],
	        [{
	            id: 'anarch',
	            label: 'Anarch',
	            image: 'bundles/netrunnerdbbuilder/images/factions/16px/anarch.png'
	        }, {
	            id: 'criminal',
	            label: 'Criminal',
	            image: 'bundles/netrunnerdbbuilder/images/factions/16px/criminal.png'
	        }, {
	            id: 'haas-bioroid',
	            label: 'Haas-Bioroid',
	            image: 'bundles/netrunnerdbbuilder/images/factions/16px/haas-bioroid.png'
	        }, {
	            id: 'jinteki',
	            label: 'Jinteki',
	            image: 'bundles/netrunnerdbbuilder/images/factions/16px/jinteki.png'
	        }, {
	            id: 'nbn',
	            label: 'NBN',
	            image: 'bundles/netrunnerdbbuilder/images/factions/16px/nbn.png'
	        }, {
	            id: 'shaper',
	            label: 'Shaper',
	            image: 'bundles/netrunnerdbbuilder/images/factions/16px/shaper.png'
	        }, {
	            id: 'weyland-consortium',
	            label: 'Weyland Consortium',
	            image: 'bundles/netrunnerdbbuilder/images/factions/16px/weyland-consortium.png'
	        }, {
	            id: 'neutral',
	            label: 'Neutral',
	            image: 'bundles/netrunnerdbbuilder/images/factions/16px/neutral.png'
	        }, ]
	    ],
	    'number': [],
	    'title': [
	        [{
	            id: 'cards',
	            label: 'Cards'
	        }]
	    ]
	};
	return dd[sort];
}

function update_deck() {
	Identity = CardDB({indeck:{'gt':0},type_code:'identity'}).first();
	if(!Identity) return;

	var displayDescription = getDisplayDescriptions(DisplaySort);
	if(displayDescription == null) return;
	
	if(DisplaySort === 'faction') {
		for(var i=0; i<displayDescription[1].length; i++) {
			if(displayDescription[1][i].id === Identity.faction_code) {
				displayDescription[0] = displayDescription[1].splice(i, 1);
				break;
			}
		}
	}
	if(DisplaySort === 'number' && displayDescription.length === 0) {
		var rows = [];
		SetDB().order('cyclenumber,number').each(function (record) {
			rows.push({id: record.code, label: record.name});
		});
		displayDescription.push(rows);
	}
	
	$('#deck-content').empty();
	var cols_size = 12/displayDescription.length;
	for(var colnum=0; colnum<displayDescription.length; colnum++) {
		var rows = displayDescription[colnum];
		var div = $('<div>').addClass('col-sm-'+cols_size).appendTo($('#deck-content'));
		for(var rownum=0; rownum<rows.length; rownum++) {
			var row = rows[rownum];
			var item = $('<h5> '+row.label+' (<span></span>)</h5>').hide();
			if(row.image) {
				$('<img>').addClass(DisplaySort+'-icon').attr('src', Url_Asset.replace('XXX', row.image)).prependTo(item);
			}
			var content = $('<div class="deck-'+row.id+'"></div>')
			div.append(item).append(content);
		}
	}
	
	InfluenceLimit = 0;
	var cabinet = {};
	var parts = Identity.title.split(/: /);
	$('#identity').html('<a href="#cardModal" class="card" data-toggle="modal" data-index="'+Identity.code+'">'+parts[0]+' <small>'+parts[1]+'</small></a>');
	$('#img_identity').prop('src', Identity.imagesrc);
	InfluenceLimit = Identity.influencelimit;
	MinimumDeckSize = Identity.minimumdecksize;

	var latestpack = SetDB({name:Identity.setname}).first();
	CardDB({indeck:{'gt':0},type_code:{'!is':'identity'}}).order(DisplaySort === 'number' ? 'code' : 'title').each(function(record) {
		var pack = SetDB({name:record.setname}).first();
		if(latestpack.cyclenumber < pack.cyclenumber || (latestpack.cyclenumber == pack.cyclenumber && latestpack.number < pack.number)) latestpack = pack;
		
		var influence = '';
		if(record.faction != Identity.faction) {
			var faction = record.faction.toLowerCase().replace(' ','-');
			var infcost = record.factioncost * record.indeck;
			for(var i=0; i<infcost; i++) {
				if(i%5 == 0) influence+=" ";
				influence+="&bull;";
			}
			influence = ' <span class="influence-'+faction+'">'+influence+'</span>';
		}

		var item = null;
		var criteria = null;
		
		if(DisplaySort === 'type') {
			criteria = record.type_code, subtypes = record.subtype_code ? record.subtype_code.split(" - ") : [];
			if(criteria == "ice") {
				 if(subtypes.indexOf("barrier") >= 0) criteria = "barrier";
				 if(subtypes.indexOf("code gate") >= 0) criteria = "code-gate";
				 if(subtypes.indexOf("sentry") >= 0) criteria = "sentry";
			}
			if(criteria == "program") {
				 if(subtypes.indexOf("icebreaker") >= 0) criteria = "icebreaker";
			}
		} else if(DisplaySort === 'faction') {
			criteria = record.faction_code;
		} else if(DisplaySort === 'number') {
			criteria = record.set_code;
			var number_of_sets = Math.ceil(record.indeck / record.quantity);
			var alert_number_of_sets = number_of_sets > 1 ? '<small class="text-warning">'+number_of_sets+' sets needed</small> ' : '';
			item = $('<div>'+record.indeck+'x <a href="#cardModal" class="card" data-toggle="modal" data-index="'+record.code+'">'+record.title+'</a> (#'+record.number+') '+alert_number_of_sets+influence+'</div>');
		} else if(DisplaySort === 'title') {
			criteria = 'cards';
		}

		if(item === null) item = $('<div>'+record.indeck+'x <a href="#cardModal" class="card" data-toggle="modal" data-index="'+record.code+'">'+record.title+'</a>'+influence+'</div>');
		item.appendTo($('#deck-content .deck-'+criteria));
		
		cabinet[criteria] |= 0;
		cabinet[criteria] = cabinet[criteria] + record.indeck;
		$('#deck-content .deck-'+criteria).prev().show().find('span').html(cabinet[criteria]);
		
	});
	$('#latestpack').html('Cards up to <i>'+latestpack.name+'</i>');
	check_influence();
	check_decksize();
	$('#deck').show();
}


function check_decksize() {
	DeckSize = CardDB({indeck:{'gt':0},type_code:{'!is':'identity'}}).select("indeck").reduce(function (previousValue, currentValue) { return previousValue+currentValue; }, 0);
	MinimumDeckSize = Identity.minimumdecksize;
	$('#cardcount').html(DeckSize+" cards (min "+MinimumDeckSize+")")[DeckSize < MinimumDeckSize ? 'addClass' : 'removeClass']("text-danger");
	if(Identity.side_code == 'corp') {
		AgendaPoints = CardDB({indeck:{'gt':0},type_code:'agenda'}).select("indeck","agendapoints").reduce(function (previousValue, currentValue) { return previousValue+currentValue[0]*currentValue[1]; }, 0);
		var min = Math.floor(Math.max(DeckSize, MinimumDeckSize) / 5) * 2 + 2, max = min+1;
		$('#agendapoints').html(AgendaPoints+" agenda points (between "+min+" and "+max+")")[AgendaPoints < min || AgendaPoints > max ? 'addClass' : 'removeClass']("text-danger");
	} else {
		$('#agendapoints').empty();
	}
}

function check_influence() {
	InfluenceSpent = 0;
	var repartition_influence = {};
	CardDB({indeck:{'gt':0},faction_code:{'!is':Identity.faction_code}}).each(function(record) {
		if(record.factioncost) {
			var inf, faction = record.faction_code;
			if(Identity.code == "03029" && record.type_code == "program") {
				inf = record.indeck > 1 ? (record.indeck-1) * record.factioncost : 0;
			} else {
				inf = record.indeck * record.factioncost;
			}
			if(inf) {
				InfluenceSpent += inf;
				repartition_influence[faction] = (repartition_influence[faction] || 0) + inf;
			}
		}
	});
	var graph = '';
	$.each(repartition_influence, function (key, value) {
		var ronds = '';
		for(var i=0; i<value; i++) {
			ronds += '&bull;';
		}
		graph += '<span class="influence-'+key+'" title="'+key+': '+value+'">'+ronds+'</span>';
	})
	$('#influence').html(InfluenceSpent+" influence spent (max "+InfluenceLimit+") "+graph)[InfluenceSpent > InfluenceLimit ? 'addClass' : 'removeClass']("text-danger");
}

$(function () {
	$('<div class="modal fade" id="cardModal" tabindex="-1" role="dialog" aria-labelledby="cardModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3 class="modal-title">Modal title</h3><div class="row"><div class="col-sm-12 text-center"><div class="btn-group" data-toggle="buttons" id="modal-qty"></div></div></div></div><div class="modal-body"><div class="row"><div class="col-sm-6" id="modal-image"></div><div class="col-sm-6" id="modal-info"></div></div></div></div></div></div>').appendTo('body');
	
	if(!Modernizr.touch)  $('body').on({mouseover: display_qtip}, '.card');
	if(Modernizr.touch) $('#svg').remove();
		
	$('body').on({click: display_modal}, '.card');
	
});

function display_modal(event) {
	$(this).qtip('hide');
	var code = $(this).data('index') || $(this).closest('tr').data('index');
	fill_modal(code);
}

function fill_modal(code) {
	var card = CardDB({code:code}).first();
	var modal = $('div.modal').data('index', code);
	modal.find('h3.modal-title').text(card.title);
	modal.find('#modal-image').html('<img class="img-responsive" src="'+card.imagesrc+'">');
	modal.find('#modal-info').html(
	  '<div class="card-info">'+get_type_line(card)+'</div>'
	  +'<div><small>' + card.faction + ' &bull; '+ card.setname + '</small></div>'
	  +'<div class="card-text"><small>'+text_format(card.text)+'</small></div>'
	);

	if($('#modal-qty') && typeof Filters != "undefined") {

		var qty = '';
	  	for(var i=0; i<=card.maxqty; i++) {
	  		qty += '<label class="btn btn-default"><input type="radio" name="qty" value="'+i+'">'+i+'</label>';
	  	}
	   	modal.find('#modal-qty').html(qty);
	   	
		$('#modal-qty label').each(function (index, element) {
			if(index == card.indeck) $(element).addClass('active');
			else $(element).removeClass('active');
		});
		if(card.type_code == "agenda" && card.faction_code != "neutral" && card.faction_code != Identity.faction_code) {
			modal.find('label').addClass("disabled").find('input[type=radio]').attr("disabled", true);
		}
		if(card.code == Identity.code) {
			modal.find('label').addClass("disabled").find('input[type=radio]').attr("disabled", true);
		}

		
	} else {
		if($('#modal-qty')) $('#modal-qty').closest('.row').remove();
	}
}

function text_format(text) {
	text = text.replace(
	  /\[([\w ]+)\]/g, 
	  function(match, p1) { return '<span class="sprite '+p1.replace(/ /, '_').toLowerCase()+'"></span>'; }
	).split("\n").join("</p><p>");
	
	return "<p>"+text+"</p>";
	
}
function get_type_line(card) {
	var type = '<span class="card-type">'+card.type+'</span>';
	if(card.subtype) type += '<span class="card-keywords">: '+card.subtype+'</span>';
	if(card.type_code == "agenda") type += ' &middot; <span class="card-prop">'+card.advancementcost+'/'+card.agendapoints+'</span>';
	if(card.type_code == "identity" && card.side_code == "corp") type += ' &middot; <span class="card-prop">'+card.minimumdecksize+'/'+card.influencelimit+'</span>';
	if(card.type_code == "identity" && card.side_code == "runner") type += ' &middot; <span class="card-prop">'+card.minimumdecksize+'/'+card.influencelimit+' '+card.baselink+'<span class="sprite link"></span></span>';
	if(card.type_code == "operation" || card.type_code == "event") type += ' &middot; <span class="card-prop">'+card.cost+'<span class="sprite credits"></span></span>';
	if(card.type_code == "resource" || card.type_code == "hardware") type += ' &middot; <span class="card-prop">'+card.cost+'<span class="sprite credits"></span></span>';
	if(card.type_code == "program") type += ' &middot; <span class="card-prop">'+card.cost+'<span class="sprite credits"></span> '+card.memoryunits+'<span class="sprite memory_unit"></span></span>';
	if(card.type_code == "asset" || card.type_code == "upgrade") type += ' &middot; <span class="card-prop">'+card.cost+'<span class="sprite credits"></span> '+card.trash+'<span class="sprite trash"></span></span>';
	if(card.type_code == "ice") type += ' &middot; <span class="card-prop">'+card.cost+'<span class="sprite credits"></span></span>';
	return type;
}

function display_qtip(event) {
	var code = $(this).data('index') || $(this).closest('tr').data('index');
	var card = CardDB({code:code}).first();
	var type = '<p class="card-info">'+get_type_line(card)+'</p>';
	var influence = '';
	for(var i=0; i<card.factioncost; i++) influence += "&bull;";
	if(card.strength != null) type += '<p>Strength <b>'+card.strength+'</b></p>';
	$(this).qtip({
		content: {
			text: '<h4>'+card.title+'</h4>'+type+text_format(card.text)+'<p style="text-align:right">'+influence+' '+card.faction+'</p>'
		},
		style: { 
			classes: 'qtip-bootstrap'
		},
		position: {
    		my : 'left center',
    		at : 'right center',
    		viewport: $(window)
		},
		show: {
			event: event.type,
			ready: true
		}/*,
		hide: {
			event: 'click'
		}*/
	}, event);
}


function export_bbcode() {
	var deck = process_deck(SelectedDeck);
	var colors = {
		"anarch": "#FF4500",
		"criminal": "#4169E1",
		"shaper": "#32CD32",
		"neutral": "#708090",
		"haas-bioroid": "#8A2BE2",
		"jinteki": "#DC143C",
		"nbn": "#FF8C00",
		"weyland-consortium": "#006400"
	};
	
	var lines = [];
	lines.push("[b]"+SelectedDeck.name+"[/b]");
	lines.push("");
	var types = ["identity", "event", "hardware", "resource", "icebreaker", "program", "agenda", "asset", "upgrade", "operation", "barrier", "code-gate", "sentry", "ice"];
	var typesstr = ["Identity", "Event", "Hardware", "Resource", "Icebreaker", "Program", "Agenda", "Asset", "Upgrade", "Operation", "Barrier", "Code Gate", "Sentry", "ICE"];
	$.each(types, function (n, type) {
		if(deck[type] != null) {
			if(type == "identity") {
				var slot = deck[type][0];
				lines.push('[url=http://netrunnerdb.com/'+Locale+'/card/'
				 + slot.card.code
				 + ']'
				 + slot.card.title
				 + '[/url] ('
				 + slot.card.setname
				 + ")");
				 lines.push("");
			} else {
				var count = deck[type].reduce(function (prev, curr) { return prev + curr.qty; }, 0);
				lines.push("[b]"+typesstr[n]+"[/b] ("+count+")");
				$.each(deck[type], function (n, slot) {
					var inf = "";
					for(var i=0; i<slot.influence; i++) {
						if(i%5==0) inf += " ";
						inf+="•";
					}
					lines.push(slot.qty + 'x [url=http://netrunnerdb.com/'+Locale+'/card/'
					 + slot.card.code
					 + ']'
					 + slot.card.title
					 + '[/url] [i]('
					 + slot.card.setname
					 + ")[/i]"
					 + ( slot.influence ? '[color=' + colors[slot.faction] + ']' + inf + '[/color]' : '' )
					);
				});
				lines.push("");
			}
		}
	});
	if(typeof Decklist != "undefined" && Decklist != null) {
		lines.push("Decklist [url="+location.href+"]published on NetrunnerDB[/url].");
	} else {
		lines.push("Deck built on [url=http://netrunnerdb.com]NetrunnerDB[/url].");
	}
	
	$('#export-deck').html(lines.join("\n"));
	$('#exportModal').modal('show');
}

function export_markdown() {
	var deck = process_deck(SelectedDeck);
	var lines = [];
	lines.push("# "+SelectedDeck.name);
	lines.push("");
	var types = ["identity", "event", "hardware", "resource", "icebreaker", "program", "agenda", "asset", "upgrade", "operation", "barrier", "code-gate", "sentry", "ice"];
	var typesstr = ["Identity", "Event", "Hardware", "Resource", "Icebreaker", "Program", "Agenda", "Asset", "Upgrade", "Operation", "Barrier", "Code Gate", "Sentry", "ICE"];
	$.each(types, function (n, type) {
		if(deck[type] != null) {
			if(type == "identity") {
				var slot = deck[type][0];
				lines.push('['
				 + slot.card.title
				 + '](http://netrunnerdb.com/'+Locale+'/card/'
				 + slot.card.code
				 + ') _('
				 + slot.card.setname
				 + ")_");
				 lines.push("");
			} else {
				var count = deck[type].reduce(function (prev, curr) { return prev + curr.qty; }, 0);
				lines.push("");
				lines.push("## "+typesstr[n]+" ("+count+")");
				lines.push("");
				$.each(deck[type], function (n, slot) {
					var inf = "";
					for(var i=0; i<slot.influence; i++) {
						if(i%5==0) inf += " ";
						inf+="•";
					}
					lines.push('* '+ slot.qty + 'x ['
					 + slot.card.title 
					 + '](http://netrunnerdb.com/'+Locale+'/card/'
					 + slot.card.code
					 + ') _('
					 + slot.card.setname
					 + ")_"
					 + ( slot.influence ? inf : '' )
					);
				});
				
			}
		}
	});
	lines.push("");
	if(typeof Decklist != "undefined" && Decklist != null) {
		lines.push("Decklist [published on NetrunnerDB]("+location.href+").");
	} else {
		lines.push("Deck built on [NetrunnerDB](http://netrunnerdb.com).");
	}
	
	$('#export-deck').html(lines.join("\n"));
	$('#exportModal').modal('show');
}

function export_plaintext() {
	var deck = process_deck(SelectedDeck);
	var lines = [];
	lines.push(SelectedDeck.name);
	lines.push("");
	var types = ["identity", "event", "hardware", "resource", "icebreaker", "program", "agenda", "asset", "upgrade", "operation", "barrier", "code-gate", "sentry", "ice"];
	var typesstr = ["Identity", "Event", "Hardware", "Resource", "Icebreaker", "Program", "Agenda", "Asset", "Upgrade", "Operation", "Barrier", "Code Gate", "Sentry", "ICE"];
	$.each(types, function (n, type) {
		if(deck[type] != null) {
			if(type == "identity") {
				var slot = deck[type][0];
				lines.push(slot.card.title
				 + ' ('
				 + slot.card.setname
				 + ")");
			} else {
				var count = deck[type].reduce(function (prev, curr) { return prev + curr.qty; }, 0);
				lines.push("");
				lines.push(typesstr[n]+" ("+count+")");
				$.each(deck[type], function (n, slot) {
					var inf = "";
					for(var i=0; i<slot.influence; i++) {
						if(i%5==0) inf += " ";
						inf+="•";
					}
					lines.push(slot.qty + 'x '
					 + slot.card.title
					 + ' ('
					 + slot.card.setname
					 + ")"
					 + ( slot.influence ? inf : '' )
					);
				});
				
			}
		}
	});
	lines.push("");
	if(typeof Decklist != "undefined" && Decklist != null) {
		lines.push("Decklist published on http://netrunnerdb.com.");
	} else {
		lines.push("Deck built on http://netrunnerdb.com.");
	}
	$('#export-deck').html(lines.join("\n"));
	$('#exportModal').modal('show');
}
