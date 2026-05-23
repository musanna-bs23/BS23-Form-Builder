<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Elementor;

use BS23\FormBuilder\PostTypes\FormPostType;
use Elementor\Controls_Manager;
use Elementor\Widget_Base;

class FormWidget extends Widget_Base
{
    public function get_name(): string
    {
        return 'bs23_form_builder';
    }

    public function get_title(): string
    {
        return esc_html__('BS23 Form', 'bs23-form-builder');
    }

    public function get_icon(): string
    {
        return 'eicon-form-horizontal';
    }

    public function get_categories(): array
    {
        return ['general'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('bs23_form_content', [
            'label' => esc_html__('Form', 'bs23-form-builder'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('form_id', [
            'label' => esc_html__('Select Form', 'bs23-form-builder'),
            'type' => Controls_Manager::SELECT,
            'options' => $this->formOptions(),
            'default' => '',
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $formId = absint($this->get_settings_for_display('form_id'));
        if ($formId < 1) {
            echo '<div class="bs23-elementor-placeholder">' . esc_html__('Select a BS23 form', 'bs23-form-builder') . '</div>';

            return;
        }

        echo do_shortcode('[bs23_form id="' . $formId . '"]');
    }

    private function formOptions(): array
    {
        $posts = get_posts([
            'post_type' => FormPostType::NAME,
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'orderby' => 'title',
            'order' => 'ASC',
            'fields' => 'all',
            'no_found_rows' => true,
        ]);
        $options = [];

        foreach ($posts as $post) {
            $options[(int) $post->ID] = (string) $post->post_title;
        }

        return $options;
    }
}
