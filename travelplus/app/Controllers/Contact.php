<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\CrmLeadCaptureService;
use App\Services\EmailTemplateService;
use App\Services\RecaptchaService;
use App\Services\SeoService;
use App\Services\VietnamPhoneService;
use Config\Email as EmailConfig;

class Contact extends BaseController
{
    public function customTour()
    {
        if ($this->request->getMethod() === 'POST') {
            return $this->submitContactForm(true);
        }
        $locale = $this->request->getLocale() ?: 'vi';
        $token = bin2hex(random_bytes(16));
        session()->set('custom_tour_token', $token);
        $title = $locale === 'en' ? 'Tailor-made Tours & Private Travel' : 'Thiết kế tour theo yêu cầu, du lịch riêng';
        $description = $locale === 'en'
            ? 'Plan a tailor-made tour with TravelPlus. Share your destination, dates and budget for a private itinerary for your family, friends or company.'
            : 'Thiết kế tour theo yêu cầu cùng TravelPlus. Lịch trình riêng cho gia đình, nhóm bạn, doanh nghiệp; linh hoạt ngày đi và ngân sách. Gửi nhu cầu để nhận tư vấn.';
        $url = LocalizedPathCatalog::url('customTour', $locale);
        $image = base_url('assets/images/tailor-made/img-01.png');
        $faqs = \App\Data\CustomTourContent::faqs($locale);
        $seo = new SeoService();
        $breadcrumbs = [
            ['label' => $locale === 'en' ? 'Home' : 'Trang chủ', 'url' => localized_url('/')],
            ['label' => $locale === 'en' ? 'Tailor-made tours' : 'Tour theo yêu cầu'],
        ];
        return view('custom-tour/index', [
            'breadcrumbs' => $breadcrumbs,
            'faqs' => $faqs,
            'contact_form_token' => $token,
            'meta_title' => $title . ' | Travel Plus',
            'meta_desc' => $description,
            'meta_image' => $image,
            'meta_image_alt' => $locale === 'en' ? 'Planning a private journey overlooking a sunlit bay' : 'Lên kế hoạch du lịch riêng bên vịnh biển trong ánh hoàng hôn',
            'canonical_url' => $url,
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => base_url('tour-theo-yeu-cau')],
                ['hreflang' => 'en', 'href' => base_url('en/custom-tours')],
                ['hreflang' => 'x-default', 'href' => base_url('tour-theo-yeu-cau')],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->webpageSchema($title, $description, $url),
                $seo->breadcrumbSchema($breadcrumbs, $url),
                $seo->serviceSchema($title, $description, $url, $image, [$locale === 'en' ? 'Tailor-made tours' : 'Thiết kế tour theo yêu cầu']),
                $seo->faqSchema($faqs),
            ],
        ]);
    }

    public function index()
    {
        if ($this->request->getMethod() === 'POST') {
            return $this->submitContactForm();
        }

        return view('contact/index', $this->buildPageData());
    }

    private function submitContactForm(bool $isCustomRequest = false)
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $redirectTarget = $isCustomRequest ? LocalizedPathCatalog::url('customTour', $locale) : $this->resolveRedirectTarget();
        $tokenKey = $isCustomRequest ? 'custom_tour_token' : 'contact_form_token';
        $postedToken = (string) $this->request->getPost('contact_form_token');
        $sessionToken = (string) session()->get($tokenKey);

        if ($postedToken === '' || $sessionToken === '' || ! hash_equals($sessionToken, $postedToken)) {
            return $this->redirectWithMessage($locale, $redirectTarget, 'error', lang('Frontend.contact.invalidToken', [], $locale), true);
        }

        session()->remove($tokenKey);
        $serviceType = $isCustomRequest ? '' : strtolower(trim((string) $this->request->getPost('service_type')));
        $leadContext = $isCustomRequest ? '' : strtolower(trim((string) $this->request->getPost('lead_context')));
        $isVisaRequest = $serviceType === 'visa';
        $isMiceRequest = $serviceType === 'mice';
        $isInboundRequest = $leadContext === 'inbound';
        $isSpecializedRequest = $isVisaRequest || $isMiceRequest;

        $rules = [
            'service_type' => 'permit_empty|in_list[visa,mice]',
            'lead_context' => 'permit_empty|in_list[summer,inbound]',
            'company_name' => 'permit_empty|max_length[160]',
            'event_type' => 'permit_empty|max_length[160]',
            'conference_name' => 'permit_empty|max_length[180]',
            'name' => 'required|min_length[2]|max_length[120]',
            'email' => 'required|valid_email|max_length[160]',
            'phone' => $isInboundRequest ? 'required|min_length[6]|max_length[30]' : 'required|validVietnamPhone|max_length[30]',
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
        if ($isCustomRequest) {
            $rules['estimated_time'] = 'permit_empty|max_length[80]';
            $rules['email'] = 'permit_empty|valid_email|max_length[160]';
            $rules['message'] = 'permit_empty|max_length[3000]';
            $rules['privacy_agree'] = 'required|in_list[1]';
            $rules += [
                'departure' => 'permit_empty|max_length[160]',
                'adults' => 'required|is_natural_no_zero|less_than_equal_to[1000]',
                'children' => 'required|is_natural|less_than_equal_to[1000]',
                'child_ages' => 'permit_empty|max_length[160]',
                'interests' => 'permit_empty|in_list[relax,explore,food,team,family]',
                'reference_tour' => 'permit_empty|max_length[250]',
            ];
        }

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

        $valid = $isCustomRequest
            ? $this->validateData($this->request->getPost(), $rules, $messages)
            : $this->validate($rules, $messages);
        if (! $valid) {
            return $this->redirectWithMessage($locale, $redirectTarget, 'error', implode("\n", $this->validator->getErrors()), true);
        }

        if (! (new RecaptchaService())->verify(
            (string) $this->request->getPost('recaptcha_token'),
            'contact',
            (string) $this->request->getIPAddress()
        )) {
            return $this->redirectWithMessage($locale, $redirectTarget, 'error', lang('Frontend.contact.recaptchaFailed', [], $locale), true);
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $phone = $isInboundRequest
            ? trim((string) $this->request->getPost('phone'))
            : VietnamPhoneService::normalize((string) $this->request->getPost('phone'));
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

        if ($isCustomRequest) {
            $travelers = (int) $this->request->getPost('adults') . ' người lớn, ' . (int) $this->request->getPost('children') . ' trẻ em';
            $preferences = ['relax' => 'Nghỉ dưỡng', 'explore' => 'Khám phá', 'food' => 'Ẩm thực', 'team' => 'Team building', 'family' => 'Gia đình'];
            $message = implode("\n", [
                'Tour theo yêu cầu',
                'Nơi khởi hành: ' . trim((string) $this->request->getPost('departure')),
                'Thời lượng chuyến đi: ' . ($tripLength ?: 'Cần tư vấn'),
                'Số khách: ' . $travelers,
                'Độ tuổi trẻ em: ' . ((int) $this->request->getPost('children') > 0 ? trim((string) $this->request->getPost('child_ages')) : 'Không có'),
                'Sở thích: ' . ($preferences[(string) $this->request->getPost('interests')] ?? 'Cần tư vấn'),
                'Ngân sách mỗi người (VND): ' . ($budget ?: 'Cần tư vấn'),
                'Tour tham khảo: ' . trim((string) $this->request->getPost('reference_tour')),
                'Ghi chú: ' . $message,
            ]);
        }

        $leadSource = $isVisaRequest
            ? 'visa_form'
            : ($isMiceRequest ? 'mice_form' : ($leadContext === 'summer' ? 'summer_form' : ($isInboundRequest ? 'inbound_landing' : 'contact_form')));
        if ($isCustomRequest) { $leadSource = 'custom_tour'; }
        try {
            $leadId = (new CrmLeadCaptureService())->capture([
                'deduplicate' => ! $isCustomRequest,
                'source' => $leadSource,
                'stage' => 'new',
                'priority' => $isMiceRequest || $isVisaRequest || $isInboundRequest ? 'high' : 'normal',
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $phone,
                'service_type' => $serviceType !== '' ? $serviceType : 'tour',
                'interest_title' => $isCustomRequest ? 'Tour theo yêu cầu' : ($isVisaRequest
                    ? 'Visa consultation'
                    : ($isMiceRequest ? 'MICE proposal' : ($leadContext === 'summer' ? 'Summer tour request' : ($isInboundRequest ? 'Vietnam & Indochina trip request' : 'Contact request')))),
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
                    'lead_context' => $leadContext,
                ],
            ]);
        } catch (\Throwable $exception) {
            if (! $isCustomRequest) { throw $exception; }
            log_message('error', 'Custom tour request could not be saved: {message}', ['message' => $exception->getMessage()]);
            $leadId = false;
        }
        if ($isCustomRequest && $leadId === false) {
            return $this->redirectWithMessage($locale, $redirectTarget, 'error', $locale === 'en' ? 'We could not save your request. Please try again or contact us by phone.' : 'Chưa lưu được yêu cầu. Vui lòng thử lại hoặc liên hệ hotline.', true);
        }
        $analyticsEvent = $leadId !== false ? [
            'name' => 'generate_lead',
            'dedupe_key' => 'crm_lead_' . $leadId,
            'params' => [
                'lead_source' => $leadSource,
                'service_type' => $serviceType !== '' ? $serviceType : 'tour',
            ],
        ] : null;

        $emailConfig = config(EmailConfig::class);
        $recipient = trim((string) env('booking.notifyEmail', $emailConfig->recipients));
        $fromEmail = trim((string) $emailConfig->fromEmail);
        $fromName = trim((string) ($emailConfig->fromName ?: 'Travel Plus'));

        if ($recipient === '' || $fromEmail === '') {
            log_message('warning', 'Contact lead captured but email notification is not configured.');

            return $this->redirectWithMessage($locale, $redirectTarget, 'success', lang('Frontend.contact.sendSuccess', [], $locale), false, $analyticsEvent);
        }

        $mailer = service('email');
        $mailer->clear(true);
        $mailer->setFrom($fromEmail, $fromName);
        $mailer->setTo($recipient);
        if ($email !== '') { $mailer->setReplyTo($email, $name); }
        $mailer->setSubject($isVisaRequest
            ? ($locale === 'en' ? 'New visa consultation request from Travel Plus website' : 'Yêu cầu tư vấn visa mới từ website Travel Plus')
            : ($isMiceRequest
                ? ($locale === 'en' ? 'New MICE brief request from Travel Plus website' : 'Yêu cầu nhận proposal MICE mới từ website Travel Plus')
                : ($isInboundRequest ? 'New Vietnam & Indochina trip request from Travel Plus website' : lang('Frontend.contact.mailSubject', [], $locale))));
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

            return $this->redirectWithMessage($locale, $redirectTarget, 'success', lang('Frontend.contact.sendSuccess', [], $locale), false, $analyticsEvent);
        }

        return $this->redirectWithMessage($locale, $redirectTarget, 'success', lang('Frontend.contact.sendSuccess', [], $locale), false, $analyticsEvent);
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

    private function redirectWithMessage(
        string $locale,
        ?string $redirectTarget,
        string $flashKey,
        string $message,
        bool $withInput = false,
        ?array $analyticsEvent = null
    )
    {
        $redirect = $redirectTarget !== null
            ? redirect()->to($redirectTarget)
            : redirect()->to(LocalizedPathCatalog::url('contact', $locale));

        if ($withInput) {
            $redirect = $redirect->withInput();
        }

        if ($flashKey === 'success' && $analyticsEvent !== null) {
            $redirect = $redirect->with('analytics_event', $analyticsEvent);
        }

        return $redirect->with($flashKey, $message);
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
