<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Submission;

final class UploadStorage
{
    public function store(int $formId, string $fieldName, array $file): array
    {
        $upload = wp_upload_dir();
        $baseDir = trailingslashit((string) $upload['basedir']) . 'bs23-form-builder/' . $formId;
        $baseUrl = trailingslashit((string) $upload['baseurl']) . 'bs23-form-builder/' . $formId;

        wp_mkdir_p($baseDir);

        $name = sanitize_file_name((string) ($file['name'] ?? 'upload'));
        $target = trailingslashit($baseDir) . wp_unique_filename($baseDir, $name);
        $tmp = (string) ($file['tmp_name'] ?? '');

        if ($tmp !== '' && is_uploaded_file($tmp)) {
            move_uploaded_file($tmp, $target);
        } elseif ($tmp !== '' && file_exists($tmp)) {
            rename($tmp, $target);
        }

        $url = trailingslashit($baseUrl) . basename($target);
        $attachmentId = $this->createAttachment($target, $url, (string) ($file['type'] ?? ''), $name);

        return [
            'name' => basename($target),
            'url' => esc_url_raw($url),
            'path' => $target,
            'type' => sanitize_text_field((string) ($file['type'] ?? '')),
            'size' => isset($file['size']) && is_numeric($file['size']) ? (int) $file['size'] : 0,
            'attachment_id' => $attachmentId,
        ];
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function storeUploads(int $formId, array $schema, array $data): array
    {
        foreach ($this->uploadFieldNames($schema['fields'] ?? []) as $name) {
            if (! is_array($data[$name] ?? null) || empty($data[$name]['tmp_name'])) {
                continue;
            }
            $data[$name] = $this->store($formId, $name, $data[$name]);
        }

        return $data;
    }

    private function createAttachment(string $path, string $url, string $type, string $title): int
    {
        if (! function_exists('wp_insert_attachment') || ! file_exists($path)) {
            return 0;
        }

        $attachmentId = wp_insert_attachment([
            'guid' => $url,
            'post_mime_type' => $type ?: wp_check_filetype($path)['type'],
            'post_title' => sanitize_text_field(pathinfo($title, PATHINFO_FILENAME)),
            'post_content' => '',
            'post_status' => 'inherit',
        ], $path);

        return is_wp_error($attachmentId) ? 0 : (int) $attachmentId;
    }

    private function uploadFieldNames(array $fields): array
    {
        $names = [];

        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }
            if (($field['type'] ?? '') === 'container') {
                foreach (($field['children'] ?? []) as $column) {
                    if (is_array($column)) {
                        $names = array_merge($names, $this->uploadFieldNames($column));
                    }
                }
                continue;
            }
            if (in_array($field['type'] ?? '', ['file_upload', 'image_upload'], true)) {
                $names[] = sanitize_key((string) ($field['name'] ?? $field['id'] ?? ''));
            }
        }

        return array_filter($names);
    }
}
