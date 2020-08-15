<?php declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Nish\PHPStan\NsDepends\DependencyChecker;

/**
 * @extends RuleTestCase<PropertyRule>
 */
class PropertyRuleTest extends RuleTestCase
{
    /** @override */
    protected function getRule(): Rule
    {
        return new PropertyRule(new DependencyChecker(
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
                'Cannot allow depends in Model\IndexModel::$obj(stdClass).',
                14
            ],
        ]);
    }
}
