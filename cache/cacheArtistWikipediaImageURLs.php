<?php

require('multiRequest.php');

// log failures to a logfile and to standard output
function errorLog($message) {
	$handle = fopen("log3.txt", "a");
	
	fwrite($handle, strftime("%D %T") . " == ");
	fwrite($handle, $message . "\n");
	
	echo $message . "\n";
	
	fclose($handle);
}

$artists = json_decode(file_get_contents("artists.json"), true);
//$artists = array('UpWAAYdPsEWv' => $artists['UpWAAYdPsEWv_yxvBzCjvg']);

// STEP 6 Wikipedia Image Search Process: query the Wikipedia image URL of each artist

$batchTitles = array(); // An array of the titles of 50 pages that will form one batch

$i = 0;
foreach ($artists as $artist) {
	
	if (!isset($artist["wikipediaImageURL"])) { // don't repeat work
		
		echo $i++ . "/" . count($artists) . " complete!\n\n";
		
		$chartLyricsID = $artist['chartLyricsID'];
		
		$artists[$chartLyricsID]["wikipediaImageURL"] = "";
		
		if (isset($artist["wikipediaURL"]) && $artist["wikipediaURL"] != "") { // can't do if there's no Wikipedia URL
			
			preg_match("/\/wiki\/(.*)$/", $artist["wikipediaURL"], $matches); // extract the page title
			$batchTitles[urlencode(urldecode($matches[1]))] = $chartLyricsID;
		}
		
		if (count($batchTitles) == 50) { // ready to send a batch!
			
			$url = 'https://en.wikipedia.org/w/api.php?action=query&titles='
				. substr(array_reduce(array_keys($batchTitles), function($carry, $item) { // concatenate the titles, with '|' as a separator
					return $carry . '|' . $item;
				}), 1) // omit the first '|'
				. '&prop=pageimages&format=json&pithumbsize=150&pilimit=50';
						
			$json = file_get_contents($url); // execution will hold here
			$results = json_decode($json, true)["query"];
						
			foreach ($results["normalized"] as $unit) { // normalize page titles
				$batchTitles[urlencode($unit["to"])] = $batchTitles[urlencode($unit["from"])];
			}
			
			foreach ($results["pages"] as $page) {
				
				$chartLyricsID = $batchTitles[urlencode($page["title"])];								
				
				if (isset($page["thumbnail"]) and isset($artists[$chartLyricsID])) {
					
					$imageURL = $page["thumbnail"]["source"];
					
					$artists[$chartLyricsID]["wikipediaImageURL"] = $imageURL;
					
					echo $artists[$chartLyricsID]['name'] . " :: " . $imageURL . "\n";
				}
			}
			
			$batchTitles = array(); // reset								
			
			// Save our intermediate progress
				
			// The 2 save files protect against the possibility that the program crashes (memory overflow) when opening one and loses all the data
			
			$handle = fopen("artists1.json", "w");
			fwrite($handle, json_encode($artists));
			fclose($handle);
			
			$handle = fopen("artists2.json", "w");
			fwrite($handle, json_encode($artists));
			fclose($handle);
			
			echo count($artists) . " artists saved!\n";
			
			
			//sleep(1);
		}

	} else {
		$i++;
	}
}

echo "Step 6 Complete!\n";

// SAVE JSON

// $handle = fopen("artists.json", "w");
// fwrite($handle, json_encode($artists));
// fclose($handle);
