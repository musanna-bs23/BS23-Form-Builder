<?php
declare(strict_types=1);

namespace BS23\FormBuilder;

use BS23\FormBuilder\PostTypes\FormPostType;

final class Plugin
{
    public function register(): void
    {
        (new FormPostType())->register();
    }
}
