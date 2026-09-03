<?php

namespace App\Commands;

use App\Services\GeminiWebsiteChatService;
use App\Services\WebsiteKnowledgeService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use ReflectionClass;

class PreviewTourAdvisory extends BaseCommand
{
    protected $group = 'TravelPlus';
    protected $name = 'tour:preview-advisory';
    protected $description = 'Preview the auto-generated tour advisory profile used by the website AI chatbox.';
    protected $usage = 'tour:preview-advisory "tour or customer question" [--locale vi|en] [--json]';
    protected $options = [
        '--locale' => 'Locale to preview. Default: vi.',
        '--json' => 'Output raw advisory profile as JSON.',
        '--fallback' => 'Output the chatbox fallback answer for the matched structured facts.',
    ];

    public function run(array $params)
    {
        $query = trim(implode(' ', $params));
        $locale = strtolower((string) (CLI::getOption('locale') ?? 'vi'));
        $locale = $locale === 'en' ? 'en' : 'vi';
        $jsonOutput = CLI::getOption('json') !== null;
        $fallbackOutput = CLI::getOption('fallback') !== null;

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

        if ($fallbackOutput) {
            $reflection = new ReflectionClass(GeminiWebsiteChatService::class);
            $method = $reflection->getMethod('buildFactsFallbackMessage');
            $method->setAccessible(true);

            CLI::write((string) $method->invoke(new GeminiWebsiteChatService(), $locale, $facts));

            return EXIT_SUCCESS;
        }

        $tour = is_array($facts['tour'] ?? null) ? $facts['tour'] : [];
        $selectedTour = is_array($facts['selected_tour'] ?? null) ? $facts['selected_tour'] : [];
        $advisory = is_array($facts['selected_advisory'] ?? null)
            ? $facts['selected_advisory']
            : (is_array($tour['advisory'] ?? null)
                ? $tour['advisory']
                : (is_array($facts['advisory'] ?? null) ? $facts['advisory'] : []));
        $fit = is_array($facts['selected_fit'] ?? null)
            ? $facts['selected_fit']
            : (is_array($selectedTour['fit'] ?? null)
                ? $selectedTour['fit']
                : (is_array($tour['fit'] ?? null) ? $tour['fit'] : []));

        if ($jsonOutput) {
            CLI::write(json_encode([
                'type' => (string) ($facts['type'] ?? ''),
                'tour' => (string) ($tour['title'] ?? $selectedTour['title'] ?? ''),
                'fit' => $fit,
                'advisory' => $advisory,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '{}');

            return EXIT_SUCCESS;
        }

        CLI::write('Type: ' . (string) ($facts['type'] ?? ''), 'cyan');
        CLI::write('Tour: ' . (string) ($tour['title'] ?? $selectedTour['title'] ?? '(none)'), 'cyan');

        if ($fit !== []) {
            CLI::write('');
            CLI::write('Fit:', 'green');
            CLI::write(trim((string) ($fit['label'] ?? '')) . ' (' . (int) ($fit['score'] ?? 0) . '%)');
            $this->writeList('Fit reasons', (array) ($fit['reasons'] ?? []));
        }

        if ($advisory === []) {
            CLI::write('No advisory profile was generated for this result.', 'yellow');

            return EXIT_SUCCESS;
        }

        CLI::write('');
        CLI::write('Summary:', 'green');
        CLI::write((string) ($advisory['summary'] ?? ''));

        $this->writeList('Matched categories', (array) ($advisory['matched_categories'] ?? []));
        $this->writeList('Matched destinations', (array) ($advisory['matched_destinations'] ?? []));
        $this->writeList('Request signals', (array) ($advisory['request_signals'] ?? []));
        $this->writeList('Strengths', (array) ($advisory['strengths'] ?? []));
        $this->writeList('Suitable for', (array) ($advisory['suitable_for'] ?? []));
        $this->writeList('Destination notes', (array) ($advisory['destination_notes'] ?? []));
        $this->writeList('Personalized notes', (array) ($advisory['personalized_notes'] ?? []));
        $this->writeKnownContext((array) ($advisory['known_context'] ?? []));
        $this->writeList('Missing information', (array) ($advisory['missing_information'] ?? []));
        $leadReadiness = trim((string) ($advisory['lead_readiness'] ?? ''));
        $recommendedCta = trim((string) ($advisory['recommended_cta'] ?? ''));
        if ($leadReadiness !== '' || $recommendedCta !== '') {
            CLI::write('');
        }
        if ($leadReadiness !== '') {
            CLI::write('Lead readiness: ' . $leadReadiness);
        }
        if ($recommendedCta !== '') {
            CLI::write('Recommended CTA: ' . $recommendedCta);
        }

        $pace = trim((string) ($advisory['pace'] ?? ''));
        $paceNote = trim((string) ($advisory['pace_note'] ?? ''));
        $budgetSegment = trim((string) ($advisory['budget_segment'] ?? ''));

        if ($pace !== '' || $paceNote !== '' || $budgetSegment !== '') {
            CLI::write('');
        }
        if ($pace !== '') {
            CLI::write('Pace: ' . $pace);
        }
        if ($paceNote !== '') {
            CLI::write('Pace note: ' . $paceNote);
        }
        if ($budgetSegment !== '') {
            CLI::write('Budget segment: ' . $budgetSegment);
        }

        $this->writeList('Budget notes', (array) ($advisory['budget_notes'] ?? []));
        $salesCaution = trim((string) ($advisory['sales_caution'] ?? ''));
        if ($salesCaution !== '') {
            CLI::write('Sales caution: ' . $salesCaution);
        }

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

    /**
     * @param array<string, mixed> $knownContext
     */
    private function writeKnownContext(array $knownContext): void
    {
        if ($knownContext === []) {
            return;
        }

        CLI::write('');
        CLI::write('Known context:', 'green');

        foreach ($knownContext as $key => $value) {
            CLI::write('- ' . $key . ': ' . ($value ? 'yes' : 'no'));
        }
    }
}
