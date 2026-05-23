<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class BootstrapRequireOrderTest extends TestCase
{
    public function test_submission_validator_dependencies_are_required_first(): void
    {
        $bootstrap = file_get_contents(dirname(__DIR__, 2) . '/bs23-form-builder.php');

        self::assertIsString($bootstrap);
        $validator = strpos($bootstrap, "includes/Validation/SubmissionValidator.php");
        $evaluator = strpos($bootstrap, "includes/ConditionalLogic/Evaluator.php");
        $rules = strpos($bootstrap, "includes/Validation/RuleValidator.php");

        self::assertNotFalse($validator);
        self::assertNotFalse($evaluator);
        self::assertNotFalse($rules);
        self::assertLessThan($validator, $evaluator);
        self::assertLessThan($validator, $rules);
    }
}
