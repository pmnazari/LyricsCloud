<?php

require("multiRequest.php");

//STEP 1 Indexing Process: get ChartLyrics artist list pages URLs

$index_url = 'http://www.chartlyrics.com/';

$index_html = file_get_contents($index_url); // This request is a bottleneck

preg_match("/<!-- Footer -->.*<!-- \[END\] Footer -->/s", $index_html, $matches); // extract the document footer

$doc = new DOMDocument();
$doc->loadHTML($matches[0]);

$xpath = new DOMXPath($doc);
$links = $xpath->query("//div[@class='top_box']//a"); // query the relevant a tags

$artistListPageURLs = array();
foreach ($links as $link) {
	$artistListPageURLs[] = 'http://www.chartlyrics.com/' . $link->getAttribute('href');
}

echo "Step 1 Complete\n";

//STEP 2 Artist List Process: query each ChartLyrics artist list page; get artist names, ChartLyrics IDs, disambiguators, and popularity

$responses = multiRequest($artistListPageURLs); // Execution holds here until all the requests are completed

$artists = array();
foreach ($responses as $page) {
	
	preg_match("/<!-- Page -->.*<!-- \[END\] Page -->/s", $page, $matches); // extract the document content section
	
	$doc = new DOMDocument();
	$doc->loadHTML($matches[0]);
	
	$xpath = new DOMXPath($doc);
	$rows = $xpath->query("//table/tr"); // query the relevant tr tags
		
	foreach ($rows as $row) {
		
		// extract the artist popularity
		$popularityImage = ($xpath->query(".//img", $row))[0];
		$popularity = intval($popularityImage->getAttribute("alt"));
		
		// extract the artist name and ChartLyrics ID
		$a = $xpath->query(".//a", $row)[0];
		$url = $a->getAttribute('href');
		preg_match("/\/(.*)\.aspx/", $url, $matches);
		
		$id = $matches[1];
		$name = $a->nodeValue;
		
		// extract the artist disambiguator
		$column = $xpath->query(".//td[2]", $row)[0];
		$disambiguator = "";
		foreach ($column->childNodes as $child) {
			if ($child instanceof DOMText) { // this is a text node
				$disambiguator .= $child->nodeValue;
			}
		}
		$disambiguator = trim($disambiguator);
		
		$artists[$id] = array('chartLyricsID' => $id, 'name' => $name, 'disambiguator' => $disambiguator, 'popularity' => $popularity);
	}
}

echo "Step 2 Complete\n";

// SAVE JSON

$handle = fopen("artists.json", "w");
fwrite($handle, json_encode($artists));
fclose($handle);

echo count($artists) . ' artists saved!';