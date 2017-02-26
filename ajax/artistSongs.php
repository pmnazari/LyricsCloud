<?php

/*

Should return a JSON object of the form:

	[
		{
			"artist": <artist name>,
			"title": <song title>,
			"lyrics": <song lyrics>
		}, ... for each song
	]


*/

require("../cache/multiRequest.php");
ini_set('memory_limit','1G');

$ids = $_GET['ids'];

$artists = json_decode(file_get_contents("../cache/artists.json"), true);

$songs = array();
foreach ($ids as $chartLyricsID) {
	
	$artist = $artists[$chartLyricsID];
	
	$batch = 200;
	
	// Get artist songs:
	for ($start=0; $start < count($artist["songURLs"]); $start+=$batch) { // For each batch:
		
		$songURLs = array_slice($artist["songURLs"], $start, $batch);
				
		$responses = multiRequest($songURLs); // Execution holds here until all the requests are completed
		
		foreach ($responses as $page) {
						
			preg_match("/<!-- Breadcrumb -->.*<!-- \[END\] Breadcrumb -->/s", $page, $matches); // extract the breadcrumb section
			
			if (!isset($matches[0])) { // if no match, skip this song
				continue;
			}
			
			$doc = new DOMDocument();
			$doc->loadHTML($matches[0]);
			$xpath = new DOMXPath($doc);
			
			// query the song name
			$breadcrumbs = $xpath->query("//div[@id='breadcrumb']"); // query the relevant div tag
			preg_match("/(?<=\>)[^\>]*$/", $breadcrumbs[0]->textContent, $matches); // extract the last breadcrumb
			$title = trim(trim(trim($matches[0]), chr(160) . chr(194)));
			
			// query the lyrics
			preg_match("/(?<=<p>)( <img.*?>)?(.*?)(?=<\/p>)/s", $page, $matches);
			if (!isset($matches[2])) { // if no match, skip this song
				continue;
			}
			$lyrics = trim($matches[2]);
			
			$songs[] = array(
				"artist" => $artist["name"],
				"title" => $title,
				"lyrics" => $lyrics
			);
			
		}
	}
}

echo json_encode($songs);

// $handle = fopen("artistSongs_debug.json", "w");
// fwrite($handle, json_encode($songs));
// fclose($handle);