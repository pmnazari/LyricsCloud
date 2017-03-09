<?php

require_once "vendor/autoload.php";
require_once "vendor/phpunit/phpunit/src/Framework/Assert/Functions.php";

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
	public $driver;
	public $session;
	
	public $page;

	public $artistSearchBar;
	public $artistSearchTextField;
    public $searchButton;

	/**
	* Initializes context.
	*
	* Every scenario gets its own context instance.
	* You can also pass arbitrary arguments to the
	* context constructor through behat.yml.
	*/
	public function __construct()
	{
		$this->driver = new \Behat\Mink\Driver\Selenium2Driver();
		$this->session = new \Behat\Mink\Session($this->driver);

		$this->session->start();

		$this->session->visit('http://localhost:80/LyricsCloud/');
		$this->page = $this->session->getPage();

		$this->artistSearchBar = $this->page->find("css", "#artistSearchBar");
		$this->artistSearchTextField = $this->artistSearchBar->find("css", "#artistSearchTextField");

        $this->searchButton = $this->page->find("css", "#search");
	}

	public function __destruct()
	{
		$this->session->stop();
	}

	/**
	* @Given there is an artist search bar
	*/
	public function thereIsAnArtistSearchBar()
	{
		assertNotEquals(null, $this->artistSearchBar);
		// $this->artistSearchBar = $this->page->find("css", "#artistSearchBar");
	}

	/**
	* @Then the artist search bar should be empty
	*/
	public function theArtistSearchBarShouldBeEmpty()
	{

		assertEquals("", $this->artistSearchTextField->getValue());
	}

	/**
    * @Given there are more than three characters in the textbox
    */
    public function thereAreMoreThanThreeCharactersInTheTextbox()
    {
    	$this->artistSearchTextField->setValue('The Bea');
        sleep(3);
    }

    public $suggestions;

    /**
     * @Then the suggestions drop-down should be visible below the textbox
     */
    public function theSuggestionsDropDownShouldBeVisibleBelowTheTextbox()
    {
        $this->suggestions = $this->page->find("css", "#ui-id-1");
        assertNotEquals(null, $this->suggestions);
        assertTrue($this->suggestions->isVisible());
    }

    /**
     * @Then there should be at least three artists in the drop-down
     */
    public function thereShouldBeAtLeastThreeArtistsInTheDropDown()
    {
        $this->suggestions->findall('css', 'li');

        $i = 0;
        foreach ($this->suggestions as $suggestion) {
            ++$i;
        }

        assertGreaterThan($i, 2);
    }

    /**
     * @Given an artist is chosen from the drop-down
     */
    public function anArtistIsChosenFromTheDropDown()
    {
        $this->artistSearchTextField->setValue('The Bea');
        sleep(3);

        $this->suggestions = $this->page->find("css", "#ui-id-1");
        
        $firstsuggestion = $this->suggestions->find('css', 'li');

        $firstsuggestion->click();
    }

    /**
     * @Then the textbox should be updated to contain the name of the artist
     */
    public function theTextboxShouldBeUpdatedToContainTheNameOfTheArtist()
    {
        sleep(0.5);

        assertEquals("The Beach Boys", $this->artistSearchTextField->getValue());
    }

    /**
     * @Given the Artist Search Bar has three or fewer characters
     */
    public function theArtistSearchBarHasThreeOrFewerCharacters()
    {
        $this->artistSearchTextField->setValue('the');
    }

    /**
     * @Then the search button is not clickable
     */
    public function theSearchButtonIsNotClickable()
    {
        assertEquals('disabled', $this->searchButton->getAttribute('disabled'));
    }

    /**
     * @Given the Artist Search Bar has more than three characters
     */
    public function theArtistSearchBarHasMoreThanThreeCharacters()
    {
        $this->artistSearchTextField->setValue('the ');
        sleep(3);
    }

    /**
     * @Then the search button is clickable
     */
    public function theSearchButtonIsClickable()
    {
        assertEquals(null, $this->searchButton->getAttribute('disabled'));
    }

    /**
     * @Given the search button is clicked
     */
    public function theSearchButtonIsClicked()
    {
        $this->artistSearchTextField->setValue('The Beach Boys');

        sleep(1.5);

        $this->suggestions = $this->page->find("css", "#ui-id-1");
        $firstsuggestion = $this->suggestions->find('css', 'li');
        $firstsuggestion->click();

        $this->searchButton->click();

        sleep(2);
    }

    /**
     * @Then we should be navigated to the Word Cloud Page for the artist
     */
    public function weShouldBeNavigatedToTheWordCloudPageForTheArtist()
    {
        assertNotEquals(null, $this->page->find("css", "#wordCloudPage"));
    }
}