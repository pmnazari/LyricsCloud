<html>
	<head>
		<title>Lyrics Cloud</title>
		<link rel="stylesheet" type="text/css" href="styles.css">
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script>
			
			////
			// Page Loading Infrastructure:
			////
			
			var pages = {};
			
			// Load HTML into memory to be used later
			function preparePage(name, location) {
				
				$.get({
					url: location,
					async: false,
					success: function( data ) {
					  pages[name] = data;
					}
				});
			}
			
			// Display a page by loading its HTML into the body element
			function loadPage(name) {
				
				if (name in pages) {
					$('body').html(pages[name]);
				} else {
					alert("No such page: " + name + "!");
				}
			}
			
			////
			// Global Variables:
			////
			
			var allArtists = {}; // the metadata of all the artists
			
			var artists = []; // required by Word Cloud Page and on
			var songs = []; // required by Word Cloud Page and on
			var word = ""; // required by Song List Page and on
			var song = {}; // required by Lyrics Page and on
			
			////
			// Prepare everything, then load the first page:
			////
			
			$(function() {
				// Prepare all the pages
				preparePage("artistSearch", "pages/artistSearchPage.html");
				preparePage("wordCloud", "pages/wordCloudPage.html");
				preparePage("songList", "pages/songListPage.html");
				preparePage("lyrics", "pages/lyricsPage.html");
				
				$.ajax({ // Load all artist metadata from the server
					url: "ajax/allArtistMetadata.php",
					dataType: "json",
					async: false,
					success: function(data) {
						allArtists = data;
					}
				});
				
				loadPage("artistSearch"); // Display the first page
			});
		</script>
	</head>
	<body>
	</body>
</html>