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
 * @implements Rule<Expr\StaticCall>
 */
class StaticCallRule implements Rule
{

	private DependencyChecker $checker;

	public function __construct(DependencyChecker $checker)
	{
		$this->checker = $checker;
	}

	public function getNodeType(): string
	{
		return Expr\StaticCall::class;
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

		$class = $node->class;
		if (!$class instanceof Node\Name) {
			return [];
		}

		$className = (string) $class;
		$lowercasedClassName = strtolower($className);
		if (in_array($lowercasedClassName, ['self', 'static', 'parent'])) {
			return [];
		}

		if (!$this->checker->accept($sourceClassName, $className)) {
			$methodName = $node->name->name;
			$errors[] = RuleErrorBuilder::message(sprintf(
				'Cannot allow depends %s to %s::%s().',
				$sourceClassName,
				$className,
				$methodName
			))->line($node->getLine())->build();
		}

		return $errors;
	}

}
