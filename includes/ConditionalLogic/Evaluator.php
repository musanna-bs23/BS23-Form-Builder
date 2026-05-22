<?php
declare(strict_types=1);

namespace BS23\FormBuilder\ConditionalLogic;

final class Evaluator
{
    private const OPERATORS = ['equals', 'not_equals', 'contains', 'is_empty', 'is_not_empty'];

    public function isVisible(array $field, array $values): bool
    {
        $logic = $field['settings']['conditionalLogic'] ?? null;

        if (! is_array($logic) || empty($logic['enabled'])) {
            return true;
        }

        $rules = is_array($logic['rules'] ?? null) ? $logic['rules'] : [];
        if ($rules === []) {
            return true;
        }

        $match = ($logic['match'] ?? 'all') === 'any' ? 'any' : 'all';
        $action = ($logic['action'] ?? 'show') === 'hide' ? 'hide' : 'show';
        $matches = array_map(fn (array $rule): bool => $this->ruleMatches($rule, $values), $rules);
        $matched = $match === 'any'
            ? in_array(true, $matches, true)
            : ! in_array(false, $matches, true);

        return $action === 'show' ? $matched : ! $matched;
    }

    private function ruleMatches(array $rule, array $values): bool
    {
        $field = sanitize_key((string) ($rule['field'] ?? ''));
        $operator = (string) ($rule['operator'] ?? 'equals');

        if ($field === '' || ! in_array($operator, self::OPERATORS, true)) {
            return false;
        }

        $actual = $this->normalizeValue($values[$field] ?? '');
        $expected = $this->normalizeValue($rule['value'] ?? '');

        if ($operator === 'is_empty') {
            return $actual === '';
        }

        if ($operator === 'is_not_empty') {
            return $actual !== '';
        }

        if ($operator === 'contains') {
            return $expected !== '' && stripos($actual, $expected) !== false;
        }

        if ($operator === 'not_equals') {
            return $actual !== $expected;
        }

        return $actual === $expected;
    }

    private function normalizeValue($value): string
    {
        if (is_array($value)) {
            $value = implode(' ', array_map(static fn ($item): string => is_scalar($item) ? (string) $item : '', $value));
        }

        return trim(is_scalar($value) ? (string) $value : '');
    }
}
