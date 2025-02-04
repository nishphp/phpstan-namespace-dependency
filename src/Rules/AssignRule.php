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
 * @implements Rule<Expr>
 */
class AssignRule implements Rule
{

	private DependencyChecker $checker;

	public function __construct(DependencyChecker $checker)
	{
		$this->checker = $checker;
	}

	public function getNodeType(): string
	{
		return Expr::class;
	}

	/** @return array<string|\PHPStan\Rules\RuleError> errors */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			return [];
		}

		if (
			!$node instanceof Expr\Assign
			&& !$node instanceof Expr\AssignOp
		) {
			return [];
		}

		$sourceClassReflection = $scope->getClassReflection();
		$sourceClassName = $sourceClassReflection->getName();

		$errors = [];

		if ($node instanceof Node\Expr\Assign) {
			$assignedValueType = $scope->getType($node->expr);
		} else {
			$assignedValueType = $scope->getType($node);
		}

		$referencedClasses = $assignedValueType->getReferencedClasses();
		foreach ($referencedClasses as $referencedClass) {
			if ($this->checker->accept($sourceClassName, $referencedClass)) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf(
				'Cannot allow depends %s to %s.',
				$sourceClassName,
				$referencedClass
			))->line($node->getLine())->build();
		}

		return $errors;
	}

}
