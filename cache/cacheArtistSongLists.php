<?php

require("multiRequest.php");

$artists = json_decode(file_get_contents("artists.json"), true);
$artists_values = array_values($artists);

// STEP 3 Artist Elimination Process: query each artist's song list -- eliminate artists that have no lyrics available

$batch = 100;

// $start = 0;
// while (isset($artists_values[$start]['songURLs'])) {
	// $start++;
// }

for (; $start < count($artists_values); $start+=$batch) { // For each batch:
	
	echo $start . '/' . count($artists_values) . " artists complete.\n";
	
	$songListPageURLs = array();
	for ($i=$start; $i<$start+$batch and $i<count($artists_values); $i++) {
		$songListPageURLs[] = 'http://www.chartlyrics.com/' . $artists_values[$i]['chartLyricsID'] . '.aspx';
	}

	$responses = multiRequest($songListPageURLs); // Execution holds here until all the requests are completed
	
	for ($i = $start; $i<$start+$batch and $i<count($artists_values); $i++) {
		// look at the ith artist/response
		$page = $responses[$i - $start];
		$chartLyricsID = $artists_values[$i]['chartLyricsID'];
		
		preg_match("/<!-- Page -->.*<!-- \[END\] Page -->/s", $page, $matches); // extract the document content section
		
		$doc = new DOMDocument();
		$doc->loadHTML($matches[0]);
		
		$xpath = new DOMXPath($doc);
		$links = $xpath->query("//td/a[not(@rel)]"); // query the relevant a tags
		
		if ($links->length == 0) { // omit this artist
			unset($artists[$chartLyricsID]);
		} else { // include this artist
			
			$songURLs = array();
			foreach ($links as $link) {
				$songURLs[] = 'http://www.chartlyrics.com/' . $link->getAttribute('href');
			}
			$artists[$chartLyricsID]["songURLs"] = $songURLs;
		}
	}

	// Save Intermediate JSON

	// $handle = fopen("artists1.json", "w");
	// fwrite($handle, json_encode($artists));
	// fclose($handle);
	
	// $handle = fopen("artists2.json", "w");
	// fwrite($handle, json_encode($artists));
	// fclose($handle);

	echo count($artists) . " artists saved!\n";
}

echo "Step 3 Complete\n";

// SAVE JSON

$handle = fopen("artists.json", "w");
fwrite($handle, json_encode($artists));
fclose($handle);