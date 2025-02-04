<?php

declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends\Rules;

use Nish\PHPStan\NsDepends\DependencyChecker;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

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

	/**
	 * @template T of ParametersAcceptor
	 * @param T[] $parametersAcceptors
	 * @return T
	 */
	public static function selectSingle(
		array $parametersAcceptors,
	): ParametersAcceptor
	{
		$count = count($parametersAcceptors);
		if ($count === 0) {
			throw new ShouldNotHappenException(
				'getVariants() must return at least one variant.',
			);
		}
		if ($count !== 1) {
			throw new ShouldNotHappenException('Multiple variants - use selectFromArgs() instead.');
		}

		return $parametersAcceptors[0];
	}

	/** @return array<string|\PHPStan\Rules\RuleError> errors */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node instanceof \PHPStan\Node\InClassMethodNode) {
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

		$methodReflection = $scope->getFunction();
		if (!$methodReflection || !$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
			return [];
		}

		$parametersAcceptor = self::selectSingle($methodReflection->getVariants());
		foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
			$type = $parameterReflection->getType();

			$referencedClasses = $type->getReferencedClasses();
			foreach ($referencedClasses as $referencedClass) {
				if ($this->checker->accept($sourceClassName, $referencedClass)) {
					continue;
				}

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
		} else {
			$referencedClasses = array_merge(
				$parametersAcceptor->getNativeReturnType()->getReferencedClasses(),
				$parametersAcceptor->getPhpDocReturnType()->getReferencedClasses()
			);
		}
		foreach ($referencedClasses as $referencedClass) {
			if ($this->checker->accept($sourceClassName, $referencedClass)) {
				continue;
			}

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
