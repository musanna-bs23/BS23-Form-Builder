<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Rest;

use BS23\FormBuilder\Notifications\Mailer;
use BS23\FormBuilder\Settings\FormSettings;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class FormSettingsRestController
{
    private FormSettings $settings;
    private Mailer $mailer;

    public function __construct(FormSettings $settings, Mailer $mailer)
    {
        $this->settings = $settings;
        $this->mailer = $mailer;
    }

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route(FormRestController::NAMESPACE, '/forms/(?P<id>\d+)/settings', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getSettings'],
                'permission_callback' => [$this, 'canManage'],
            ],
            [
                'methods' => 'PUT',
                'callback' => [$this, 'saveSettings'],
                'permission_callback' => [$this, 'canManage'],
            ],
        ]);

        register_rest_route(FormRestController::NAMESPACE, '/forms/(?P<id>\d+)/settings/test-email', [
            'methods' => 'POST',
            'callback' => [$this, 'testEmail'],
            'permission_callback' => [$this, 'canManage'],
        ]);
    }

    public function canManage(): bool
    {
        return current_user_can('manage_options');
    }

    public function getSettings(WP_REST_Request $request)
    {
        $formId = absint($request['id']);
        if ($formId < 1) {
            return new WP_Error('bs23_invalid_form', __('Invalid form.', 'bs23-form-builder'), ['status' => 404]);
        }

        return new WP_REST_Response($this->settings->get($formId), 200);
    }

    public function saveSettings(WP_REST_Request $request): WP_REST_Response
    {
        $formId = absint($request['id']);
        $settings = $request->get_param('settings');

        return new WP_REST_Response($this->settings->save($formId, is_array($settings) ? $settings : []), 200);
    }

    public function testEmail(WP_REST_Request $request): WP_REST_Response
    {
        $formId = absint($request['id']);
        $settings = $request->get_param('settings');
        $settings = is_array($settings) ? $this->settings->sanitize($settings) : $this->settings->get($formId);
        $sent = $this->mailer->send($formId, 0, ['email' => get_option('admin_email'), 'test' => 'This is a test notification.'], $settings);

        return new WP_REST_Response(['sent' => $sent], 200);
    }
}
