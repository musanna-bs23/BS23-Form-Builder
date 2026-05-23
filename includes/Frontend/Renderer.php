<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Frontend;

use BS23\FormBuilder\ConditionalLogic\Evaluator;
use BS23\FormBuilder\Submission\SubmissionHandler;

final class Renderer
{
    private Evaluator $conditionalLogic;
    private SubmissionHandler $submissions;
    private bool $hasSubmit = false;

    public function __construct(SubmissionHandler $submissions, ?Evaluator $conditionalLogic = null)
    {
        $this->submissions = $submissions;
        $this->conditionalLogic = $conditionalLogic ?: new Evaluator();
    }

    public function render(int $formId, array $schema): string
    {
        $this->hasSubmit = false;
        $state = $this->submissions->stateFor($formId);
        $state['conditional_values'] = array_merge(
            $this->defaultValues($schema['fields'] ?? []),
            is_array($state['values'] ?? null) ? $state['values'] : []
        );

        ob_start();
        ?>
        <form class="bs23-form" method="post" data-bs23-form-id="<?php echo esc_attr((string) $formId); ?>" <?php echo $this->hasUploadFields($schema['fields'] ?? []) ? 'enctype="multipart/form-data"' : ''; ?>>
            <script type="application/json" class="bs23-form__schema"><?php echo wp_json_encode($schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
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
            return $this->wrapField($field, sprintf(
                '<hr class="bs23-form__section" /><h3 class="bs23-form__section-title">%s</h3>%s',
                esc_html((string) ($field['label'] ?? '')),
                ! empty($field['settings']['description']) ? '<p class="bs23-form__section-description">' . esc_html((string) $field['settings']['description']) . '</p>' : ''
            ), $state, ['bs23-form__field--section']);
        }

        if ($type === 'html') {
            return $this->wrapField($field, '<div class="bs23-form__html">' . wp_kses_post((string) ($field['settings']['content'] ?? $field['label'] ?? '')) . '</div>', $state, ['bs23-form__field--html']);
        }

        if ($type === 'form_step') {
            return sprintf(
                '<div class="bs23-form__step-marker" data-bs23-field-id="%s" data-bs23-step-marker>%s</div>',
                esc_attr((string) ($field['id'] ?? '')),
                esc_html((string) ($field['label'] ?? __('Step', 'bs23-form-builder')))
            );
        }

        if ($type === 'submit') {
            $this->hasSubmit = true;

            return $this->wrapField($field, sprintf(
                '<button class="bs23-form__submit" type="submit">%s</button>',
                esc_html((string) ($field['label'] ?? __('Submit', 'bs23-form-builder')))
            ), $state, ['bs23-form__field--submit']);
        }

        if ($type === 'hidden') {
            return $this->wrapField($field, $this->inputMarkup($field, $state, 'hidden'), $state, ['bs23-form__field', 'bs23-form__field--hidden']);
        }

        $supported = ['name', 'email', 'text', 'textarea', 'number', 'dropdown', 'radio', 'checkbox', 'multiple_choice', 'url', 'phone', 'file_upload', 'image_upload'];
        if (! in_array($type, $supported, true)) {
            return '';
        }

        $name = $this->fieldName($field);
        $error = $state['errors'][$name] ?? '';

        $customClasses = array_filter(array_map(
            'sanitize_html_class',
            preg_split('/\s+/', (string) ($field['settings']['className'] ?? '')) ?: []
        ));
        $classes = array_merge([
            'bs23-form__field',
            'bs23-form__field--' . sanitize_html_class($type),
        ], $customClasses);

        return $this->wrapField($field,
            $this->labelMarkup($field) .
            $this->controlMarkup($field, $state) .
            $this->helpMarkup($field) .
            ($error ? '<div class="bs23-form__error">' . esc_html((string) $error) . '</div>' : ''),
            $state,
            $classes
        );
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
                trim($this->requiredAttr($field) . ' ' . $this->placeholderAttr($field)),
                esc_textarea($this->value($field, $state))
            );
        }

        if (in_array($type, ['file_upload', 'image_upload'], true)) {
            return $this->fileInputMarkup($field);
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
            $inputType === 'hidden' ? '' : trim($this->requiredAttr($field) . ' ' . $this->placeholderAttr($field))
        );
    }

    private function fileInputMarkup(array $field): string
    {
        return sprintf(
            '<input type="file" name="%s" %s %s />',
            esc_attr($this->fieldName($field)),
            $this->requiredAttr($field),
            $this->acceptAttr($field)
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

    private function conditionalValues(array $state): array
    {
        $values = is_array($state['conditional_values'] ?? null) ? $state['conditional_values'] : [];

        return array_map(static fn ($value) => is_scalar($value) || is_array($value) ? $value : '', $values);
    }

    private function defaultValues(array $fields): array
    {
        $values = [];

        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }

            if (($field['type'] ?? '') === 'container') {
                foreach (($field['children'] ?? []) as $column) {
                    if (is_array($column)) {
                        $values = array_merge($values, $this->defaultValues($column));
                    }
                }
                continue;
            }

            $name = $this->fieldName($field);
            if ($name !== '' && isset($field['settings']['default'])) {
                $values[$name] = $field['settings']['default'];
            }
        }

        return $values;
    }

    private function requiredAttr(array $field): string
    {
        return ! empty($field['required']) ? 'required' : '';
    }

    private function placeholderAttr(array $field): string
    {
        $placeholder = (string) ($field['settings']['placeholder'] ?? '');

        return $placeholder !== '' ? 'placeholder="' . esc_attr($placeholder) . '"' : '';
    }

    private function helpMarkup(array $field): string
    {
        $help = (string) ($field['settings']['help'] ?? '');

        return $help !== '' ? '<p class="bs23-form__help">' . esc_html($help) . '</p>' : '';
    }

    private function acceptAttr(array $field): string
    {
        $extensions = (string) ($field['settings']['validation']['allowedExtensions'] ?? '');
        if ($extensions === '') {
            return '';
        }

        $accept = array_filter(array_map(
            static fn (string $extension): string => '.' . sanitize_key(ltrim(trim($extension), '.')),
            explode(',', $extensions)
        ));

        return $accept === [] ? '' : 'accept="' . esc_attr(implode(',', $accept)) . '"';
    }

    private function hasUploadFields(array $fields): bool
    {
        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }
            if (in_array($field['type'] ?? '', ['file_upload', 'image_upload'], true)) {
                return true;
            }
            if (($field['type'] ?? '') === 'container') {
                foreach (($field['children'] ?? []) as $column) {
                    if (is_array($column) && $this->hasUploadFields($column)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function wrapField(array $field, string $content, array $state, array $classes): string
    {
        $visible = $this->conditionalLogic->isVisible($field, $this->conditionalValues($state));
        $classes[] = $visible ? '' : 'is-hidden';

        return sprintf(
            '<div class="%s" data-bs23-field-id="%s" %s>%s</div>',
            esc_attr(implode(' ', array_filter($classes))),
            esc_attr((string) ($field['id'] ?? '')),
            $visible ? '' : 'hidden',
            $content
        );
    }
}
