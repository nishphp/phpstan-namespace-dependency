<?php

declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use Nish\PHPStan\NsDepends\DependencyChecker;

/**
 * @implements Rule<\PHPStan\Node\InClassMethodNode>
 */
class MethodDefinitionRule implements Rule
{
    /** @var DependencyChecker */
    private $checker;

	public function __construct(DependencyChecker $checker)
	{
        $this->checker = $checker;
	}

	public function getNodeType(): string
	{
		return \PHPStan\Node\InClassMethodNode::class;
	}

	/** @return array<string|\PHPStan\Rules\RuleError> errors */
	public function processNode(Node $node, Scope $scope): array
	{
        if (!$node instanceof \PHPStan\Node\InClassMethodNode)
            return [];

        if (!$scope->isInClass())
            return [];

        $namespace = $scope->getNamespace();
        if ($namespace === null)
            return [];

        $errors = [];

        $methodReflection = $scope->getFunction();
        if (!$methodReflection || !$methodReflection instanceof \PHPStan\Reflection\MethodReflection)
            return [];

        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            $type = $parameterReflection->getType();

            $referencedClasses = $type->getReferencedClasses();
            foreach ($referencedClasses as $referencedClass){
                if ($this->checker->accept($namespace, $referencedClass)) continue;

                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Cannot allow depends in %s::%s() parameter $%s(%s).',
                    $methodReflection->getDeclaringClass()->getDisplayName(),
                    $methodReflection->getName(),
                    $parameterReflection->getName(),
                    $referencedClass
                ))->line($node->getLine())->build();
            }
        }

		if (!$parametersAcceptor instanceof ParametersAcceptorWithPhpDocs) {
            $referencedClasses = $parametersAcceptor->getReturnType()->getReferencedClasses();
		}else {
            $referencedClasses = array_merge(
                $parametersAcceptor->getNativeReturnType()->getReferencedClasses(),
                $parametersAcceptor->getPhpDocReturnType()->getReferencedClasses()
            );
        }
        foreach ($referencedClasses as $referencedClass){
            if ($this->checker->accept($namespace, $referencedClass)) continue;

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Cannot allow depends in %s::%s() return type %s.',
                $methodReflection->getDeclaringClass()->getDisplayName(),
                $methodReflection->getName(),
                $referencedClass
            ))->line($node->getLine())->build();
        }

        return $errors;
    }
}
