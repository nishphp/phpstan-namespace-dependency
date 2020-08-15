<?php declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends;

use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Rules\FunctionReturnTypeCheck;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NamespaceDependencyRule>
 */
class NamespaceDependencyRuleTest extends RuleTestCase
{
    /** @override */
    protected function getRule(): Rule
    {
        return new NamespaceDependencyRule(
            [
                'Model' => ['Controller'],
                'View' => ['Controller'],
            ],
            [
                'Model' => ['Util'],
            ],
            new RuleLevelHelper($this->createBroker(), true, false, true)
        );
    }

    public function testFrom(): void
    {
        $this->analyse([__DIR__ . '/data.php'], [
            [
                'Cannot allow depends in Model\IndexModel::$obj(stdClass).',
                11
            ],
            [
                'Cannot allow depends in Model\IndexModel::setObj() parameter $obj(stdClass).',
                13
            ],
            [
                'Cannot allow depends in Model\IndexModel::getObj() return type stdClass.',
                17
            ],
            [
                'Cannot allow depends in Model\IndexModel to Presenter\Form.',
                29
            ],
            [
                'Cannot allow depends in Model\IndexModel to Presenter\Form.',
                34
            ],
            [
                'Cannot allow depends in Util\ModelUtil::getModel() return type Model\IndexModel.',
                61
            ],
            [
                'Cannot allow depends in Util\Container::getView() return type View\IndexView.',
                75
            ],
        ]);
    }
}
