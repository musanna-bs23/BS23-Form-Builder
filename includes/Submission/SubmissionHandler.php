<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Submission;

use BS23\FormBuilder\PostTypes\FormPostType;
use BS23\FormBuilder\Rest\FormRestController;
use BS23\FormBuilder\Notifications\Mailer;
use BS23\FormBuilder\Settings\FormSettings;
use BS23\FormBuilder\Validation\SubmissionValidator;

final class SubmissionHandler
{
    public const ACTION_FIELD = 'bs23_form_submit';

    private SubmissionValidator $validator;
    private EntryRepository $entries;
    private UploadStorage $uploads;
    private FormSettings $settings;
    private Mailer $mailer;
    private array $states = [];

    public function __construct(SubmissionValidator $validator, EntryRepository $entries, ?FormSettings $settings = null, ?Mailer $mailer = null, ?UploadStorage $uploads = null)
    {
        $this->validator = $validator;
        $this->entries = $entries;
        $this->uploads = $uploads ?: new UploadStorage();
        $this->settings = $settings ?: new FormSettings();
        $this->mailer = $mailer ?: new Mailer($this->settings, new \BS23\FormBuilder\Notifications\TemplateRenderer());
    }

    public function register(): void
    {
        add_action('init', [$this, 'handle']);
    }

    public function stateFor(int $formId): array
    {
        return $this->states[$formId] ?? [
            'success' => '',
            'errors' => [],
            'values' => [],
        ];
    }

    public function handle(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_POST[self::ACTION_FIELD])) {
            return;
        }

        $formId = isset($_POST['bs23_form_id']) ? absint(wp_unslash($_POST['bs23_form_id'])) : 0;
        $posted = array_merge(wp_unslash($_POST), $this->uploadedFiles());

        $this->states[$formId] = [
            'success' => '',
            'errors' => [],
            'values' => is_array($posted) ? $posted : [],
        ];

        if ($formId < 1 || get_post_type($formId) !== FormPostType::NAME) {
            $this->states[$formId]['errors']['form'] = __('Invalid form.', 'bs23-form-builder');
            return;
        }

        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';
        if (! wp_verify_nonce($nonce, $this->nonceAction($formId))) {
            $this->states[$formId]['errors']['form'] = __('Form security check failed.', 'bs23-form-builder');
            return;
        }

        $schema = get_post_meta($formId, FormRestController::META_KEY, true);
        if (! is_array($schema)) {
            $this->states[$formId]['errors']['form'] = __('Form configuration is missing.', 'bs23-form-builder');
            return;
        }

        $result = $this->validator->validate($schema, is_array($posted) ? $posted : []);
        if (! $result['valid']) {
            $this->states[$formId]['errors'] = $result['errors'];
            return;
        }

        $entryData = $this->uploads->storeUploads($formId, $schema, $result['data']);
        $entryId = $this->entries->insert($formId, $entryData);
        if ($entryId < 1) {
            $this->states[$formId]['errors']['form'] = __('Could not save your submission.', 'bs23-form-builder');
            return;
        }

        $settings = $this->settings->get($formId);
        $this->mailer->send($formId, $entryId, $entryData, $settings);

        $this->states[$formId] = [
            'success' => (string) ($settings['confirmation']['message'] ?? __('Thanks, your submission has been received.', 'bs23-form-builder')),
            'errors' => [],
            'values' => [],
        ];

        if (! empty($settings['confirmation']['redirect_url']) && ! headers_sent()) {
            wp_safe_redirect((string) $settings['confirmation']['redirect_url']);
            exit;
        }
    }

    public function nonceAction(int $formId): string
    {
        return 'bs23_form_submit_' . $formId;
    }

    private function uploadedFiles(): array
    {
        $files = [];
        foreach ($_FILES as $name => $file) {
            if (is_array($file)) {
                $files[sanitize_key((string) $name)] = $file;
            }
        }

        return $files;
    }
}
