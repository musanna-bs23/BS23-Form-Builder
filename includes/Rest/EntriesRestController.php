<?php
declare(strict_types=1);

namespace BS23\FormBuilder\Rest;

use BS23\FormBuilder\Entries\EntryQueryRepository;
use BS23\FormBuilder\Export\CsvExporter;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class EntriesRestController
{
    private EntryQueryRepository $entries;
    private CsvExporter $csv;

    public function __construct(EntryQueryRepository $entries, CsvExporter $csv)
    {
        $this->entries = $entries;
        $this->csv = $csv;
    }

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route(FormRestController::NAMESPACE, '/entries', [
            'methods' => 'GET',
            'callback' => [$this, 'listEntries'],
            'permission_callback' => [$this, 'canManage'],
        ]);

        register_rest_route(FormRestController::NAMESPACE, '/entries/summary', [
            'methods' => 'GET',
            'callback' => [$this, 'summary'],
            'permission_callback' => [$this, 'canManage'],
        ]);

        register_rest_route(FormRestController::NAMESPACE, '/entries/export', [
            'methods' => 'GET',
            'callback' => [$this, 'export'],
            'permission_callback' => [$this, 'canManage'],
        ]);

        register_rest_route(FormRestController::NAMESPACE, '/entries/bulk-delete', [
            'methods' => 'POST',
            'callback' => [$this, 'bulkDelete'],
            'permission_callback' => [$this, 'canManage'],
        ]);

        register_rest_route(FormRestController::NAMESPACE, '/entries/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getEntry'],
                'permission_callback' => [$this, 'canManage'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'deleteEntry'],
                'permission_callback' => [$this, 'canManage'],
            ],
        ]);
    }

    public function canManage(): bool
    {
        return current_user_can('manage_options');
    }

    public function listEntries(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response($this->entries->list($this->queryArgs($request)), 200);
    }

    public function summary(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response($this->entries->summary($this->queryArgs($request)), 200);
    }

    public function getEntry(WP_REST_Request $request)
    {
        $entry = $this->entries->find(absint($request['id']));
        if (! $entry) {
            return new WP_Error('bs23_entry_not_found', __('Entry not found.', 'bs23-form-builder'), ['status' => 404]);
        }

        return new WP_REST_Response($entry, 200);
    }

    public function deleteEntry(WP_REST_Request $request): WP_REST_Response
    {
        $deleted = $this->entries->delete(absint($request['id']));

        return new WP_REST_Response(['deleted' => $deleted], 200);
    }

    public function bulkDelete(WP_REST_Request $request): WP_REST_Response
    {
        $ids = $request->get_param('ids');
        $deleted = $this->entries->bulkDelete(is_array($ids) ? $ids : []);

        return new WP_REST_Response(['deleted' => $deleted], 200);
    }

    public function export(WP_REST_Request $request): WP_REST_Response
    {
        $csv = $this->csv->export($this->entries->allForExport($this->queryArgs($request)));

        return new WP_REST_Response([
            'filename' => 'bs23-form-entries-' . gmdate('Y-m-d') . '.csv',
            'csv' => $csv,
        ], 200);
    }

    private function queryArgs(WP_REST_Request $request): array
    {
        return [
            'form_id' => absint($request->get_param('form_id')),
            'search' => sanitize_text_field((string) $request->get_param('search')),
            'date_from' => sanitize_text_field((string) $request->get_param('date_from')),
            'date_to' => sanitize_text_field((string) $request->get_param('date_to')),
            'order' => strtoupper((string) $request->get_param('order')) === 'ASC' ? 'ASC' : 'DESC',
            'page' => max(1, absint($request->get_param('page'))),
            'per_page' => max(1, absint($request->get_param('per_page') ?: 20)),
        ];
    }
}
