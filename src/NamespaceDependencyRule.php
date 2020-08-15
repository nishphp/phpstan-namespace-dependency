<?php

declare(strict_types = 1);

namespace Nish\PHPStan\NsDepends;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\VerbosityLevel;

function starts_with(string $haystack, string $needle): bool
{
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

/**
 * @implements Rule<Node>
 */
class NamespaceDependencyRule implements Rule
{

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var array<int, array<int,string>> $from */
	private $from;
	/** @var array<int, array<int,string>> $from */
	private $to;

	/**
	 * @param array<int, array<int, string>> $from
	 * @param array<int, array<int, string>> $to
	 */
	public function __construct(
        array $from, array $to
        , RuleLevelHelper $ruleLevelHelper
    )
	{
        $this->from = $from;
        $this->to = $to;
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return Node::class;
	}

    private function accept(string $from, string $to): bool
    {
        foreach ($this->from as $toPrefix => $fromPrefixes){
            if (starts_with($to, $toPrefix)){
                foreach ($fromPrefixes as $fromPrefix){
                    if (starts_with($from, $fromPrefix)){
                        return true;
                    }
                }

                return false;
            }
        }

        foreach ($this->to as $fromPrefix => $toPrefixes){
            if (starts_with($from, $fromPrefix)){
                foreach ($toPrefixes as $toPrefix){
                    if (starts_with($to, $toPrefix)){
                        return true;
                    }
                }

                return false;
            }
        }

        // not exists config, default is allow
        return true;
    }

	/** @return array<string|\PHPStan\Rules\RuleError> errors */
	public function processNode(Node $node, Scope $scope): array
	{
        if (!$scope->isInClass()){
            return [];
        }

        $namespace = $scope->getNamespace();
        if ($namespace === null){
            return [];
        }

        $errors = [];

        if ($node instanceof \PHPStan\Node\InClassMethodNode){
            $methodReflection = $scope->getFunction();
            $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
            foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
                $type = $parameterReflection->getType();

                $referencedClasses = $type->getReferencedClasses();
                foreach ($referencedClasses as $referencedClass){
                    if ($this->accept($namespace, $referencedClass)) continue;

                    $errors[] = RuleErrorBuilder::message(sprintf(
                        'Cannot allow depends in %s::%s() parameter $%s(%s).',
                        $methodReflection->getDeclaringClass()->getDisplayName(),
                        $methodReflection->getName(),
                        $parameterReflection->getName(),
                        $referencedClass
                    ))->line($node->getLine())->build();
                }
            }

            $referencedClasses = array_merge(
                $parametersAcceptor->getNativeReturnType()->getReferencedClasses(),
                $parametersAcceptor->getPhpDocReturnType()->getReferencedClasses()
            );
            foreach ($referencedClasses as $referencedClass){
                if ($this->accept($namespace, $referencedClass)) continue;

                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Cannot allow depends in %s::%s() return type %s.',
                    $methodReflection->getDeclaringClass()->getDisplayName(),
                    $methodReflection->getName(),
                    $referencedClass
                ))->line($node->getLine())->build();
            }

        }elseif($node instanceof \PhpParser\Node\Stmt\PropertyProperty){
            $propertyReflection = $scope->getClassReflection()->getNativeProperty($node->name->name);
			$referencedClasses = array_merge(
				$propertyReflection->getNativeType()->getReferencedClasses(),
				$propertyReflection->getPhpDocType()->getReferencedClasses()
			);

            foreach ($referencedClasses as $referencedClass) {
                if ($this->accept($namespace, $referencedClass)) continue;

                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Cannot allow depends in %s::$%s(%s).',
                    $propertyReflection->getDeclaringClass()->getDisplayName(),
                    $node->name->name,
                    $referencedClass
                ))->line($node->getLine())->build();
            }


        }elseif ($node instanceof Expr\StaticCall){
            if (!$node->name instanceof Node\Identifier)
                return [];

            $class = $node->class;
            if (!($class instanceof Node\Name))
                return [];

            $className = (string) $class;
            $lowercasedClassName = strtolower($className);
            if (in_array($lowercasedClassName, ['self', 'static', 'parent']))
                return [];

            if (!$this->accept($namespace, $className)) {
                $methodName = $node->name->name;
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Cannot allow depends %s to %s::%s().',
                    $namespace,
                    $className,
                    $methodName
                ))->line($node->getLine())->build();
            }

        }elseif ($node instanceof Expr\New_){
            $class = $node->class;
            if (!($class instanceof Node\Name))
                return [];

            $className = (string) $class;
            $lowercasedClassName = strtolower($className);
            if (in_array($lowercasedClassName, ['self', 'static', 'parent']))
                return [];

            if (!$this->accept($namespace, $className)) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Cannot allow depends %s to %s::__construct().',
                    $namespace,
                    $className
                ))->line($node->getLine())->build();
            }


        }else{
            return [];
        }

		return $errors;
	}

}
