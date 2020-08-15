<?php declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Nish\PHPStan\NsDepends\DependencyChecker;

/**
 * @extends RuleTestCase<NewInstanceRule>
 */
class NewInstanceRuleTest extends RuleTestCase
{
    /** @override */
    protected function getRule(): Rule
    {
        return new NewInstanceRule(new DependencyChecker(
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
                'Cannot allow depends Model\IndexModel to stdClass::__construct().',
                22
            ],
            [
                'Cannot allow depends Model\IndexModel to Presenter\Form::__construct().',
                32
            ],
            [
                'Cannot allow depends Util\ModelUtil to Model\IndexModel::__construct().',
                78
            ],
            [
                'Cannot allow depends Util\Container to View\IndexView::__construct().',
                92
            ],
        ]);
    }
}
