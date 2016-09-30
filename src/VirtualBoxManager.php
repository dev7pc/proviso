<?php //declare(strict_types = 1);

namespace SevenPercent\Proviso;

class VirtualBoxManager {

	/*public static function executeCommand(array $parameters = [], array $options = []) {
		$cmd = escapeshellcmd('VBoxManage' . implode(array_merge(array_map(function ($parameter) {
			return ' ' . escapeshellarg($parameter);
		}, $parameters), array_map(function (string $key, $value): string {
			return ' ' . escapeshellarg("--$key") . ($value === NULL ? '' : '=' . escapeshellarg($value));
		}, array_keys($options), $options)))) . ' 2>&1';
		echo $cmd, PHP_EOL;
	}*/

	public static function executeCommand(array $parameters = [], array $options = [])/*: array*/ {
		exec(escapeshellcmd('VBoxManage' . implode(array_merge(array_map(function ($parameter) {
			return ' ' . escapeshellarg($parameter);
		}, $parameters), array_map(function (/*string */$key, $value)/*: string*/ {
			return ' ' . escapeshellarg("--$key") . ($value === NULL ? '' : '=' . escapeshellarg($value));
		}, array_keys($options), $options)))) . ' 2>&1', $output, $exitCode);
		if ($exitCode !== 0) {
			throw new Exception('VBoxManage command failed: ' . json_encode([
				'cmd' => $cmd,
				'output' => $output,
			]));
		} else {
			return $output;
		}
	}
}
