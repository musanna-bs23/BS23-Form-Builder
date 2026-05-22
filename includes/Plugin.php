<?php
declare(strict_types=1);

namespace BS23\FormBuilder;

use BS23\FormBuilder\Admin\Menu;
use BS23\FormBuilder\Admin\EntriesPage;
use BS23\FormBuilder\Builder\SchemaValidator;
use BS23\FormBuilder\Entries\EntryQueryRepository;
use BS23\FormBuilder\Export\CsvExporter;
use BS23\FormBuilder\Frontend\Renderer;
use BS23\FormBuilder\Frontend\Shortcode;
use BS23\FormBuilder\PostTypes\FormPostType;
use BS23\FormBuilder\Rest\EntriesRestController;
use BS23\FormBuilder\Rest\FormRestController;
use BS23\FormBuilder\Submission\EntryRepository;
use BS23\FormBuilder\Submission\SubmissionHandler;
use BS23\FormBuilder\Validation\SubmissionValidator;

final class Plugin
{
    public function register(): void
    {
        $submissionHandler = new SubmissionHandler(new SubmissionValidator(), new EntryRepository());
        $renderer = new Renderer($submissionHandler);
        $entryQueries = new EntryQueryRepository();

        (new FormPostType())->register();
        (new FormRestController(new SchemaValidator()))->register();
        (new EntriesRestController($entryQueries, new CsvExporter()))->register();
        (new Menu())->register();
        (new EntriesPage())->register();
        $submissionHandler->register();
        (new Shortcode($renderer))->register();
    }
}
