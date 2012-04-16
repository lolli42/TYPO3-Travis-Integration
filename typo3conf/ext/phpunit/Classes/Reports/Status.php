<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2011 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class provides a status report for the "Reports" BE module.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Reports_Status implements tx_reports_StatusProvider {
	/**
	 * @var string
	 */
	const MEMORY_REQUIRED = '128M';
	/**
	 * @var string
	 */
	const MEMORY_RECOMMENDED = '256M';

	/**
	 * Returns the status of this extension.
	 *
	 * @return array<tx_reports_reports_status_Status>
	 *         status reports for this extension
	 */
	public function getStatus() {
		return array(
			$this->getReflectionStatus(),
			$this->getEacceleratorStatus(),
			$this->getXdebugStatus(),
			$this->getMemoryLimitStatus(),
			$this->getIncludePathStatus(),
			$this->getExcludedExtensionsStatus(),
		);
	}

	/**
	 * Translates a localized string.
	 *
	 * @param string $subkey
	 *        the part of the key to translate (without the
	 *        "LLL:EXT:phpunit/Resources/Private/Language/locallang_report.xml:" prefix)
	 *
	 * @return string the localized string for $subkey, might be empty
	 */
	protected function translate($subkey) {
		return $GLOBALS['LANG']->sL(
			'LLL:EXT:phpunit/Resources/Private/Language/locallang_report.xml:' . $subkey
		);
	}

	/**
	 * Creates a status concerning whether PHP reflection works correctly.
	 *
	 * @return tx_reports_reports_status_Status
	 *         a status indicating whether PHP reflection works correctly
	 */
	protected function getReflectionStatus() {
		$heading = $this->translate('status_phpComments');

		$method = new ReflectionMethod('tx_phpunit_Reports_Status', 'getStatus');
		if (strlen($method->getDocComment()) > 0) {
			$status = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$heading,
				$this->translate('status_phpComments_present_short'),
				$this->translate('status_phpComments_present_verbose'),
				tx_reports_reports_status_Status::OK
			);
		} else {
			$status = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$heading,
				$this->translate('status_phpComments_stripped_short'),
				$this->translate('status_phpComments_stripped_verbose'),
				tx_reports_reports_status_Status::ERROR
			);
		}

		return $status;
	}

	/**
	 * Creates a status concerning eAccelerator not crashing phpunit.
	 *
	 * @return tx_reports_reports_status_Status
	 *         a status concerning eAccelerator not crashing phpunit
	 */
	protected function getEacceleratorStatus() {
		$heading = $this->translate('status_eAccelerator');

		if (!extension_loaded('eaccelerator')) {
			$status = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$heading,
				$this->translate('status_eAccelerator_notInstalled_short'),
				'',
				tx_reports_reports_status_Status::OK
			);
		} else {
			$version = phpversion('eaccelerator');

			if (version_compare($version, '0.9.5.2', '<')) {
				$verboseMessage = sprintf(
					$this->translate('status_eAccelerator_installedOld_verbose'),
					$version
				);

				$status = t3lib_div::makeInstance(
					'tx_reports_reports_status_Status',
					$heading,
					$this->translate('status_eAccelerator_installedOld_short'),
					$verboseMessage,
					tx_reports_reports_status_Status::ERROR
				);
			} else {
				$verboseMessage = sprintf(
					$this->translate('status_eAccelerator_installedNew_verbose'),
					$version
				);

				$status = t3lib_div::makeInstance(
					'tx_reports_reports_status_Status',
					$heading,
					$this->translate('status_eAccelerator_installedNew_short'),
					$verboseMessage,
					tx_reports_reports_status_Status::OK
				);
			}
		}

		return $status;
	}

	/**
	 * Creates a status concerning whether Xdebug is loaded.
	 *
	 * @return tx_reports_reports_status_Status
	 *         a status concerning whether Xdebug is loaded
	 */
	protected function getXdebugStatus() {
		if (extension_loaded('xdebug')) {
			$messageKey = 'status_loaded';
		} else {
			$messageKey = 'status_notLoaded';
		}

		return t3lib_div::makeInstance(
			'tx_reports_reports_status_Status',
			$this->translate('status_xdebug'),
			$this->translate($messageKey),
			'',
			tx_reports_reports_status_Status::NOTICE
		);
	}

	/**
	 * Creates a status concerning the PHP memory limit.
	 *
	 * @return tx_reports_reports_status_Status
	 *         a status indicating whether the PHP memory limit is high enogh
	 */
	protected function getMemoryLimitStatus() {
		$memoryLimitFromConfiguration = ini_get('memory_limit');
		$memoryLimitInBytes = t3lib_div::getBytesFromSizeMeasurement($memoryLimitFromConfiguration);
		$requiredMemoryLimitInBytes = t3lib_div::getBytesFromSizeMeasurement(self::MEMORY_REQUIRED);
		$recommendedMemoryLimitInBytes = t3lib_div::getBytesFromSizeMeasurement(self::MEMORY_RECOMMENDED);

		$heading = $this->translate('status_memoryLimit');
		$message = sprintf(
			$this->translate('status_memoryLimit_tooLittle'),
			self::MEMORY_REQUIRED, self::MEMORY_RECOMMENDED
		);

		if ($memoryLimitInBytes < $requiredMemoryLimitInBytes) {
			$status = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$heading,
				$memoryLimitFromConfiguration,
				$message,
				tx_reports_reports_status_Status::ERROR
			);
		} elseif ($memoryLimitInBytes < $recommendedMemoryLimitInBytes) {
			$status = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$heading,
				$memoryLimitFromConfiguration,
				$message,
				tx_reports_reports_status_Status::WARNING
			);
		} else {
			$status = t3lib_div::makeInstance(
				'tx_reports_reports_status_Status',
				$heading,
				$memoryLimitFromConfiguration,
				'',
				tx_reports_reports_status_Status::OK
			);
		}

		return $status;
	}

	/**
	 * Creates a status about the PHP include path.
	 *
	 * @return tx_reports_reports_status_Status
	 *         a status about the PHP include path
	 */
	protected function getIncludePathStatus() {
		$paths = explode(PATH_SEPARATOR, get_include_path());

		$escapedPaths = array();
		foreach ($paths as $path) {
			$escapedPaths[] = htmlspecialchars($path);
		}

		return t3lib_div::makeInstance(
			'tx_reports_reports_status_Status',
			$this->translate('status_includePath'),
			'',
			'<code>' . implode('<br />', $escapedPaths) . '</code>',
			tx_reports_reports_status_Status::NOTICE
		);
	}

	/**
	 * Creates a status about the extensions that are excluded from unit testing.
	 *
	 * @return tx_reports_reports_status_Status
	 *         a status about the excluded extensions
	 */
	protected function getExcludedExtensionsStatus() {
		$extensionKeys = t3lib_div::trimExplode(
			',', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['excludeextensions']
		);

		return t3lib_div::makeInstance(
			'tx_reports_reports_status_Status',
			$this->translate('status_excludedExtensions'),
			'',
			implode('<br />', $extensionKeys),
			tx_reports_reports_status_Status::NOTICE
		);
	}
}
?>