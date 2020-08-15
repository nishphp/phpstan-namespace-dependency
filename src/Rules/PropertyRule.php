<?php

declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Nish\PHPStan\NsDepends\DependencyChecker;

/**
 * @implements Rule<Stmt\PropertyProperty>
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
		return Stmt\PropertyProperty::class;
	}

	/** @return array<string|\PHPStan\Rules\RuleError> errors */
	public function processNode(Node $node, Scope $scope): array
	{
        if (!$node instanceof Stmt\PropertyProperty)
            return [];

        if (!$scope->isInClass())
            return [];

        $namespace = $scope->getNamespace();
        if ($namespace === null)
            return [];

        $errors = [];

        $classReflection = $scope->getClassReflection();
        if (!$classReflection)
            return [];

        $propertyReflection = $classReflection->getNativeProperty($node->name->name);

        $referencedClasses = array_merge(
            $propertyReflection->getNativeType()->getReferencedClasses(),
            $propertyReflection->getPhpDocType()->getReferencedClasses()
        );

        foreach ($referencedClasses as $referencedClass) {
            if ($this->checker->accept($namespace, $referencedClass)) continue;

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
