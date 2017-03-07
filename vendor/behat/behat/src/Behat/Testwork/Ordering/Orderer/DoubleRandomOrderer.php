<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering\Orderer;

use Behat\Testwork\Specification\SpecificationArrayIterator;
use Behat\Gherkin\Node\FeatureNode;

/**
 * Prioritises Suites and Features and Scenarios into random order
 */
final class DoubleRandomOrderer implements Orderer
{
    /**
     * @param SpecificationIterator[] $scenarioIterators
     * @return SpecificationIterator[]
     */
    public function order(array $scenarioIterators)
    {
        $orderedFeatures = $this->orderFeatures($scenarioIterators);
        shuffle($orderedFeatures);

        return $orderedFeatures;
    }

    /**
     * @param array $scenarioIterators
     * @return array
     */
    private function orderFeatures(array $scenarioIterators)
    {
        $orderedSuites = array();

        foreach ($scenarioIterators as $scenarioIterator) {

		$orderedSpecifications = iterator_to_array($scenarioIterator);
		shuffle($orderedSpecifications);

		$shuffledFeatures = array();

		// Shuffle the scenarios within each feature:
		foreach ($orderedSpecifications as $feature) {
	
			$scenarios = $feature->getScenarios();
			shuffle($scenarios);
	
			$shuffledFeatures[] = new FeatureNode(
				$feature->getTitle(),
				$feature->getDescription(),
				$feature->getTags(),
				$feature->getBackground(),
				$scenarios,
				$feature->getKeyword(),
				$feature->getLanguage(),
				$feature->getFile(),
				$feature->getLine()
			);
		}

		$orderedSuites[] = new SpecificationArrayIterator(
			$scenarioIterator->getSuite(),
			$shuffledFeatures
		);

        }

        return $orderedSuites;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'double-random';
    }
}
