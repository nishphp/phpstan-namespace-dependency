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
                14
            ],
            [
                'Cannot allow depends in Model\IndexModel::setObj() parameter $obj(stdClass).',
                16
            ],
            [
                'Cannot allow depends in Model\IndexModel::getObj() return type stdClass.',
                20
            ],
            [
                'Cannot allow depends Model to stdClass::__construct().',
                22
            ],
            [
                'Cannot allow depends Model to Presenter\Form::__construct().',
                32
            ],
            [
                'Cannot allow depends Model to Presenter\Form::build().',
                37
            ],
            [
                'Cannot allow depends Model to Presenter\Form::$a.',
                42
            ],
            [
                'Cannot allow depends Model to Presenter\Form::B.',
                47
            ],
            [
                'Cannot allow depends in Util\ModelUtil::getModel() return type Model\IndexModel.',
                76
            ],
            [
                'Cannot allow depends Util to Model\IndexModel::__construct().',
                78
            ],
            [
                'Cannot allow depends in Util\Container::getView() return type View\IndexView.',
                90
            ],
            [
                'Cannot allow depends Util to View\IndexView::__construct().',
                92
            ],
        ]);
    }
}
