<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Notifications;

use BS23\FormBuilder\Settings\FormSettings;

final class Mailer
{
    private FormSettings $settings;
    private TemplateRenderer $templates;

    public function __construct(FormSettings $settings, TemplateRenderer $templates)
    {
        $this->settings = $settings;
        $this->templates = $templates;
    }

    public function send(int $formId, int $entryId, array $entryData, array $settings): bool
    {
        if (empty($settings['notification']['enabled'])) {
            return false;
        }

        $to = $this->settings->resolveRecipient((string) ($settings['notification']['to'] ?? '{admin_email}'));
        $subject = $this->templates->render((string) ($settings['notification']['subject'] ?? ''), $formId, $entryId, $entryData);
        $message = $this->templates->render((string) ($settings['notification']['message'] ?? ''), $formId, $entryId, $entryData);
        $headers = [];
        $replyField = (string) ($settings['notification']['reply_to'] ?? '');

        if ($replyField !== '' && ! empty($entryData[$replyField]) && is_email((string) $entryData[$replyField])) {
            $headers[] = 'Reply-To: ' . sanitize_email((string) $entryData[$replyField]);
        }

        return (bool) wp_mail($to, $subject, $message, $headers);
    }
}
