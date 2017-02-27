<?php

require('multiRequest.php');

// log failures to a logfile and to standard output
function errorLog($message) {
	$handle = fopen("log2.txt", "a");
	
	fwrite($handle, strftime("%D %T") . " == ");
	fwrite($handle, $message . "\n");
	
	echo $message . "\n";
	
	fclose($handle);
}

$artists = json_decode(file_get_contents("artists.json"), true);
//$artists = array('UpWAAYdPsEWv' => $artists['UpWAAYdPsEWv_yxvBzCjvg']);

// STEP 5 Image Search Process: query the image URL of each artist

$i = 0;
foreach ($artists as $artist) {
	
	if (!isset($artist["imageURL"])) { // don't repeat work
		
		echo $i++ . "/" . count($artists) . " complete!\n\n";
		
		$chartLyricsID = $artist['chartLyricsID'];
		
		if (isset($artist["musicBrainzID"]) && $artist["musicBrainzID"] != "") { // can't do if there's no MBID
			
			$url = 'http://musicbrainz.org/ws/2/artist/' . $artist["musicBrainzID"] . '?inc=url-rels';
			
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
			$nodes = $xpath->query("//*[@type='image']"); // query the image nodes	
			if ($nodes->length == 0) { // no images found
				$artists[$chartLyricsID]["imageURL"] = "";
				
				errorLog('No image found for "' . $artist['name'] . '"');
				
			} else { // match found
				$artists[$chartLyricsID]["imageURL"] = trim($nodes[0]->textContent);
				
				echo 'Found Image: "' . trim($nodes[0]->textContent) . '" for artist: "' . $artist['name'] . '"' . "\n";
			}
			
			$nodes = $xpath->query("//*[@type='wikipedia']"); // query the wikipedia nodes	
			if ($nodes->length == 0) { // no wikipedia link found
				$artists[$chartLyricsID]["wikipediaURL"] = "";
				
				errorLog('No Wikipedia page for "' . $artist['name'] . '"');
				
			} else { // match found
				$artists[$chartLyricsID]["wikipediaURL"] = trim($nodes[0]->textContent);
				
				echo 'Found Wikipedia Page: "' . trim($nodes[0]->textContent) . '" for artist: "' . $artist['name'] . '"' . "\n";
			}

		
			sleep(1); // MusicBrainz throttles us to 1 request per second. This script will take a few hours to run.
		} else {
			$artists[$chartLyricsID]["imageURL"] = "";
			$artists[$chartLyricsID]["wikipediaURL"] = "";
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
		
	} else {
		$i++;
	}
}

echo "Step 5 Complete!\n";

// SAVE JSON

// $handle = fopen("artists.json", "w");
// fwrite($handle, json_encode($artists));
// fclose($handle);
