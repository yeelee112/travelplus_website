<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\CrmLeadCaptureService;
use App\Services\EmailTemplateService;
use App\Services\SeoService;
use App\Services\VietnamPhoneService;
use Config\Email as EmailConfig;

class Contact extends BaseController
{
    public function index()
    {
        if ($this->request->getMethod() === 'POST') {
            return $this->submitContactForm();
        }

        return view('contact/index', $this->buildPageData());
    }

    private function submitContactForm()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $redirectTarget = $this->resolveRedirectTarget();
        $postedToken = (string) $this->request->getPost('contact_form_token');
        $sessionToken = (string) session()->get('contact_form_token');

        if ($postedToken === '' || $sessionToken === '' || ! hash_equals($sessionToken, $postedToken)) {
            return $this->redirectWithMessage($locale, $redirectTarget, 'error', lang('Frontend.contact.invalidToken', [], $locale), true);
        }

        session()->remove('contact_form_token');
        $serviceType = strtolower(trim((string) $this->request->getPost('service_type')));
        $isVisaRequest = $serviceType === 'visa';
        $isMiceRequest = $serviceType === 'mice';
        $isSpecializedRequest = $isVisaRequest || $isMiceRequest;

        $rules = [
            'service_type' => 'permit_empty|in_list[visa,mice]',
            'company_name' => 'permit_empty|max_length[160]',
            'event_type' => 'permit_empty|max_length[160]',
            'conference_name' => 'permit_empty|max_length[180]',
            'name' => 'required|min_length[2]|max_length[120]',
            'email' => 'required|valid_email|max_length[160]',
            'phone' => 'required|validVietnamPhone|max_length[30]',
            'destination' => 'permit_empty|max_length[160]',
            'visa_type' => 'permit_empty|max_length[120]',
            'visa_refusal' => 'permit_empty|max_length[120]',
            'budget' => 'permit_empty|max_length[160]',
            'travelers' => 'permit_empty|max_length[80]',
            'estimated_time' => 'permit_empty|max_length[120]',
            'trip_length' => 'permit_empty|max_length[120]',
            'hotel_rating' => 'permit_empty|max_length[80]',
            'message' => $isSpecializedRequest ? 'permit_empty|min_length[10]|max_length[5000]' : 'required|min_length[10]|max_length[5000]',
            'privacy_agree' => 'required',
            'recaptcha_token' => 'required',
        ];

        $messages = [
            'name' => [
                'required' => lang('Frontend.contact.validation.nameRequired', [], $locale),
            ],
            'email' => [
                'required' => lang('Frontend.contact.validation.emailRequired', [], $locale),
                'valid_email' => lang('Frontend.contact.validation.emailInvalid', [], $locale),
            ],
            'phone' => [
                'required' => lang('Frontend.contact.validation.phoneRequired', [], $locale),
                'validVietnamPhone' => $locale === 'en'
                    ? 'Please enter a valid Vietnamese phone number.'
                    : 'Vui lòng nhập số điện thoại Việt Nam hợp lệ.',
            ],
            'message' => [
                'required' => lang('Frontend.contact.validation.messageRequired', [], $locale),
                'min_length' => lang('Frontend.contact.validation.messageMin', [], $locale),
            ],
            'privacy_agree' => [
                'required' => lang('Frontend.contact.validation.privacyRequired', [], $locale),
            ],
            'recaptcha_token' => [
                'required' => lang('Frontend.contact.validation.recaptchaRequired', [], $locale),
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return $this->redirectWithMessage($locale, $redirectTarget, 'error', implode("\n", $this->validator->getErrors()), true);
        }

        if (! $this->verifyRecaptcha((string) $this->request->getPost('recaptcha_token'))) {
            return $this->redirectWithMessage($locale, $redirectTarget, 'error', lang('Frontend.contact.recaptchaFailed', [], $locale), true);
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $phone = VietnamPhoneService::normalize((string) $this->request->getPost('phone'));
        $destination = trim((string) $this->request->getPost('destination'));
        $visaType = trim((string) $this->request->getPost('visa_type'));
        $visaRefusal = trim((string) $this->request->getPost('visa_refusal'));
        $travelers = trim((string) $this->request->getPost('travelers'));
        $estimatedTime = trim((string) $this->request->getPost('estimated_time'));
        $tripLength = trim((string) $this->request->getPost('trip_length'));
        $hotelRating = trim((string) $this->request->getPost('hotel_rating'));
        $message = trim((string) $this->request->getPost('message'));
        if ($isVisaRequest && $message === '') {
            $message = $locale === 'en'
                ? 'Customer sent a visa consultation request and did not add extra notes.'
                : 'Khách gửi yêu cầu tư vấn visa và chưa nhập ghi chú thêm.';
        }
        if ($isMiceRequest && $message === '') {
            $message = $locale === 'en'
                ? 'Customer sent a MICE brief request and did not add extra notes.'
                : 'Khách gửi yêu cầu nhận proposal MICE và chưa nhập brief chi tiết.';
        }
        $companyName = trim((string) $this->request->getPost('company_name'));
        $eventType = trim((string) $this->request->getPost('event_type'));
        $conferenceName = trim((string) $this->request->getPost('conference_name'));
        $budget = trim((string) $this->request->getPost('budget'));

        (new CrmLeadCaptureService())->capture([
            'source' => $isVisaRequest ? 'visa_form' : ($isMiceRequest ? 'mice_form' : 'contact_form'),
            'stage' => 'new',
            'priority' => $isMiceRequest || $isVisaRequest ? 'high' : 'normal',
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'service_type' => $serviceType !== '' ? $serviceType : 'tour',
            'interest_title' => $isVisaRequest ? 'Visa consultation' : ($isMiceRequest ? 'MICE proposal' : 'Contact request'),
            'interest_url' => $redirectTarget ?? LocalizedPathCatalog::url('contact', $locale),
            'destination' => $destination,
            'travel_date' => $estimatedTime,
            'travelers' => $travelers,
            'budget' => $budget,
            'message' => $message,
            'metadata' => [
                'locale' => $locale,
                'company_name' => $companyName,
                'event_type' => $eventType,
                'conference_name' => $conferenceName,
                'visa_type' => $visaType,
                'visa_refusal' => $visaRefusal,
                'trip_length' => $tripLength,
                'hotel_rating' => $hotelRating,
            ],
        ]);

        $emailConfig = config(EmailConfig::class);
        $recipient = trim((string) env('booking.notifyEmail', $emailConfig->recipients));
        $fromEmail = trim((string) $emailConfig->fromEmail);
        $fromName = trim((string) ($emailConfig->fromName ?: 'Travel Plus'));

        if ($recipient === '' || $fromEmail === '') {
            log_message('warning', 'Contact lead captured but email notification is not configured.');

            return $this->redirectWithMessage($locale, $redirectTarget, 'success', lang('Frontend.contact.sendSuccess', [], $locale));
        }

        $mailer = service('email');
        $mailer->clear(true);
        $mailer->setFrom($fromEmail, $fromName);
        $mailer->setTo($recipient);
        $mailer->setReplyTo($email, $name);
        $mailer->setSubject($isVisaRequest
            ? ($locale === 'en' ? 'New visa consultation request from Travel Plus website' : 'Yêu cầu tư vấn visa mới từ website Travel Plus')
            : ($isMiceRequest
                ? ($locale === 'en' ? 'New MICE brief request from Travel Plus website' : 'Yêu cầu nhận proposal MICE mới từ website Travel Plus')
                : lang('Frontend.contact.mailSubject', [], $locale)));
        $mailer->setMessage($this->buildMailBody([
            'service_type' => $serviceType,
            'company_name' => $companyName,
            'event_type' => $eventType,
            'conference_name' => $conferenceName,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'destination' => $destination,
            'visa_type' => $visaType,
            'visa_refusal' => $visaRefusal,
            'budget' => $budget,
            'travelers' => $travelers,
            'estimated_time' => $estimatedTime,
            'trip_length' => $tripLength,
            'hotel_rating' => $hotelRating,
            'message' => $message,
        ], $locale));

        if (! $mailer->send()) {
            log_message('error', 'Contact form email failed: {debug}', ['debug' => print_r($mailer->printDebugger(['headers']), true)]);

            return $this->redirectWithMessage($locale, $redirectTarget, 'success', lang('Frontend.contact.sendSuccess', [], $locale));
        }

        return $this->redirectWithMessage($locale, $redirectTarget, 'success', lang('Frontend.contact.sendSuccess', [], $locale));
    }

    private function resolveRedirectTarget(): ?string
    {
        $target = trim((string) $this->request->getPost('redirect_to'));
        if ($target === '') {
            return null;
        }

        $targetHost = parse_url($target, PHP_URL_HOST);
        $baseHost = parse_url(base_url(), PHP_URL_HOST);

        if ($targetHost !== null && $baseHost !== null && strcasecmp((string) $targetHost, (string) $baseHost) !== 0) {
            return null;
        }

        return $target;
    }

    private function redirectWithMessage(string $locale, ?string $redirectTarget, string $flashKey, string $message, bool $withInput = false)
    {
        $redirect = $redirectTarget !== null
            ? redirect()->to($redirectTarget)
            : redirect()->to(LocalizedPathCatalog::url('contact', $locale));

        if ($withInput) {
            $redirect = $redirect->withInput();
        }

        if ($flashKey === 'success') {
            $serviceType = strtolower(trim((string) $this->request->getPost('service_type')));
            $leadSource = $serviceType === 'visa' ? 'visa_form' : ($serviceType === 'mice' ? 'mice_form' : 'contact_form');
            $redirect = $redirect->with('analytics_event', [
                'name' => 'generate_lead',
                'dedupe_key' => 'contact_' . hash('sha256', (string) $this->request->getPost('contact_form_token')),
                'params' => [
                    'lead_source' => $leadSource,
                    'service_type' => $serviceType !== '' ? $serviceType : 'tour',
                ],
            ]);
        }

        return $redirect->with($flashKey, $message);
    }

    private function verifyRecaptcha(string $token): bool
    {
        $secretKey = trim((string) env('recaptcha.secretKey', ''), " \t\n\r\0\x0B\"'");

        if ($secretKey === '') {
            log_message('error', 'Contact form reCAPTCHA secret key is missing.');
            return false;
        }

        try {
            $options = ['timeout' => 10];
            $caBundle = trim((string) env('recaptcha.caBundle', ''), " \t\n\r\0\x0B\"'");

            if ($caBundle !== '' && is_file($caBundle)) {
                $options['verify'] = $caBundle;
            }

            $client = \Config\Services::curlrequest($options);

            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => $secretKey,
                    'response' => $token,
                ],
            ]);

            $result = json_decode((string) $response->getBody(), true);

            return is_array($result)
                && ! empty($result['success'])
                && (($result['score'] ?? 0) >= (float) env('recaptcha.minimumScore', 0.5));
        } catch (\Throwable $exception) {
            log_message('error', 'Contact form reCAPTCHA failed: {message}', ['message' => $exception->getMessage()]);

            return false;
        }
    }

    /**
     * @param array{service_type?:string,company_name?:string,event_type?:string,conference_name?:string,name:string,email:string,phone:string,destination:string,visa_type?:string,visa_refusal?:string,budget?:string,travelers:string,estimated_time:string,trip_length:string,hotel_rating:string,message:string} $payload
     */
    private function buildMailBody(array $payload, string $locale): string
    {
        $isVisaRequest = ($payload['service_type'] ?? '') === 'visa';
        $isMiceRequest = ($payload['service_type'] ?? '') === 'mice';
        $destination = $payload['destination'] !== ''
            ? $payload['destination']
            : lang('Frontend.contact.mailUnknownDestination', [], $locale);

        if ($isVisaRequest) {
            $labels = $locale === 'en'
                ? [
                    'destination' => 'Visa destination',
                    'visa_type' => 'Visa type',
                    'visa_refusal' => 'Previous visa refusal',
                    'travelers' => 'Number of applicants',
                    'estimated_time' => 'Expected timing',
                    'trip_length' => 'Trip length',
                    'hotel_rating' => 'Hotel standard',
                ]
                : [
                    'destination' => 'Quốc gia cần xin visa',
                    'visa_type' => 'Loại visa',
                    'visa_refusal' => 'Đã từng bị từ chối visa',
                    'travelers' => 'Số người xin visa',
                    'estimated_time' => 'Thời gian dự kiến',
                    'trip_length' => 'Thời gian đi',
                    'hotel_rating' => 'Khách sạn mong muốn',
                ];
        } elseif ($isMiceRequest) {
            $labels = $locale === 'en'
                ? [
                    'company_name' => 'Company',
                    'event_type' => 'Program type',
                    'conference_name' => 'Conference / meeting name',
                    'destination' => 'Preferred destination',
                    'travelers' => 'Guest count',
                    'estimated_time' => 'Expected timing',
                    'budget' => 'Reference budget',
                    'trip_length' => 'Trip length',
                    'hotel_rating' => 'Hotel standard',
                ]
                : [
                    'company_name' => 'Tên công ty',
                    'event_type' => 'Loại chương trình',
                    'conference_name' => 'Tên hội nghị/hội thảo',
                    'destination' => 'Điểm đến mong muốn',
                    'travelers' => 'Số lượng khách',
                    'estimated_time' => 'Thời gian dự kiến',
                    'budget' => 'Ngân sách tham khảo',
                    'trip_length' => 'Thời gian đi',
                    'hotel_rating' => 'Khách sạn mong muốn',
                ];
        } else {
            $labels = $locale === 'en'
                ? [
                'travelers' => 'Group size',
                'estimated_time' => 'Preferred travel period',
                'trip_length' => 'Trip length',
                'hotel_rating' => 'Hotel standard',
                ]
                : [
                'travelers' => 'Số lượng khách',
                'estimated_time' => 'Thoi gian du kien',
                'trip_length' => 'Thoi gian di',
                'hotel_rating' => 'Khach san mong muon',
                ];
        }

        $details = [
            ['label' => lang('Frontend.contact.email', [], $locale), 'value' => $payload['email']],
            ['label' => lang('Frontend.contact.phone', [], $locale), 'value' => $payload['phone']],
        ];

        if ($isVisaRequest) {
            $optionalDetails = [
            'visa_type' => $payload['visa_type'] ?? '',
            'visa_refusal' => $payload['visa_refusal'] ?? '',
            'travelers' => $payload['travelers'],
            'estimated_time' => $payload['estimated_time'],
            'trip_length' => $payload['trip_length'],
            'hotel_rating' => $payload['hotel_rating'],
            ];
        } elseif ($isMiceRequest) {
            $optionalDetails = [
                'company_name' => $payload['company_name'] ?? '',
                'event_type' => $payload['event_type'] ?? '',
                'conference_name' => $payload['conference_name'] ?? '',
                'travelers' => $payload['travelers'],
                'estimated_time' => $payload['estimated_time'],
                'budget' => $payload['budget'] ?? '',
            ];
        } else {
            $optionalDetails = [
            'travelers' => $payload['travelers'],
            'estimated_time' => $payload['estimated_time'],
            'trip_length' => $payload['trip_length'],
            'hotel_rating' => $payload['hotel_rating'],
            ];
        }

        foreach ($optionalDetails as $key => $value) {
            if ($value === '') {
                continue;
            }

            $details[] = [
                'label' => $labels[$key],
                'value' => $value,
            ];
        }

        return (new EmailTemplateService())->render(
            $isVisaRequest ? 'Yêu cầu tư vấn visa' : ($isMiceRequest ? 'Yêu cầu proposal MICE' : 'Yêu cầu liên hệ'),
            $isVisaRequest
                ? ($locale === 'en' ? 'New visa consultation request from Travel Plus website' : 'Yêu cầu tư vấn visa mới từ website Travel Plus')
                : ($isMiceRequest
                    ? ($locale === 'en' ? 'New MICE brief request from Travel Plus website' : 'Yêu cầu nhận proposal MICE mới từ website Travel Plus')
                    : lang('Frontend.contact.mailHeading', [], $locale)),
            $isVisaRequest
                ? ($locale === 'en'
                    ? 'A customer has submitted initial visa file information. Please review the destination, visa type and document status before advising.'
                    : 'Khách vừa gửi thông tin hồ sơ visa ban đầu. Vui lòng kiểm tra quốc gia, loại visa và tình trạng hồ sơ để tư vấn sớm.')
                : ($isMiceRequest
                    ? ($locale === 'en'
                        ? 'A company has submitted a MICE brief request. Please review the program type, guest count, timing and budget before proposing.'
                        : 'Doanh nghiệp vừa gửi brief MICE. Vui lòng kiểm tra loại chương trình, số khách, thời gian và ngân sách để phản hồi proposal.')
                    : 'Khách vừa gửi yêu cầu tư vấn từ trang liên hệ Travel Plus. Vui lòng kiểm tra nhu cầu và phản hồi sớm.'),
            [
                lang('Frontend.contact.name', [], $locale) => $payload['name'],
                ($isVisaRequest || $isMiceRequest ? $labels['destination'] : lang('Frontend.contact.destination', [], $locale)) => $destination,
            ],
            $details,
            $payload['message'],
            'Mở website',
            LocalizedPathCatalog::url('contact', $locale)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPageData(): array
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $seo = new SeoService();
        $formToken = bin2hex(random_bytes(16));
        session()->set('contact_form_token', $formToken);

        $breadcrumbs = [
            [
                'label' => lang('Frontend.common.home', [], $locale),
                'url' => localized_url('/'),
            ],
            [
                'label' => lang('Frontend.contact.breadcrumb', [], $locale),
            ],
        ];

        $metaTitle = lang('Frontend.contact.metaTitle', [], $locale);
        $metaDesc = lang('Frontend.contact.metaDesc', [], $locale);
        $serviceDescription = $locale === 'en'
            ? 'Contact Travel Plus for outbound tours, domestic tours, visa consultation, corporate MICE programs, incentive travel and tailor-made itineraries.'
            : 'Liên hệ Travel Plus để tư vấn tour nước ngoài, tour trong nước, visa, MICE doanh nghiệp, incentive travel và lịch trình thiết kế riêng.';

        return [
            'breadcrumbs' => $breadcrumbs,
            'meta_title' => $metaTitle,
            'meta_desc' => $metaDesc,
            'meta_image' => base_url('assets/images/TravelPlus_CompanyProfile.png'),
            'meta_image_alt' => $metaTitle,
            'canonical_url' => LocalizedPathCatalog::url('contact', $locale),
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => base_url('contact')],
                ['hreflang' => 'en', 'href' => base_url('en/contact')],
                ['hreflang' => 'x-default', 'href' => base_url('contact')],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, LocalizedPathCatalog::url('contact', $locale)),
                $seo->webpageSchema($metaTitle, $metaDesc, LocalizedPathCatalog::url('contact', $locale), 'ContactPage'),
                $seo->serviceSchema(
                    'Travel Plus Consultation',
                    $serviceDescription,
                    LocalizedPathCatalog::url('contact', $locale),
                    base_url('assets/images/TravelPlus_CompanyProfile.png'),
                    ['Tours', 'Visa consultation', 'MICE', 'Incentive travel', 'Tailor-made travel']
                ),
            ],
            'contact_form_token' => $formToken,
        ];
    }
}
