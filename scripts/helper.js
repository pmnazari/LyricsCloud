
// returns a RegExp object that can be used to match the word in a lyrics
function regexForWord(test) {
	
	var regex = new RegExp("\\b" + test + "\\b", "ig");	
	return regex;
}
