NRDB.data_loaded.add(function() {
	NRDB.data.cards({set_code:"alt"}).remove();
	CardNames = [];
	NRDB.data.cards().each(function (record, recordnumber) {
		CardNames.push({code: record.code, title: record.title, type: record.type, token: record.title.replace(/\W+/, ' ').trim().toLowerCase()});
	});
	$('#btn-import').prop('disabled', false);
});

$(function() {
	$('#analyzed').on({
		click: click_option
	}, 'ul.dropdown-menu a');
	$('#analyzed').on({
		click: click_trash
	}, 'a.glyphicon-trash');
})

function click_trash(event) {
	$(this).closest('li.list-group-item').remove();
	update_stats();
}
function do_import() {
	$('#analyzed').empty();
	var content = $('textarea[name="content"]').val();
	var lines = content.split(/[\r\n]+/);
	var size = 0;
	for(var i=0; i<lines.length; i++) {
		var a = import_one_line(lines[i], i);
		if(!a) continue;
		$('#analyzed').append('<li class="list-group-item">'+a+'<a class="pull-right glyphicon glyphicon-trash"></a></li>');
	}
	update_stats();
}
function import_one_line(line, lineNumber) {
	var qty, name = line.replace(/\(.*\)/, '').replace(/\W+/g, ' ').replace(/\s+/, ' ').trim().toLowerCase();
	if(name.match(/^(\d+)x?\s*(.*)/)) {
		qty = parseInt(RegExp.$1, 10);
		name = RegExp.$2;
	} else if(name.match(/(.*)\s*x?(\d+)$/)) {
		qty = parseInt(RegExp.$2, 10);
		name = RegExp.$1;
	}
	if(name == "") return;
	var options = [];
	var query = NRDB.data.cards({token: {likenocase:name}});
	if(query.count() == 1) {
		var record = query.first();
		options.push({code: record.code, name: record.title, type: record.type});
	} else if(query.count() > 1) {
		query.each(function (record,recordnumber) {
			options.push({code: record.code, name: record.title, type: record.type});
		});
	} else if(query.count() == 0) {
		var matches = [];
		$.each(CardNames, function(index, row) {
			var score = row.token.score(name, 0.9);
			matches.push({code: row.code, title: row.title, type: row.type, score: score});
		});
		matches.sort(function (a,b) { return a.score > b.score ? -1 : a.score < b.score ? 1 : 0 });
		var bestScore = matches[0].score;
		for(var i=0; i<5 && matches[i].score > 0.4 && matches[i].score > bestScore * 0.9; i++) {
			options.push({code: matches[i].code, name: matches[i].title, type: matches[i].type});
		}
	}
	var qty_text = "", qty_int = qty;
	if(qty == null) {
		options = $.grep(options, function (element) {
			return element.type == "Identity";
		});
		qty_int = 1;
	} else {
		qty_text = qty+"x ";
	}
	
	if(options.length == 0) {
		if(qty == null) return; 
		return '<i>No match for '+name+'</i>';
	} else if(options.length == 1) {
		return '<input type="hidden" name="'+lineNumber+'" value="'+options[0].code+':'+qty_int+'">'
		+qty_text+'<a class="card" data-code="'+options[0].code+'" href="#">'+options[0].name+' </a>';
	} else {
		var text = '<input type="hidden" name="'+lineNumber+'" value="'+options[0].code+':'+qty_int+'">'
		+qty_text+'<a class="card dropdown-toggle text-warning" data-toggle="dropdown" data-code="'+options[0].code+'" href="#">'+options[0].name+' <span class="caret"></span></a>';
		text += '<ul class="dropdown-menu">';
		$.each(options, function (index, option) {
			text += '<li><a href="#" data-code="'+option.code+'">'+option.name+'</a></li>';
		});
		text += '</ul>';
		return text;
	}
}
function click_option(event) {
	var name = $(this).text();
	var code = $(this).data('code');
	var input = $(this).closest('li.list-group-item').find('input[type="hidden"]');
	input.val(input.val().replace(/^\d+/, code));
	$(this).closest('li.list-group-item').find('a.card').html(name+' <span class="caret"></span>').data('code', code);
	update_stats();
}
function update_stats() {
	var deck = {}, size = 0, types = {};
	$('#analyzed input[type="hidden"]').each(function (index, element) {
		var card = $(element).val().split(':');
		var code = card[0], qty = parseInt(card[1], 10);
		deck[code] = qty;
		var record = NRDB.data.cards({code:code}).first();
		types[record.type] = types[record.type] || 0;
		types[record.type]+=qty;
	});
	var html = '';
	$.each(types, function (key, value) {
		if(key != "Identity") {
			size+=value;
			html += value+' '+key+'s.<br>';
		}
	});
	html = size+' Cards.<br>'+html;
	$('#stats').html(html);
	if($('#analyzed li').size() > 0) {
	  $('#btn-save').prop('disabled', false);
	} else {
	  $('#btn-save').prop('disabled', true);
	}
}
function do_save() {
	var deck = {};
	$('#analyzed input[type="hidden"]').each(function (index, element) {
		var card = $(element).val().split(':');
		var code = card[0], qty = parseInt(card[1], 10);
		deck[code] = qty;
	});
	$('input[name="content"]').val(JSON.stringify(deck));
}