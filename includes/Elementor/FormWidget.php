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

        $this->registerStyleControls();
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

    private function registerStyleControls(): void
    {
        $this->start_controls_section('bs23_form_layout_style', [
            'label' => esc_html__('Layout', 'bs23-form-builder'),
            'tab' => Controls_Manager::TAB_STYLE,
        ]);
        $this->addSliderStyleControl('max_width', esc_html__('Form Width', 'bs23-form-builder'), '--bs23-form-max-width', 760);
        $this->addSliderStyleControl('field_gap', esc_html__('Field Gap', 'bs23-form-builder'), '--bs23-field-gap', 16);
        $this->end_controls_section();

        $this->start_controls_section('bs23_form_label_style', [
            'label' => esc_html__('Labels', 'bs23-form-builder'),
            'tab' => Controls_Manager::TAB_STYLE,
        ]);
        $this->addColorStyleControl('label_color', esc_html__('Label Color', 'bs23-form-builder'), '--bs23-label-color');
        $this->addSliderStyleControl('label_size', esc_html__('Label Size', 'bs23-form-builder'), '--bs23-label-size', 14);
        $this->end_controls_section();

        $this->start_controls_section('bs23_form_input_style', [
            'label' => esc_html__('Inputs', 'bs23-form-builder'),
            'tab' => Controls_Manager::TAB_STYLE,
        ]);
        $this->addColorStyleControl('input_background', esc_html__('Background', 'bs23-form-builder'), '--bs23-input-background');
        $this->addColorStyleControl('input_border', esc_html__('Border', 'bs23-form-builder'), '--bs23-input-border');
        $this->addSliderStyleControl('input_radius', esc_html__('Radius', 'bs23-form-builder'), '--bs23-input-radius', 8);
        $this->end_controls_section();

        $this->start_controls_section('bs23_form_button_style', [
            'label' => esc_html__('Button', 'bs23-form-builder'),
            'tab' => Controls_Manager::TAB_STYLE,
        ]);
        $this->addColorStyleControl('button_background', esc_html__('Background', 'bs23-form-builder'), '--bs23-button-background');
        $this->addColorStyleControl('button_text', esc_html__('Text', 'bs23-form-builder'), '--bs23-button-text');
        $this->addSliderStyleControl('button_radius', esc_html__('Radius', 'bs23-form-builder'), '--bs23-button-radius', 8);
        $this->end_controls_section();

        $this->start_controls_section('bs23_form_message_style', [
            'label' => esc_html__('Messages', 'bs23-form-builder'),
            'tab' => Controls_Manager::TAB_STYLE,
        ]);
        $this->addColorStyleControl('error_color', esc_html__('Error Color', 'bs23-form-builder'), '--bs23-error-color');
        $this->addColorStyleControl('success_color', esc_html__('Success Color', 'bs23-form-builder'), '--bs23-success-color');
        $this->end_controls_section();

        $this->start_controls_section('bs23_form_step_style', [
            'label' => esc_html__('Steps', 'bs23-form-builder'),
            'tab' => Controls_Manager::TAB_STYLE,
        ]);
        $this->addColorStyleControl('step_active', esc_html__('Active Step', 'bs23-form-builder'), '--bs23-step-active');
        $this->end_controls_section();
    }

    private function addColorStyleControl(string $id, string $label, string $variable): void
    {
        $this->add_control($id, [
            'label' => $label,
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .bs23-form' => $variable . ': {{VALUE}};',
            ],
        ]);
    }

    private function addSliderStyleControl(string $id, string $label, string $variable, int $defaultSize): void
    {
        $this->add_control($id, [
            'label' => $label,
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', '%', 'em', 'rem'],
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 1200,
                ],
            ],
            'default' => [
                'unit' => 'px',
                'size' => $defaultSize,
            ],
            'selectors' => [
                '{{WRAPPER}} .bs23-form' => $variable . ': {{SIZE}}{{UNIT}};',
            ],
        ]);
    }
}
