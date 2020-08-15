<?php declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Nish\PHPStan\NsDepends\DependencyChecker;

/**
 * @extends RuleTestCase<StaticCallRule>
 */
class StaticCallRuleTest extends RuleTestCase
{
    /** @override */
    protected function getRule(): Rule
    {
        return new StaticCallRule(new DependencyChecker(
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
        $this->analyse([__DIR__ . '/data/data.php'], [
            [
                'Cannot allow depends Model\IndexModel to Presenter\Form::build().',
                37
            ],
        ]);
    }
}
