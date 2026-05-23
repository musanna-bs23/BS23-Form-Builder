<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests;

use BS23\FormBuilder\ConditionalLogic\Evaluator;
use WP_UnitTestCase;

final class ConditionalLogicEvaluatorTest extends WP_UnitTestCase
{
    public function test_show_logic_is_visible_when_rule_matches(): void
    {
        $field = [
            'settings' => [
                'conditionalLogic' => [
                    'enabled' => true,
                    'action' => 'show',
                    'match' => 'all',
                    'rules' => [
                        ['field' => 'department', 'operator' => 'equals', 'value' => 'Sales'],
                    ],
                ],
            ],
        ];

        $this->assertTrue((new Evaluator())->isVisible($field, ['department' => 'Sales']));
        $this->assertFalse((new Evaluator())->isVisible($field, ['department' => 'Support']));
    }

    public function test_hide_logic_is_hidden_when_any_rule_matches(): void
    {
        $field = [
            'settings' => [
                'conditionalLogic' => [
                    'enabled' => true,
                    'action' => 'hide',
                    'match' => 'any',
                    'rules' => [
                        ['field' => 'department', 'operator' => 'contains', 'value' => 'Sales'],
                        ['field' => 'email', 'operator' => 'is_empty', 'value' => 'ignored'],
                    ],
                ],
            ],
        ];

        $this->assertFalse((new Evaluator())->isVisible($field, ['department' => 'Inside Sales', 'email' => 'person@example.com']));
        $this->assertTrue((new Evaluator())->isVisible($field, ['department' => 'Support', 'email' => 'person@example.com']));
    }
}
