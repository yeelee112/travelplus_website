<?php

namespace App\Commands;

use App\Services\WebsiteKnowledgeService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class PreviewTourAdvisory extends BaseCommand
{
    protected $group = 'TravelPlus';
    protected $name = 'tour:preview-advisory';
    protected $description = 'Preview the auto-generated tour advisory profile used by the website AI chatbox.';
    protected $usage = 'tour:preview-advisory "tour or customer question" [--locale vi|en]';
    protected $options = [
        '--locale' => 'Locale to preview. Default: vi.',
    ];

    public function run(array $params)
    {
        $query = trim(implode(' ', $params));
        $locale = strtolower((string) (CLI::getOption('locale') ?? 'vi'));
        $locale = $locale === 'en' ? 'en' : 'vi';

        if ($query === '') {
            CLI::error('Please provide a tour name, destination, or customer question.');
            CLI::write('Example: php spark tour:preview-advisory "tour đà nẵng cho gia đình"');

            return EXIT_ERROR;
        }

        $facts = (new WebsiteKnowledgeService())->getStructuredFacts($locale, $query, []);

        if (! is_array($facts)) {
            CLI::error('No structured tour facts matched this query.');

            return EXIT_ERROR;
        }

        $tour = is_array($facts['tour'] ?? null) ? $facts['tour'] : [];
        $selectedTour = is_array($facts['selected_tour'] ?? null) ? $facts['selected_tour'] : [];
        $advisory = is_array($facts['selected_advisory'] ?? null)
            ? $facts['selected_advisory']
            : (is_array($tour['advisory'] ?? null) ? $tour['advisory'] : []);

        CLI::write('Type: ' . (string) ($facts['type'] ?? ''), 'cyan');
        CLI::write('Tour: ' . (string) ($tour['title'] ?? $selectedTour['title'] ?? '(none)'), 'cyan');

        if ($advisory === []) {
            CLI::write('No advisory profile was generated for this result.', 'yellow');

            return EXIT_SUCCESS;
        }

        CLI::write('');
        CLI::write('Summary:', 'green');
        CLI::write((string) ($advisory['summary'] ?? ''));

        $this->writeList('Matched categories', (array) ($advisory['matched_categories'] ?? []));
        $this->writeList('Request signals', (array) ($advisory['request_signals'] ?? []));
        $this->writeList('Strengths', (array) ($advisory['strengths'] ?? []));
        $this->writeList('Suitable for', (array) ($advisory['suitable_for'] ?? []));
        $this->writeList('Personalized notes', (array) ($advisory['personalized_notes'] ?? []));

        CLI::write('');
        CLI::write('Pace: ' . (string) ($advisory['pace'] ?? ''));
        CLI::write('Pace note: ' . (string) ($advisory['pace_note'] ?? ''));
        CLI::write('Budget segment: ' . (string) ($advisory['budget_segment'] ?? ''));
        CLI::write('Sales caution: ' . (string) ($advisory['sales_caution'] ?? ''));

        $this->writeList('Service add-ons', (array) ($advisory['service_addons'] ?? []));
        $this->writeList('Next questions', (array) ($advisory['next_questions'] ?? []));

        return EXIT_SUCCESS;
    }

    /**
     * @param array<int, mixed> $items
     */
    private function writeList(string $title, array $items): void
    {
        $items = array_values(array_filter(array_map(
            static fn (mixed $item): string => trim((string) $item),
            $items
        )));

        if ($items === []) {
            return;
        }

        CLI::write('');
        CLI::write($title . ':', 'green');

        foreach ($items as $item) {
            CLI::write('- ' . $item);
        }
    }
}
