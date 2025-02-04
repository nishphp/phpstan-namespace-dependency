<?php

declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use Nish\PHPStan\NsDepends\DependencyChecker;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node\PropertyItem>
 */
class PropertyRule implements Rule
{

	/** @var DependencyChecker */
	private $checker;

	public function __construct(DependencyChecker $checker)
	{
		$this->checker = $checker;
	}

	public function getNodeType(): string
	{
		return Node\PropertyItem::class;
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

		$classReflection = $scope->getClassReflection();
		$propertyReflection = $classReflection->getNativeProperty($node->name->name);

		$referencedClasses = array_merge(
			$propertyReflection->getNativeType()->getReferencedClasses(),
			$propertyReflection->getPhpDocType()->getReferencedClasses()
		);

		foreach ($referencedClasses as $referencedClass) {
			if ($this->checker->accept($sourceClassName, $referencedClass)) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf(
				'Cannot allow depends in %s::$%s(%s).',
				$propertyReflection->getDeclaringClass()->getDisplayName(),
				$node->name->name,
				$referencedClass
			))->line($node->getLine())->build();
		}

		return $errors;
	}

}
