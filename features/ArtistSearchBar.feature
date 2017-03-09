Feature: Artist Search Bar
	In order to browse artist lyrics clouds
	As a user
	I need to be able to search for artists in an artist search bar

	Scenario: Observing an empty artist search bar
		Given there is an artist search bar
		Then the artist search bar should be empty

	Scenario: There are more than three characters in the textbox
		Given there are more than three characters in the textbox
		Then the suggestions drop-down should be visible below the textbox
		And there should be at least three artists in the drop-down

	Scenario: An artist is chosen from the drop-down
		Given an artist is chosen from the drop-down
		Then the textbox should be updated to contain the name of the artist
