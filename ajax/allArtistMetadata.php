<?php

/*

Should return a JSON object of the form:

	{
		<ChartLyrics ID>: {
			"chartLyricsID": <ChartLyrics ID>,
			"musicBrainzID": <MusicBrainz ID>,
			"name": <artist name>,
			"disambiguator": <disambiguator>,
			"popularity": <popularity (1-10)>,
			"imageURL": <image URL>
			"songURLs": [<song URL>, ...]
		}, ... for each artist
	}


*/

echo file_get_contents("../cache/artists.json");