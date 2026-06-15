<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\EmailTemplateService;
use App\Services\SeoService;
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

        $rules = [
            'name' => 'required|min_length[2]|max_length[120]',
            'email' => 'required|valid_email|max_length[160]',
            'phone' => 'required|min_length[8]|max_length[30]',
            'destination' => 'permit_empty|max_length[160]',
            'message' => 'required|min_length[10]|max_length[5000]',
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

        $emailConfig = config(EmailConfig::class);
        $recipient = trim((string) env('booking.notifyEmail', $emailConfig->recipients));
        $fromEmail = trim((string) $emailConfig->fromEmail);
        $fromName = trim((string) ($emailConfig->fromName ?: 'Travel Plus'));

        if ($recipient === '' || $fromEmail === '') {
            return $this->redirectWithMessage($locale, $redirectTarget, 'error', lang('Frontend.contact.smtpMissing', [], $locale), true);
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $phone = trim((string) $this->request->getPost('phone'));
        $destination = trim((string) $this->request->getPost('destination'));
        $message = trim((string) $this->request->getPost('message'));

        $mailer = service('email');
        $mailer->clear(true);
        $mailer->setFrom($fromEmail, $fromName);
        $mailer->setTo($recipient);
        $mailer->setReplyTo($email, $name);
        $mailer->setSubject(lang('Frontend.contact.mailSubject', [], $locale));
        $mailer->setMessage($this->buildMailBody([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'destination' => $destination,
            'message' => $message,
        ], $locale));

        if (! $mailer->send()) {
            log_message('error', 'Contact form email failed: {debug}', ['debug' => print_r($mailer->printDebugger(['headers']), true)]);

            return $this->redirectWithMessage($locale, $redirectTarget, 'error', lang('Frontend.contact.sendFailed', [], $locale), true);
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
     * @param array{name:string,email:string,phone:string,destination:string,message:string} $payload
     */
    private function buildMailBody(array $payload, string $locale): string
    {
        $destination = $payload['destination'] !== ''
            ? $payload['destination']
            : lang('Frontend.contact.mailUnknownDestination', [], $locale);

        return (new EmailTemplateService())->render(
            'Yêu cầu liên hệ',
            lang('Frontend.contact.mailHeading', [], $locale),
            'Khách vừa gửi yêu cầu tư vấn từ trang liên hệ Travel Plus. Vui lòng kiểm tra nhu cầu và phản hồi sớm.',
            [
                lang('Frontend.contact.name', [], $locale) => $payload['name'],
                lang('Frontend.contact.destination', [], $locale) => $destination,
            ],
            [
                ['label' => lang('Frontend.contact.email', [], $locale), 'value' => $payload['email']],
                ['label' => lang('Frontend.contact.phone', [], $locale), 'value' => $payload['phone']],
            ],
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
