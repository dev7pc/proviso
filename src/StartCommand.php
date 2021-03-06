<?php //declare(strict_types = 1);

namespace SevenPercent\Proviso;

use Exception;
use Symfony\Component\Console;

class StartCommand extends Console\Command\Command {

	protected function configure() {
		$this->setName('start');
	}

	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output) {
		$config = new Config(getcwd());
		$output = VirtualBoxManager::executeCommand([
			'showvminfo',
			$config->getName(),
		], [
			'machinereadable' => NULL,
		]);
		$found = FALSE;
		$state = NULL;
		foreach ($output as $line) {
			if (preg_match('/^VMState="(.*)"/', $line, $matches) === 1) {
				$found = TRUE;
				$state = $matches[1];
				break;
			}
		}
		if (!$found) {
			throw new Exception('showvminfo produced unreadable output');
		} elseif ($state === 'running') {
			throw new Exception('VM already running');
		} else {
			VirtualBoxManager::executeCommand([
				'startvm',
				$config->getName(),
			], [
				'type' => 'gui',
			]);
		}
	}
}
