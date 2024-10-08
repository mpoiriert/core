<?php

namespace Draw\Component\Core\FilterExpression\Expression;

use Draw\Component\Core\FilterExpression\Evaluator;

class CompositeExpressionEvaluator extends ExpressionEvaluator
{
    public function __construct(private Evaluator $evaluator)
    {
    }

    public function evaluate($data, Expression $expression): bool
    {
        if (!$expression instanceof CompositeExpression) {
            throw new \InvalidArgumentException('Expression of class ['.$expression::class.'] is not supported');
        }

        $type = $expression->getType();

        if (!\in_array($type, [CompositeExpression::TYPE_AND, CompositeExpression::TYPE_OR], true)) {
            throw new \InvalidArgumentException('Unsupported CompositeExpression type ['.$type.']');
        }

        if (!\count($expressions = $expression->getExpressions())) {
            return true;
        }

        foreach ($expressions as $subExpression) {
            $result = $this->evaluator->evaluate($data, $subExpression);
            if ($result && CompositeExpression::TYPE_OR === $type) {
                return true;
            }

            if (!$result && CompositeExpression::TYPE_AND === $type) {
                return false;
            }
        }

        // If we pass trough the loop above no early exit happen. This is the logic base on that
        return CompositeExpression::TYPE_AND === $type;
    }
}
