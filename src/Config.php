<?php //declare(strict_types = 1);

namespace SevenPercent\Proviso;

use Exception;

class Config {

	const DIR_PRIVATE = '.proviso';
	const DIR_KICKSTART = 'kickstart';
	const FILENAME_CONFIG = 'proviso.json';
	const FILENAME_KICKSTART_CONFIG = 'ks.cfg';
	const FILENAME_KICKSTART_IMAGE = 'kickstart.iso';

	private $_dir;
	private $_name;

	public function __construct(/*string */$dir) {
		$this->_dir = realpath($dir) . '/';
		if (!is_file($filename = $this->_dir . self::FILENAME_CONFIG)) {
			throw new Exception('Error: ' . self::FILENAME_CONFIG . ' not found in this directory');
		} elseif (!is_readable($filename)) {
			throw new Exception('Error: ' . self::FILENAME_CONFIG . ' not readable');
		} elseif (($json_config = file_get_contents($filename)) === FALSE) {
			throw new Exception('Error reading from ' . self::FILENAME_CONFIG);
		} elseif (($config = json_decode($json_config, TRUE)) === NULL) {
			throw new Exception('Error decoding ' . self::FILENAME_CONFIG);
		} else {
			$this->_name = $config['virtualbox']['vm-name'];
		}
	}

	public function getName()/*: string*/ {
		return $this->_name;
	}

	public function getBasePath()/*: string*/ {
		return $this->_dir . self::DIR_PRIVATE . '/';
	}

	public function getSSDImageFilename()/*: string*/ {
		return $this->getBasePath() . $this->getName() . '.vdi';
	}

	public function getKickstartPath()/*: string*/ {
		return $this->getBasePath() . self::DIR_KICKSTART . '/';
	}

	public function getKickstartConfigFilename()/*: string*/ {
		return $this->getKickstartPath() . self::FILENAME_KICKSTART_CONFIG;
	}

	public function getKickstartImageFilename()/*: string*/ {
		return $this->getBasePath() . self::FILENAME_KICKSTART_IMAGE;
	}
}
