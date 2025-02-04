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
 * @implements Rule<Expr\ClassConstFetch>
 */
class ClassConstFetchRule implements Rule
{

	private DependencyChecker $checker;

	public function __construct(DependencyChecker $checker)
	{
		$this->checker = $checker;
	}

	public function getNodeType(): string
	{
		return Expr\ClassConstFetch::class;
	}

	/** @return array<string|\PHPStan\Rules\RuleError> errors */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			return [];
		}

		$sourceClassReflection = $scope->getClassReflection();
		$sourceClassName = $sourceClassReflection->getName();

		$errors = [];

		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		if (!($node->class instanceof Node\Name)) {
			return [];
		}

		$className = (string) $node->class;

		if ($className === 'self' || $className === 'static') {
			return [];
		}

		if (!$this->checker->accept($sourceClassName, $className)) {
			$errors[] = RuleErrorBuilder::message(sprintf(
				'Cannot allow depends %s to %s::%s.',
				$sourceClassName,
				$className,
				$node->name->name
			))->line($node->getLine())->build();
		}

		return $errors;
	}

}
