<?php

require('multiRequest.php');

// encode a string to be used in a URL
function encodeURL($string) {
	return urlencode(str_replace("/", "+", $string));
}

// log failures to a logfile and to standard output
function errorLog($message) {
	$handle = fopen("log.txt", "a");
	
	fwrite($handle, strftime("%D %T") . " == ");
	fwrite($handle, $message . "\n");
	
	echo $message . "\n";
	
	fclose($handle);
}

$artists = json_decode(file_get_contents("artists.json"), true);

// STEP 4 Database Matching Process: query the MusicBrainz ID of each artist

$i = 0;
foreach ($artists as $artist) {
	
	if (!isset($artist["musicBrainzID"])) { // make sure we're not repeating work
		
		echo $i++ . "/" . count($artists) . " complete!\n\n";
		
		$chartLyricsID = $artist['chartLyricsID'];
		
		$url = 'http://musicbrainz.org/ws/2/artist/?query=artist:"' . encodeURL($artist['name']) . '"';
		if ($artist["disambiguator"] != "") { // the artist has a disambiguator
			$url .= '^20+OR+comment:(' . encodeURL($artist['disambiguator']) . ')';
		}
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_USERAGENT, 'sfalafelis@gmail.com'); // MusicBrainz blocks requests that omit the user-agent header
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$xml = curl_exec($curl); // execution holds here until the request is completed.
		
		//echo $xml;
		
		if ($xml === false) { // Request Failed
			errorLog('Failure on url: "' . $url . '"' . "\n");
			continue;
		}
				
		$doc = new DOMDocument();
		$doc->loadXML($xml); // the response is well-formed XML
		
		$xpath = new DOMXPath($doc);
		$nodes = $xpath->query("//*"); // query all the nodes
		$matches = array();
		foreach ($nodes as $node) { // filter out the artist tags			
			if ($node->tagName == 'artist') {
				$matches[] = $node;
			}
		}
			
		if (count($matches) == 0) { // no matches found
			$artists[$chartLyricsID]["musicBrainzID"] = "";
			
			errorLog('No matches found on url: "' . $url . '"' . "\n");
			
		} else { // match found
			$artists[$chartLyricsID]["musicBrainzID"] = $matches[0]->getAttribute("id");
			
			echo "Found MBID: " . $matches[0]->getAttribute("id") . ' for artist: "' . $artist['name'] . '"' . "\n";
		}
	
		if ($i % 60 == 0) { // every minute, save our intermediate progress
			
			// The 2 save files protect against the possibility that the program crashes (memory overflow) when opening one and loses all the data
			
			$handle = fopen("artists1.json", "w");
			fwrite($handle, json_encode($artists));
			fclose($handle);
			
			$handle = fopen("artists2.json", "w");
			fwrite($handle, json_encode($artists));
			fclose($handle);
			
			echo count($artists) . " artists saved!\n";
		}
	
		sleep(1); // MusicBrainz throttles us to 1 request per second. This script will take a few hours to run.
	} else {
		$i++;
	}
}

echo "Step 4 Complete!\n";

// SAVE JSON

$handle = fopen("artists.json", "w");
fwrite($handle, json_encode($artists));
fclose($handle);
