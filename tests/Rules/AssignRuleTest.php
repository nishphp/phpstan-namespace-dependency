<?php declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Nish\PHPStan\NsDepends\DependencyChecker;

/**
 * @extends RuleTestCase<AssignRule>
 */
class AssignRuleTest extends RuleTestCase
{
    /** @override */
    protected function getRule(): Rule
    {
        return new AssignRule(new DependencyChecker(
            [
                'Model' => ['Controller'],
                'View' => ['Controller'],
            ],
            [
                'Model' => ['Util'],
            ]
        ));
    }

    public function testFrom(): void
    {
        $this->analyse([__DIR__ . '/data/assign.php'], [
            [
                'Cannot allow depends Model\DateModel to DateTimeInterface.',
                9
            ],
        ]);
    }
}
