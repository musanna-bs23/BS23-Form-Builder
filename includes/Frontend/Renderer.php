<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Frontend;

use BS23\FormBuilder\Submission\SubmissionHandler;

final class Renderer
{
    private SubmissionHandler $submissions;
    private bool $hasSubmit = false;

    public function __construct(SubmissionHandler $submissions)
    {
        $this->submissions = $submissions;
    }

    public function render(int $formId, array $schema): string
    {
        $this->hasSubmit = false;
        $state = $this->submissions->stateFor($formId);

        ob_start();
        ?>
        <form class="bs23-form" method="post">
            <?php wp_nonce_field($this->submissions->nonceAction($formId)); ?>
            <input type="hidden" name="bs23_form_id" value="<?php echo esc_attr((string) $formId); ?>" />
            <input type="hidden" name="<?php echo esc_attr(SubmissionHandler::ACTION_FIELD); ?>" value="1" />
            <?php echo $this->messageMarkup($state); ?>
            <?php echo $this->fieldsMarkup($schema['fields'] ?? [], $state); ?>
            <?php if (! $this->hasSubmit) : ?>
                <button class="bs23-form__submit" type="submit"><?php echo esc_html__('Submit', 'bs23-form-builder'); ?></button>
            <?php endif; ?>
        </form>
        <?php

        return trim((string) ob_get_clean());
    }

    private function fieldsMarkup(array $fields, array $state): string
    {
        $markup = '';

        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }

            $markup .= $this->fieldMarkup($field, $state);
        }

        return $markup;
    }

    private function fieldMarkup(array $field, array $state): string
    {
        $type = (string) ($field['type'] ?? '');

        if ($type === 'container') {
            return $this->containerMarkup($field, $state);
        }

        if ($type === 'section_break') {
            return sprintf(
                '<hr class="bs23-form__section" /><h3 class="bs23-form__section-title">%s</h3>',
                esc_html((string) ($field['label'] ?? ''))
            );
        }

        if ($type === 'html') {
            return '<div class="bs23-form__html">' . wp_kses_post((string) ($field['settings']['content'] ?? $field['label'] ?? '')) . '</div>';
        }

        if ($type === 'submit') {
            $this->hasSubmit = true;

            return sprintf(
                '<button class="bs23-form__submit" type="submit">%s</button>',
                esc_html((string) ($field['label'] ?? __('Submit', 'bs23-form-builder')))
            );
        }

        if ($type === 'hidden') {
            return $this->inputMarkup($field, $state, 'hidden');
        }

        $supported = ['name', 'email', 'text', 'textarea', 'number', 'dropdown', 'radio', 'checkbox', 'multiple_choice', 'url', 'phone'];
        if (! in_array($type, $supported, true)) {
            return '';
        }

        $name = $this->fieldName($field);
        $error = $state['errors'][$name] ?? '';

        return '<div class="bs23-form__field bs23-form__field--' . esc_attr($type) . '">' .
            $this->labelMarkup($field) .
            $this->controlMarkup($field, $state) .
            ($error ? '<div class="bs23-form__error">' . esc_html((string) $error) . '</div>' : '') .
            '</div>';
    }

    private function containerMarkup(array $field, array $state): string
    {
        $columns = (int) ($field['columns'] ?? 1);
        if (! in_array($columns, [1, 2, 3, 4], true)) {
            return '';
        }

        $markup = '<div class="bs23-form__row bs23-form__row--' . esc_attr((string) $columns) . '">';
        foreach (($field['children'] ?? []) as $column) {
            $markup .= '<div class="bs23-form__column">' . (is_array($column) ? $this->fieldsMarkup($column, $state) : '') . '</div>';
        }
        $markup .= '</div>';

        return $markup;
    }

    private function controlMarkup(array $field, array $state): string
    {
        $type = (string) ($field['type'] ?? 'text');

        if ($type === 'textarea') {
            return sprintf(
                '<textarea name="%s" %s>%s</textarea>',
                esc_attr($this->fieldName($field)),
                $this->requiredAttr($field),
                esc_textarea($this->value($field, $state))
            );
        }

        if (in_array($type, ['dropdown', 'radio', 'checkbox', 'multiple_choice'], true)) {
            return $this->choiceMarkup($field, $state);
        }

        $inputType = ['email' => 'email', 'number' => 'number', 'url' => 'url', 'phone' => 'tel', 'name' => 'text'][$type] ?? 'text';

        return $this->inputMarkup($field, $state, $inputType);
    }

    private function inputMarkup(array $field, array $state, string $inputType): string
    {
        return sprintf(
            '<input type="%s" name="%s" value="%s" %s />',
            esc_attr($inputType),
            esc_attr($this->fieldName($field)),
            esc_attr($this->value($field, $state)),
            $inputType === 'hidden' ? '' : $this->requiredAttr($field)
        );
    }

    private function choiceMarkup(array $field, array $state): string
    {
        $type = (string) ($field['type'] ?? '');
        $name = $this->fieldName($field);
        $choices = $this->choices($field);
        $current = $state['values'][$name] ?? null;

        if ($type === 'dropdown') {
            $markup = '<select name="' . esc_attr($name) . '" ' . $this->requiredAttr($field) . '>';
            $markup .= '<option value="">' . esc_html__('Select', 'bs23-form-builder') . '</option>';
            foreach ($choices as $choice) {
                $markup .= sprintf('<option value="%s" %s>%s</option>', esc_attr($choice), selected($current, $choice, false), esc_html($choice));
            }
            return $markup . '</select>';
        }

        $multiple = in_array($type, ['checkbox', 'multiple_choice'], true);
        $inputType = $multiple ? 'checkbox' : 'radio';
        $inputName = $multiple ? $name . '[]' : $name;
        $values = is_array($current) ? $current : [$current];
        $markup = '<div class="bs23-form__choices">';

        foreach ($choices as $choice) {
            $markup .= sprintf(
                '<label><input type="%s" name="%s" value="%s" %s /> %s</label>',
                esc_attr($inputType),
                esc_attr($inputName),
                esc_attr($choice),
                checked(in_array($choice, $values, true), true, false),
                esc_html($choice)
            );
        }

        return $markup . '</div>';
    }

    private function choices(array $field): array
    {
        $choices = $field['settings']['choices'] ?? $field['settings']['options'] ?? ['Option 1'];
        if (is_string($choices)) {
            $choices = array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $choices)));
        }
        if (! is_array($choices) || $choices === []) {
            return ['Option 1'];
        }

        return array_values(array_map('sanitize_text_field', $choices));
    }

    private function labelMarkup(array $field): string
    {
        return sprintf(
            '<label class="bs23-form__label">%s%s</label>',
            esc_html((string) ($field['label'] ?? 'Field')),
            ! empty($field['required']) ? ' <span aria-hidden="true">*</span>' : ''
        );
    }

    private function messageMarkup(array $state): string
    {
        if (! empty($state['success'])) {
            return '<div class="bs23-form__success">' . esc_html((string) $state['success']) . '</div>';
        }

        if (! empty($state['errors']['form'])) {
            return '<div class="bs23-form__error">' . esc_html((string) $state['errors']['form']) . '</div>';
        }

        return '';
    }

    private function fieldName(array $field): string
    {
        return sanitize_key((string) ($field['name'] ?? $field['id'] ?? 'field'));
    }

    private function value(array $field, array $state): string
    {
        $name = $this->fieldName($field);
        $value = $state['values'][$name] ?? ($field['settings']['default'] ?? '');

        return is_scalar($value) ? (string) $value : '';
    }

    private function requiredAttr(array $field): string
    {
        return ! empty($field['required']) ? 'required' : '';
    }
}
