<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Notifications;

final class TemplateRenderer
{
    public function render(string $template, int $formId, int $entryId, array $entryData): string
    {
        $replacements = [
            '{form_title}' => get_the_title($formId) ?: sprintf('Form #%d', $formId),
            '{entry_id}' => (string) $entryId,
            '{all_fields}' => $this->allFields($entryData),
        ];

        foreach ($entryData as $key => $value) {
            $replacements['{field:' . $key . '}'] = $this->valueToString($value);
        }

        return strtr($template, $replacements);
    }

    private function allFields(array $entryData): string
    {
        $lines = [];

        foreach ($entryData as $key => $value) {
            $lines[] = sprintf('%s: %s', $key, $this->valueToString($value));
        }

        return implode("\n", $lines);
    }

    private function valueToString($value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map('sanitize_text_field', array_map('strval', $value)));
        }

        return sanitize_text_field((string) $value);
    }
}
