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
 * @implements Rule<Expr\StaticPropertyFetch>
 */
class StaticPropertyFetchRule implements Rule
{
    /** @var DependencyChecker */
    private $checker;

	public function __construct(DependencyChecker $checker)
	{
        $this->checker = $checker;
	}

	public function getNodeType(): string
	{
		return Expr\StaticPropertyFetch::class;
	}

	/** @return array<string|\PHPStan\Rules\RuleError> errors */
	public function processNode(Node $node, Scope $scope): array
	{
        if (!$node instanceof Expr\StaticPropertyFetch)
            return [];

        if (!$scope->isInClass())
            return [];

        $namespace = $scope->getNamespace();
        if ($namespace === null)
            return [];

        $errors = [];


        if (!$node->name instanceof Node\VarLikeIdentifier) {
            return [];
        }

        if (!$node->class instanceof Node\Name)
            return [];

        $className = (string) $node->class;
        if (!$this->checker->accept($namespace, $className)) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Cannot allow depends %s to %s::$%s.',
                $namespace,
                $className,
                $node->name->name
            ))->line($node->getLine())->build();
        }

        return $errors;
    }
}
