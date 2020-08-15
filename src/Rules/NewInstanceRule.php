<?php

declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Nish\PHPStan\NsDepends\DependencyChecker;

/**
 * @implements Rule<Expr\New_>
 */
class NewInstanceRule implements Rule
{
    /** @var DependencyChecker */
    private $checker;

	public function __construct(DependencyChecker $checker)
	{
        $this->checker = $checker;
	}

	public function getNodeType(): string
	{
		return Expr\New_::class;
	}

	/** @return array<string|\PHPStan\Rules\RuleError> errors */
	public function processNode(Node $node, Scope $scope): array
	{
        if (!$node instanceof Expr\New_)
            return [];

        if (!$scope->isInClass())
            return [];

        $namespace = $scope->getNamespace();
        if ($namespace === null)
            return [];

        $errors = [];

        $class = $node->class;
        if (!$class instanceof Node\Name)
            return [];

        $className = (string) $class;
        $lowercasedClassName = strtolower($className);
        if (in_array($lowercasedClassName, ['self', 'static', 'parent']))
            return [];

        if (!$this->checker->accept($namespace, $className)) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Cannot allow depends %s to %s::__construct().',
                $namespace,
                $className
            ))->line($node->getLine())->build();
        }

        return $errors;
    }
}
