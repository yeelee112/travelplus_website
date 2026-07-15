<?php

namespace App\Commands;

use App\Services\BookingReminderDispatchService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SendBookingReminders extends BaseCommand
{
    protected $group = 'TravelPlus';
    protected $name = 'booking:send-reminders';
    protected $description = 'Send automated booking payment and document reminder emails.';
    protected $usage = 'booking:send-reminders [--type payment|document|all] [--limit 50] [--dry-run]';
    protected $options = [
        '--type' => 'Reminder type: payment, document, or all. Default: all.',
        '--limit' => 'Maximum bookings to process per reminder type. Default: 50.',
        '--dry-run' => 'Show candidates without sending email.',
    ];

    public function run(array $params)
    {
        $type = strtolower((string) (CLI::getOption('type') ?? 'all'));
        $limit = max(1, min(200, (int) (CLI::getOption('limit') ?? 50)));
        $dryRun = CLI::getOption('dry-run') !== null;

        if (! in_array($type, ['all', 'payment', 'document'], true)) {
            CLI::error('Invalid --type. Use payment, document, or all.');
            return EXIT_ERROR;
        }

        $result = (new BookingReminderDispatchService())->dispatch($type, $limit, $dryRun);

        foreach ($result['errors'] as $error) {
            CLI::error($error);
        }

        foreach ($result['candidates'] as $candidate) {
            CLI::write(sprintf(
                '[%s] %s -> %s',
                $candidate['type'],
                $candidate['booking_code'],
                $candidate['email']
            ));
        }

        CLI::write(sprintf(
            'Done. Sent: %d. Skipped: %d.%s',
            $result['sent'],
            $result['skipped'],
            $dryRun ? ' Dry run only.' : ''
        ), $result['errors'] === [] ? 'green' : 'yellow');

        return $result['errors'] === [] ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
