<?php
declare(strict_types=1);

namespace BS23\FormBuilder\PostTypes;

final class FormPostType
{
    public const NAME = 'bs23_form';

    public function register(): void
    {
        add_action('init', [$this, 'registerPostType']);
    }

    public function registerPostType(): void
    {
        register_post_type(self::NAME, [
            'labels' => [
                'name' => __('BS23 Forms', 'bs23-form-builder'),
                'singular_name' => __('BS23 Form', 'bs23-form-builder'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports' => ['title'],
            'capability_type' => 'post',
        ]);
    }
}
