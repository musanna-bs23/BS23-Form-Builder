<?php
declare(strict_types=1);

namespace BS23\FormBuilder;

use BS23\FormBuilder\Builder\SchemaValidator;
use BS23\FormBuilder\PostTypes\FormPostType;
use BS23\FormBuilder\Rest\FormRestController;

final class Plugin
{
    public function register(): void
    {
        (new FormPostType())->register();
        (new FormRestController(new SchemaValidator()))->register();
    }
}
