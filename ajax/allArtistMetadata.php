<?php

/*

Should return a JSON object of the form:

	{
		<ChartLyrics ID>: {
			"chartLyricsID": <ChartLyrics ID>,
			"musicBrainzID": <MusicBrainz ID>,
			"name": <artist name>,
			"popularity": <popularity (1-10)>,
			"imageURL": <image URL>
		}, ... for each artist
	}


*/

echo file_get_contents("../cache/artists.json");