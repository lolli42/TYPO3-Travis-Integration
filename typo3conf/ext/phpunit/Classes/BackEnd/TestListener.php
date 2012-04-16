<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2004-2011 Robert Lemke <robert@typo3.org>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class renders the output of the single tests in the phpunit BE module.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Robert Lemke <robert@typo3.org>
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_PhpUnit_BackEnd_TestListener implements PHPUnit_Framework_TestListener {
	/**
	 * the total number of tests to run
	 *
	 * @var integer
	 */
	protected $totalNumberOfTests = 0;
	/**
	 * the total number of data provider tests detected
	 *
	 * @var integer
	 */
	protected $totalNumberOfDetectedDataProviderTests = 0;

	/**
	 * the number of the current test (zero-based)
	 *
	 * @var integer
	 */
	protected $currentTestNumber = 0;

	/**
	 * the number of the current data provider within a test (zero-based)
	 *
	 * @var integer
	 */
	protected $currentDataProviderNumber = 0;

	/**
	 * the name of the current test case
	 *
	 * @var string
	 */
	protected $currentTestCaseName = '';

	/**
	 * the name of the current test
	 *
	 * @var string
	 */
	protected $previousTestName = '';

	/**
	 * used memory (in bytes) before the first test is run
	 *
	 * @var integer
	 */
	protected $memoryUsageStartOfTest = 0;

	/**
	 * used memory (in bytes) after the last test has been run
	 *
	 * @var integer
	 */
	protected $memoryUsageEndOfTest = 0;

	/**
	 * the number of bytes that have been in use after running the last test
	 * (relative to the used bytes before starting the first test)
	 *
	 * @var integer
	 */
	public $totalLeakedMemory = 0;

	/**
	 * the number of executed assertions
	 *
	 * @var integer
	 */
	protected $testAssertions = 0;

	/**
	 * whether to use the "testdox" format to display test case and test names
	 *
	 * @var boolean
	 */
	protected $useHumanReadableTextFormat = FALSE;

	/**
	 * whether to display the used memory and time of each test
	 *
	 * @var boolean
	 */
	protected $enableShowMemoryAndTime = FALSE;

	/**
	 * a name prettifier for creating readable test and test case names
	 *
	 * @var PHPUnit_Util_TestDox_NamePrettifier
	 */
	protected $namePrettifier = NULL;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->namePrettifier = new PHPUnit_Util_TestDox_NamePrettifier();
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->namePrettifier);
	}

	/**
	 * Sets the total number of tests to run (used for displaying the progress
	 * bar).
	 *
	 * @param integer $totalNumberOfTests
	 *        the total number of tests to run, must be >= 0
	 *
	 * @return void
	 */
	public function setTotalNumberOfTests($totalNumberOfTests) {
		$this->totalNumberOfTests = $totalNumberOfTests;
	}

	/**
	 * Gets the total number of tests that were detected to come from data providers.
	 *
	 * Note: As these are detected based on similar names, the first test from a data
	 * provider cannot be detected reliably; the number will always be too low.
	 *
	 * @return integer the total number of data-provider related tests detected so far, will be >= 0
	 */
	public function getTotalNumberOfDetectedDataProviderTests() {
		return $this->totalNumberOfDetectedDataProviderTests;
	}

	/**
	 * Enables the option to show the memory leaks and time usage of the single tests.
	 *
	 * @return void
	 */
	public function enableShowMenoryAndTime() {
		$this->enableShowMemoryAndTime = TRUE;
	}

	/**
	 * Enables the option to use human-readable test and test case names.
	 *
	 * @return void
	 */
	public function useHumanReadableTextFormat() {
		$this->useHumanReadableTextFormat = TRUE;
	}

	/**
	 * An error has occurred, i.e. an exception has been thrown when running $test.
	 *
	 * @param PHPUnit_Framework_Test $test the test that had an error
	 * @param Exception $e the exception that has caused the error
	 * @param float $time ?
	 *
	 * @return void
	 */
	public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {
		$fileName = str_replace(PATH_site, '', $e->getFile());
		$lineNumber = $e->getLine();

		echo '<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("hadError");/*]]>*/</script>
			<script type="text/javascript">/*<![CDATA[*/setClass("testcaseNum-' . $this->currentTestNumber . '_' .
				$this->currentDataProviderNumber . '","testcaseError");/*]]>*/</script>
			<strong><span class="hadError">!</span> Error</strong> in test case <em>' . $test->getName() . '</em>
			<br />File: <em>' . $fileName . '</em>
			<br />Line: <em>' . $lineNumber . '</em>' .
			'<div class="message">' . nl2br(htmlspecialchars($e->getMessage())) . '</div>';
		$this->flushOutputBuffer();
	}

	/**
	 * A test has failed.
	 *
	 * @param PHPUnit_Framework_Test $test the test that has failed
	 * @param PHPUnit_Framework_AssertionFailedError $e the failed assertion
	 * @param float $time ?
	 *
	 * @return void
	 */
	public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
		$testCaseTraceArr = $this->getFirstNonPhpUnitTrace($e->getTrace());
		$fileName = str_replace(PATH_site, '', $testCaseTraceArr['file']);

		$this->output(
			'<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("hadFailure");/*]]>*/</script>' .
				'<script type="text/javascript">/*<![CDATA[*/setClass("testcaseNum-' . $this->currentTestNumber . '_' .
				$this->currentDataProviderNumber . '","testcaseFailure");/*]]>*/</script>' .
				'<strong><span class="hadFailure">!</span> Failure</strong> in test case <em>' . $test->getName() . '</em>' .
				'<br />File: <em>' . $fileName . '</em>' .
				'<br />Line: <em>' . $testCaseTraceArr['line'] . '</em>'
		);

		if (method_exists($e, 'getDescription')) {
			$message = $e->getDescription();
		} else {
			$message = $e->getMessage();
		}
		$this->output('<div class="message">' . nl2br(htmlspecialchars($message)) . '</div>');

		if ($e instanceof PHPUnit_Framework_ExpectationFailedException) {
			$comparisonFailure = $e->getComparisonFailure();
			if ($comparisonFailure instanceof PHPUnit_Framework_ComparisonFailure_String) {
				$expected = $comparisonFailure->getExpected();
				$actual = $comparisonFailure->getActual();

				$diff = t3lib_div::makeInstance('t3lib_diff');
				$this->output('<code>'. $diff->makeDiffDisplay($actual, $expected) . '</code>');
			}
		}
	}

	/**
	 * Returns the first trace information which is not caused by the PHPUnit file
	 * "Framework/Assert.php".
	 *
	 * @param array $traceData the trace data
	 *
	 * @return array trace information
	 */
	protected function getFirstNonPhpUnitTrace(array $traceData) {
		$testCaseTraceData = array();

		foreach ($traceData as $singleTraceArr) {
			if (!stristr($singleTraceArr['file'], 'Framework' . DIRECTORY_SEPARATOR . 'Assert.php')) {
				$testCaseTraceData = $singleTraceArr;
				break;
			}
		}

		return $testCaseTraceData;
	}

	/**
	 * A test has been marked as incomplete, i.e. as not implemented yet.
	 *
	 * @param PHPUnit_Framework_Test $test the test that has been marked as incomplete
	 * @param Exception $e an exception about the incomplete test (?)
	 * @param float $time ?
	 *
	 * @return void
	 */
	public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
		$this->output(
			'<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("hadNotImplemented");/*]]>*/</script>' .
				'<script type="text/javascript">/*<![CDATA[*/setClass("testcaseNum-' . $this->currentTestNumber . '_' .
				$this->currentDataProviderNumber . '","testcaseNotImplemented");/*]]>*/</script>' .
				'<span class="inCompleteTest">!</span> <strong>Incomplete test</strong> <em>' . $test->getName() .
				'</em> in file <em>' . $e->getFile() . '</em> line <em>' . $e->getLine() . '</em>:<br />' .
				$e->getMessage() . '<br />'
		);
	}

	/**
	 * A test has been marked as skipped.
	 *
	 * @param PHPUnit_Framework_Test $test the test that has been marked as skipped
	 * @param Exception $e an exception about the skipped test (?)
	 * @param float $time ?
	 *
	 * @return void
	 */
	public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
		$this->output(
			'<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("hadSkipped");/*]]>*/</script>' .
				'<script type="text/javascript">/*<![CDATA[*/setClass("testcaseNum-' . $this->currentTestNumber . '_' .
				$this->currentDataProviderNumber . '","testcaseSkipped");/*]]>*/</script>' .
				'<span class="inSkippedTest">!</span> <strong>Skipped test</strong> <em>' . $test->getName() . '</em> in file <em>' .
				$e->getFile() . '</em> line <em>' . $e->getLine() . '</em>:<br />' .
				$e->getMessage() . '<br />'
		);
	}

	/**
	 * A test suite/case has started.
	 *
	 * Note: This function also gets called when a test that uses a data provider
	 * has started.
	 *
	 * @param PHPUnit_Framework_TestSuite $suite the test suite/case that has started
	 *
	 * @return void
	 */
	public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
		$this->setTestSuiteName($suite->getName());
		if (($suite instanceof PHPUnit_Framework_TestSuite_DataProvider)
			|| ($suite->getName() === 'tx_phpunit_basetestsuite')
		) {
			return;
		}

		$this->output(
			'<h2 class="testSuiteName">Testsuite: ' . $this->prettifyTestClass($suite->getName()) . '</h2>' .
			'<script type="text/javascript">/*<![CDATA[*/setProgressBarClass("wasSuccessful");/*]]>*/</script>'
		);
	}

	/**
	 * Sets the name of the test suite that is used for creating the re-run
	 * link.
	 *
	 * @param string $name the name of the test suite, must not be empty
	 *
	 * @return void
	 */
	public function setTestSuiteName($name) {
		$this->currentTestCaseName = $name;
	}

	/**
	 * A test suite/case has ended.
	 *
	 * Note: This function also gets called when a test that uses a data provider
	 * has ended.
	 *
	 * @param PHPUnit_Framework_TestSuite $suite the test suite/case that has ended
	 *
	 * @return void
	 */
	public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {
	}

	/**
	 * A test has started.
	 *
	 * @param PHPUnit_Framework_Test $test the test that has started
	 *
	 * @return void
	 */
	public function startTest(PHPUnit_Framework_Test $test) {
		// A single test has to take less than this or else PHP will time out.
		$this->setTimeLimit(30);

		$this->output(
			'<div id="testcaseNum-' . $this->currentTestNumber . '_' . $this->currentDataProviderNumber .
				'" class="testcaseOutput testcaseSuccess">' .
				'<h3>' . $this->createReRunLink($test) . $this->prettifyTestMethod($test->getName()) . '</h3><div>'
		);
		$this->memoryUsageStartOfTest = memory_get_usage();
	}

	/**
	 * Sets the PHP execution time limit.
	 *
	 * @param integer $limit the PHP execution time limit in seconds, must be >= 0
	 *
	 * @return void
	 */
	protected function setTimeLimit($limit) {
		set_time_limit($limit);
	}

	/**
	 * A test has ended.
	 *
	 * @param PHPUnit_Framework_Test $test the test that has ended
	 * @param float $time ?
	 *
	 * @return void
	 */
	public function endTest(PHPUnit_Framework_Test $test, $time) {
		$this->memoryUsageEndOfTest = memory_get_usage();

		if ($test instanceof PHPUnit_Framework_TestCase) {
			// Tests with the same name are a sign of data provider usage.
			$testNameParts = explode(' ', $test->getName());
			$testName = get_class($test) . ':' . $testNameParts[0];
			if ($testName !== $this->previousTestName) {
				$this->currentDataProviderNumber = 0;
				$this->currentTestNumber++;
				$this->previousTestName = $testName;
			} else {
				$this->currentDataProviderNumber++;
				$this->totalNumberOfDetectedDataProviderTests++;
			}
		}

		if (($this->totalNumberOfTests - $this->totalNumberOfDetectedDataProviderTests) > 0) {
			$percentDone = intval(($this->currentTestNumber / ($this->totalNumberOfTests - $this->totalNumberOfDetectedDataProviderTests)) * 100);
		} else {
			$percentDone = 0;
		}
		$leakedMemory = ($this->memoryUsageEndOfTest - $this->memoryUsageStartOfTest);
		$this->totalLeakedMemory += $leakedMemory;

		if ($test instanceof PHPUnit_Framework_TestCase) {
			$this->testAssertions += $test->getNumAssertions();
		}

		$output = '</div>';
		if ($this->enableShowMemoryAndTime === TRUE) {
			$output .= '<span class="memory-leak small-font"><strong>Memory leak:</strong> ' .
				t3lib_div::formatSize($leakedMemory) . 'B </span>' .
				'<span class="time-usages small-font"><strong>Time:</strong> ' . sprintf('%.4f', $time) .
				' sec.</span><br />';
		}
		$output .= '</div>' .
			'<script type="text/javascript">/*<![CDATA[*/document.getElementById("progress-bar").style.width = "' .
			$percentDone . '%";/*]]>*/</script>' .
			'<script type="text/javascript">/*<![CDATA[*/document.getElementById("transparent-bar").style.width = "' .
			(100 - $percentDone) . '%";/*]]>*/</script>';

		$this->output($output);
		$this->flushOutputBuffer();
	}

	/**
	 * Creates the link (including an icon) to re-run the given single test.
	 *
	 * @param PHPUnit_Framework_TestSuite $test
	 *        the test for which to create the re-run link
	 *
	 * @return string the link to re-run the given test, will not be empty
	 */
	protected function createReRunLink(PHPUnit_Framework_TestCase $test) {
		$iconImageTag = '<img class="runner" src="' .
			t3lib_extMgm::extRelPath('phpunit') . 'Resources/Public/Icons/Runner.gif" alt="" />';
		return '<a href="' . $this->createReRunUrl($test) . '" title="Run this test only">' . $iconImageTag . '</a> ';
	}

	/**
	 * Creates the URL to re-run the given test.
	 *
	 * @param PHPUnit_Framework_TestSuite $test
	 *        the test for which to create the re-run URL
	 *
	 * @return string the htmlspecialchared URL to re-run the given test, will not be empty
	 */
	protected function createReRunUrl(PHPUnit_Framework_TestCase $test) {
		$options = array(
			'M=tools_txphpunitbeM1',
			'command=runsingletest',
			'testCaseFile=' . $this->getTestCaseName(),
			'testname=' . $this->createTestId($test),
		);

		return htmlspecialchars('mod.php?' . implode('&', $options));
	}

	/**
	 * Creates a unique string ID for $test that can be used in URLs.
	 *
	 * @param PHPUnit_Framework_TestCase $test a test for which to create an ID
	 *
	 * @return string a unique ID for $test, not htmlspecialchared or URL-encoded yet
	 */
	protected function createTestId(PHPUnit_Framework_TestCase $test) {
		$testNameParts = explode(' ', $test->getName());

		// This is quite a hack.
		// @see http://forge.typo3.org/issues/11735
		if (strpos($this->currentTestCaseName, '::') !== FALSE) {
			$result = $testNameParts[0] . '(' . $this->getTestCaseName() . ')';
		} else {
			$result = $this->getTestCaseName() . '::' . $testNameParts[0];
		}

		return $result;
	}

	/**
	 * Gets the current test case name.
	 *
	 * @return string the current test case name, will not be empty
	 */
	protected function getTestCaseName() {
		$testCaseNameParts = explode('::', $this->currentTestCaseName);

		return $testCaseNameParts[0];
	}

	/**
	 * Prettifies the name of a test method.
	 *
	 * This method will return $testName unchanged if human-readable names
	 * are disabled.
	 *
	 * @param string $testName a camel-case test name, must not be empty
	 *
	 * @return string the prettified test name, will not be empty
	 */
	protected function prettifyTestMethod($testName) {
		if (!$this->useHumanReadableTextFormat) {
			return $testName;
		}

		// this is required because the "setPrefix" work not very well with the prefix "test_"
		$testNameWithoutSuffix = preg_replace('/^test_/i', '', $testName);

		$this->namePrettifier->setPrefix('test');
		$this->namePrettifier->setSuffix(NULL);

		return $this->namePrettifier->prettifyTestMethod($testNameWithoutSuffix);
	}

	/**
	 * Prettifies the name of a test class.
	 *
	 * This method will return $testClass unchanged if human-readable names
	 * are disabled.
	 *
	 * @param string $testClassName a camel-case test class name, must not be empty
	 *
	 * @return string the prettified test class name, will not be empty
	 */
	protected function prettifyTestClass($testClassName) {
		if (!$this->useHumanReadableTextFormat) {
			return $testClassName;
		}

		$testClassNameWithoutPrefixOrSuffix = preg_replace('/(tx_|Tx_)?(.+)(Test|_testcase)$/', '\2', $testClassName);
		$testClassNameWithoutUnderScores = str_replace('_', ' ', $testClassNameWithoutPrefixOrSuffix);

		$this->namePrettifier->setPrefix(NULL);
		$this->namePrettifier->setSuffix(NULL);

		return $this->namePrettifier->prettifyTestClass($testClassNameWithoutUnderScores);
	}

	/**
	 * Retrieves the collected amount of processed assertions.
	 *
	 * @return integer the number of executed assertions, will be >= 0
	 */
	public function assertionCount() {
		return $this->testAssertions;
	}

	/**
	 * Echoes $output.
	 *
	 * @param string $output a string to echo, may also be empty
	 *
	 * @return void
	 */
	protected function output($output) {
		echo($output);
	}

	/**
	 * Flushes the output buffer.
	 *
	 * @return void
	 */
	protected function flushOutputBuffer() {
		flush();
	}
}
?>