<?php

declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use Nish\PHPStan\NsDepends\DependencyChecker;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

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
		if (!$node instanceof Expr\StaticPropertyFetch) {
			return [];
		}

		if (!$scope->isInClass()) {
			return [];
		}

        $sourceClassReflection = $scope->getClassReflection();
        if (!$sourceClassReflection) {
            return [];
        }
        $sourceClassName = $sourceClassReflection->getName();

		$errors = [];

		if (!$node->name instanceof Node\VarLikeIdentifier) {
			return [];
		}

		if (!$node->class instanceof Node\Name) {
			return [];
		}

		$className = (string) $node->class;
		$lowercasedClassName = strtolower($className);
		if (in_array($lowercasedClassName, ['self', 'static', 'parent'])) {
			return [];
		}

		if (!$this->checker->accept($sourceClassName, $className)) {
			$errors[] = RuleErrorBuilder::message(sprintf(
				'Cannot allow depends %s to %s::$%s.',
				$sourceClassName,
				$className,
				$node->name->name
			))->line($node->getLine())->build();
		}

		return $errors;
	}

}
