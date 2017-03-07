Feature: Artist Search Bar
	In order to browse artist lyrics clouds
	As a user
	I need to be able to search for artists in an artist search bar

	Scenario: Observing an empty artist search bar
		Given there is an artist search bar
		Then the artist search bar should be empty
