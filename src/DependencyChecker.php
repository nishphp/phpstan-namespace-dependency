<?php

declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends;

class DependencyChecker
{

	/** @var array<string, array<int,string>> $from */
	private array $from;

	/** @var array<string, array<int,string>> $to */
	private array $to;

	/**
	 * @param array<string, array<int, string>> $from
	 * @param array<string, array<int, string>> $to
	 */
	public function __construct(array $from, array $to)
	{
		$this->from = $from;
		$this->to = $to;
	}

	private static function startsWith(string $haystack, string $needle): bool
	{
		return strncmp($haystack, $needle, strlen($needle)) === 0;
	}

	public function accept(string $from, string $to): bool
	{
		foreach ($this->from as $toPrefix => $fromPrefixes) {
			if (self::startsWith($to, $toPrefix)) {
				$fromPrefixes[] = $toPrefix;
				foreach ($fromPrefixes as $fromPrefix) {
					if (self::startsWith($from, $fromPrefix)) {
						return true;
					}
				}

				return false;
			}
		}

		foreach ($this->to as $fromPrefix => $toPrefixes) {
			if (self::startsWith($from, $fromPrefix)) {
				$toPrefixes[] = $fromPrefix;
				foreach ($toPrefixes as $toPrefix) {
					if (self::startsWith($to, $toPrefix)) {
						return true;
					}
				}

				return false;
			}
		}

		// not exists config, default is allow
		return true;
	}

}
