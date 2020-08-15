<?php declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Nish\PHPStan\NsDepends\DependencyChecker;

/**
 * @extends RuleTestCase<MethodDefinitionRule>
 */
class MethodDefinitionRuleTest extends RuleTestCase
{
    /** @override */
    protected function getRule(): Rule
    {
        return new MethodDefinitionRule(new DependencyChecker(
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
                'Cannot allow depends in Model\IndexModel::setObj() parameter $obj(stdClass).',
                16
            ],
            [
                'Cannot allow depends in Model\IndexModel::getObj() return type stdClass.',
                20
            ],
            [
                'Cannot allow depends in Util\ModelUtil::getModel() return type Model\IndexModel.',
                76
            ],
            [
                'Cannot allow depends in Util\Container::getView() return type View\IndexView.',
                90
            ],
        ]);
    }
}
