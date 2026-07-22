<?php

namespace App\Services;

use App\Models\CrmLeadModel;

class CrmLeadCaptureService
{
    private const STAGES = ['new', 'consulting', 'won', 'lost'];

    /**
     * @param array<string, mixed> $data
     */
    public function capture(array $data): void
    {
        $db = db_connect();

        if (! (new DatabaseSchemaCacheService($db))->tableExists('crm_leads')) {
            return;
        }

        $source = $this->nullableString((string) ($data['source'] ?? '')) ?? 'manual';
        $stage = in_array((string) ($data['stage'] ?? ''), self::STAGES, true) ? (string) $data['stage'] : 'new';
        $email = strtolower(trim((string) ($data['customer_email'] ?? $data['email'] ?? '')));
        $phone = VietnamPhoneService::normalize((string) ($data['customer_phone'] ?? $data['phone'] ?? ''));
        $bookingId = (int) ($data['booking_id'] ?? 0);
        $bookingCode = trim((string) ($data['booking_code'] ?? ''));

        if ($email === '' && $phone === '' && $bookingId <= 0 && $bookingCode === '') {
            return;
        }

        $payload = [
            'source' => $source,
            'stage' => $stage,
            'priority' => $this->normalizePriority((string) ($data['priority'] ?? 'normal')),
            'customer_name' => $this->nullableString((string) ($data['customer_name'] ?? $data['full_name'] ?? $data['name'] ?? '')),
            'customer_email' => $email !== '' ? $email : null,
            'customer_phone' => $phone !== '' ? $phone : null,
            'service_type' => $this->nullableString((string) ($data['service_type'] ?? '')),
            'interest_title' => $this->nullableString((string) ($data['interest_title'] ?? $data['tour_title'] ?? '')),
            'interest_url' => $this->nullableString((string) ($data['interest_url'] ?? $data['tour_link'] ?? $data['page_url'] ?? '')),
            'destination' => $this->nullableString((string) ($data['destination'] ?? '')),
            'travel_date' => $this->nullableString((string) ($data['travel_date'] ?? $data['estimated_time'] ?? '')),
            'travelers' => $this->nullableString((string) ($data['travelers'] ?? '')),
            'budget' => $this->nullableString((string) ($data['budget'] ?? '')),
            'message' => $this->nullableString((string) ($data['message'] ?? '')),
            'booking_id' => $bookingId > 0 ? $bookingId : null,
            'booking_code' => $bookingCode !== '' ? $bookingCode : null,
            'metadata' => $this->encodeMetadata($data['metadata'] ?? []),
        ];

        $model = new CrmLeadModel();
        $existing = $this->findExistingLead(
            $model,
            $source,
            (string) ($payload['service_type'] ?? ''),
            (string) ($payload['interest_url'] ?? ''),
            (string) ($payload['interest_title'] ?? ''),
            $email,
            $phone,
            $bookingId,
            $bookingCode
        );

        if (is_array($existing)) {
            $payload['stage'] = $this->resolveStage((string) ($existing['stage'] ?? 'new'), $stage);
            $payload['internal_note'] = $existing['internal_note'] ?? null;
            $payload = $this->preserveExistingUsefulValues($payload, $existing);
            $model->update((int) $existing['id'], $payload);

            return;
        }

        $model->insert($payload);
    }

    /**
     * @param array<string, mixed> $booking
     */
    public function captureBooking(array $booking): void
    {
        $status = strtolower((string) ($booking['payment_status'] ?? ''));

        if ($status === 'paid') {
            $stage = 'won';
            $priority = 'normal';
        } elseif (in_array($status, ['pending_payment', 'pending_transfer'], true)) {
            $stage = 'new';
            $priority = 'high';
        } elseif (in_array($status, ['cancelled', 'failed'], true)) {
            $stage = 'lost';
            $priority = 'normal';
        } else {
            $stage = 'new';
            $priority = 'normal';
        }

        $this->capture([
            'source' => 'booking',
            'stage' => $stage,
            'priority' => $priority,
            'customer_name' => $booking['customer_name'] ?? '',
            'customer_email' => $booking['customer_email'] ?? '',
            'customer_phone' => $booking['customer_phone'] ?? '',
            'service_type' => 'tour',
            'interest_title' => $booking['tour_title'] ?? '',
            'interest_url' => $booking['tour_link'] ?? '',
            'travel_date' => $booking['departure_label'] ?? '',
            'travelers' => $this->formatTravelers($booking),
            'message' => $booking['customer_note'] ?? '',
            'booking_id' => $booking['id'] ?? null,
            'booking_code' => $booking['booking_code'] ?? '',
            'metadata' => [
                'payment_status' => $booking['payment_status'] ?? '',
                'payment_method' => $booking['payment_method'] ?? '',
                'amount_due_vnd' => $booking['amount_due_vnd'] ?? null,
                'grand_total' => $booking['grand_total'] ?? null,
            ],
        ]);
    }

    /**
     * @return array{name:string,email:string,phone:string}
     */
    public function extractContactFromText(string $text): array
    {
        $email = '';
        $phone = '';

        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $text, $emailMatch) === 1) {
            $email = strtolower($emailMatch[0]);
        }

        if (preg_match('/(?:\+?84|0)(?:[\s.\-]?\d){8,10}/', $text, $phoneMatch) === 1) {
            $phone = VietnamPhoneService::normalize($phoneMatch[0]);
        }

        return [
            'name' => '',
            'email' => $email,
            'phone' => $phone,
        ];
    }

    private function findExistingLead(
        CrmLeadModel $model,
        string $source,
        string $serviceType,
        string $interestUrl,
        string $interestTitle,
        string $email,
        string $phone,
        int $bookingId,
        string $bookingCode
    ): ?array {
        if ($bookingId > 0) {
            $lead = $model->where('booking_id', $bookingId)->first();

            if (is_array($lead)) {
                return $lead;
            }
        }

        if ($bookingCode !== '') {
            $lead = $model->where('booking_code', $bookingCode)->first();

            if (is_array($lead)) {
                return $lead;
            }
        }

        if ($email === '' && $phone === '') {
            return null;
        }

        $model->groupStart();
        if ($email !== '') {
            $model->where('customer_email', $email);
        }
        if ($phone !== '') {
            if ($email !== '') {
                $model->orWhere('customer_phone', $phone);
            } else {
                $model->where('customer_phone', $phone);
            }
        }
        $model->groupEnd()
            ->where('source', $source)
            ->where('updated_at >=', date('Y-m-d H:i:s', time() - 172800));

        if ($serviceType !== '') {
            $model->where('service_type', $serviceType);
        }
        if ($interestUrl !== '') {
            $model->where('interest_url', $interestUrl);
        } elseif ($interestTitle !== '') {
            $model->where('interest_title', $interestTitle);
        }

        $lead = $model->orderBy('updated_at', 'DESC')->first();

        return is_array($lead) ? $lead : null;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $existing
     * @return array<string, mixed>
     */
    private function preserveExistingUsefulValues(array $payload, array $existing): array
    {
        foreach (['customer_name', 'customer_email', 'customer_phone', 'service_type', 'interest_title', 'interest_url', 'destination', 'travel_date', 'travelers', 'budget', 'message', 'booking_id', 'booking_code'] as $field) {
            if (($payload[$field] ?? null) === null || $payload[$field] === '') {
                $payload[$field] = $existing[$field] ?? null;
            }
        }

        return $payload;
    }

    private function resolveStage(string $existingStage, string $incomingStage): string
    {
        if ($incomingStage === 'won') {
            return 'won';
        }

        if ($existingStage === 'won') {
            return 'won';
        }

        if ($incomingStage === 'lost') {
            return $existingStage === 'consulting' ? 'consulting' : 'lost';
        }

        return $existingStage !== '' ? $existingStage : $incomingStage;
    }

    private function normalizePriority(string $priority): string
    {
        return in_array($priority, ['low', 'normal', 'high'], true) ? $priority : 'normal';
    }

    private function nullableString(string $value): ?string
    {
        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function encodeMetadata(mixed $metadata): ?string
    {
        if (! is_array($metadata) || $metadata === []) {
            return null;
        }

        $json = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $json !== false ? $json : null;
    }

    /**
     * @param array<string, mixed> $booking
     */
    private function formatTravelers(array $booking): string
    {
        $adult = (int) ($booking['adult_quantity'] ?? 0);
        $child = (int) ($booking['child_quantity'] ?? 0);
        $infant = (int) ($booking['infant_quantity'] ?? 0);

        return trim($adult . ' NL / ' . $child . ' TE / ' . $infant . ' EB');
    }
}
