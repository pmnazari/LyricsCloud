<?php

require('multiRequest.php');

// STEP 4 Database Matching Process: query the MusicBrainz ID of each artist

$databaseMatchingQueryURLs = array();
foreach ($artists as $artist) {
	$url = 'http://musicbrainz.org/ws/2/artist/?query=artist:"' . $artist["name"] . '"';
	if ($artist["disambiguator"] != "") { // the artist has a disambiguator
		$url .= '^20 OR comment:(' . $artist["disambiguator"] . ')';
	}
	$databaseMatchingQueryURLs[] = $url;
}


$responses = multiRequest($databaseMatchingQueryURLs); // Execution holds here until all the requests are completed

$i = 0;
foreach ($artists as $chartLyricsID => $artist) {
	// look at the ith artist/response
	$xml = $responses[$i];	
	
	echo $xml;
	
	$doc = new DOMDocument();
	$doc->loadHTML($xml); // the response is well-formed XML
	
	$xpath = new DOMXPath($doc);
	$matches = $xpath->query("//artist"); // query the matches
	
	if ($matches->length == 0) { // no matches found
		$artists[$chartLyricsID]["musicBrainzID"] = "";
	} else { // match found
		$artists[$chartLyricsID]["musicBrainzID"] = $matches[0]->getAttribute("id");
		echo 'ID found!';
	}
	
	$i++; // next
}