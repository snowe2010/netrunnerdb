var CardDB = null,
	SetDB = null;

function when_all_parsed() {
	if(CardDB && IsModified === false) return;
	var sets_data = SetsData || JSON.parse(localStorage.getItem('sets_data_'+Locale));
	if(!sets_data) return;
	SetDB = TAFFY(sets_data);
	SetDB({code:"alt"}).remove();

	var cards_data = CardsData || JSON.parse(localStorage.getItem('cards_data_'+Locale));
	CardDB = TAFFY(cards_data);
	CardDB({set_code:"alt"}).remove();
}

$(function() {
	when_all_parsed();
	$.when(promise1, promise2).done(when_all_parsed);
});
