<?php

require_once "vendor/autoload.php";

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
	}

	/**
	* @Given there is an artist search bar
	*/
	public function thereIsAnArtistSearchBar()
	{
		//$this->session->visit('http://localhost:80/LyricsCloud/');
		//$this->page = $this->session->getPage();
		
		//$this->artistSearchBar = $this->page->find("css", "#artistSearchBar");
	}

	/**
	* @Then the artist search bar should be empty
	*/
	public function theArtistSearchBarShouldBeEmpty()
	{
		//$artistSearchTextField = $this->artistSearchBar->find("#artistSearchTextField");
		
		//echo $artistSearchTextField->getAttribute("value");
	}
}
