<?php

declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use Nish\PHPStan\NsDepends\DependencyChecker;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Stmt\Class_>
 */
class ClassDefinitionRule implements Rule
{

	/** @var DependencyChecker */
	private $checker;

	public function __construct(DependencyChecker $checker)
	{
		$this->checker = $checker;
	}

	public function getNodeType(): string
	{
		return Stmt\Class_::class;
	}

	/** @return array<string|\PHPStan\Rules\RuleError> errors */
	public function processNode(Node $node, Scope $scope): array
	{
		$className = (string) $node->namespacedName;

		if ($node->namespacedName == '') {
			return [];
		}

		$errors = [];

		if ($node->implements) {
			foreach ($node->implements as $implements) {
				$implementedClassName = (string) $implements;

				if ($this->checker->accept($className, $implementedClassName)) {
					continue;
				}

				$errors[] = RuleErrorBuilder::message(sprintf(
					'Cannot allow depends %s implements %s.',
					$className,
					$implementedClassName
				))->line($node->getLine())->build();
			}
		}

		if ($node->extends !== null) {
			$extendedClassName = (string) $node->extends;

			if (!$this->checker->accept($className, $extendedClassName)) {
				$errors[] = RuleErrorBuilder::message(sprintf(
					'Cannot allow depends %s extends %s.',
					$className,
					$extendedClassName
				))->line($node->getLine())->build();
			}
		}

		return $errors;
	}

}
