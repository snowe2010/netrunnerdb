
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
when_all_parsed();
$.when(promise1, promise2).done(when_all_parsed);
});
