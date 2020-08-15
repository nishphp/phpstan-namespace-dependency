<?php

declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends;

class DependencyChecker
{
	/** @var array<string, array<int,string>> $from */
	private $from;
	/** @var array<string, array<int,string>> $to */
	private $to;

	/**
	 * @param array<string, array<int, string>> $from
	 * @param array<string, array<int, string>> $to
	 */
    public function __construct(array $from, array $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    private static function starts_with(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    public function accept(string $from, string $to): bool
    {
        foreach ($this->from as $toPrefix => $fromPrefixes){
            if (self::starts_with($to, $toPrefix)){
                foreach ($fromPrefixes as $fromPrefix){
                    if (self::starts_with($from, $fromPrefix)){
                        return true;
                    }
                }

                return false;
            }
        }

        foreach ($this->to as $fromPrefix => $toPrefixes){
            if (self::starts_with($from, $fromPrefix)){
                foreach ($toPrefixes as $toPrefix){
                    if (self::starts_with($to, $toPrefix)){
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
