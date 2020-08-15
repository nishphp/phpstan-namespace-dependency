<?php declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Nish\PHPStan\NsDepends\DependencyChecker;

/**
 * @extends RuleTestCase<ClassDefinitionRule>
 */
class ClassDefinitionRuleTest extends RuleTestCase
{
    /** @override */
    protected function getRule(): Rule
    {
        return new ClassDefinitionRule(new DependencyChecker(
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
        $this->analyse([__DIR__ . '/data/classdef.php'], [
            [
                'Cannot allow depends Model\FooModel1 implements OtherModel\OtherModelInterface.',
                7
            ],
            [
                'Cannot allow depends Model\FooModel2 extends OtherModel\OtherModelAbstract.',
                8
            ],
            [
                'Cannot allow depends Model\FooModel implements OtherModel\OtherModelInterface.',
                18
            ],
            [
                'Cannot allow depends Model\FooModel extends OtherModel\OtherModelAbstract.',
                18
            ],
        ]);
    }
}
