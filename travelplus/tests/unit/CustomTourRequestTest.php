<?php

use App\Controllers\Contact;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/** @internal */
final class CustomTourRequestTest extends CIUnitTestCase
{
    private $previousSecret;
    private string $previousFrom;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'form', 'url_helper_custom']);
        $this->db = db_connect('tests');
        $this->assertSame(':memory:', $this->db->database);
        require_once APPPATH . 'Database/Migrations/20260614_add_crm_leads.php';
        (new \App\Database\Migrations\AddCrmLeads())->up();
        $this->db->table('crm_leads')->emptyTable();
        $this->previousSecret = $_ENV['recaptcha.secretKey'] ?? null;
        $_ENV['recaptcha.secretKey'] = 'test-secret';
        $this->previousFrom = config('Email')->fromEmail;
        config('Email')->fromEmail = '';
        $http = $this->createMock(CURLRequest::class);
        $http->method('post')->willReturn(service('response')->setJSON(['success' => true, 'action' => 'contact', 'score' => 0.9]));
        Services::injectMock('curlrequest', $http);
    }

    protected function tearDown(): void
    {
        if ($this->previousSecret === null) unset($_ENV['recaptcha.secretKey']);
        else $_ENV['recaptcha.secretKey'] = $this->previousSecret;
        config('Email')->fromEmail = $this->previousFrom;
        parent::tearDown();
    }

    private function submit(array $overrides = []): void
    {
        service('validation')->reset();
        $request = service('incomingrequest');
        $request->setMethod('POST');
        $request->setLocale('vi');
        $request->setGlobal('post', array_replace([
            'contact_form_token' => 'unit-token', 'name' => 'Khách kiểm thử',
            'phone' => '0901234567', 'email' => '', 'adults' => '2', 'children' => '1',
            'child_ages' => '8 tuổi', 'destination' => 'Đà Nẵng', 'departure' => 'Hà Nội',
            'interests' => 'family', 'reference_tour' => 'Tour biển', 'budget' => '5–10 triệu',
            'privacy_agree' => '1', 'recaptcha_token' => 'test-token',
        ], $overrides));
        Services::injectMock('request', $request);
        session()->set('custom_tour_token', 'unit-token');
        session()->remove(['success', 'error']);
        // Isolate submission from unrelated navigation and visitor tracking.
        $controller = new class extends Contact {
            public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
            {
                $this->request = $request;
                $this->response = $response;
                $this->logger = $logger;
            }
        };
        $controller->initController($request, service('response'), service('logger'));
        $controller->customTour();
    }

    public function testOptionalEmailAndNotesSaveCompleteRequest(): void
    {
        $this->submit();
        $lead = $this->db->table('crm_leads')->get()->getRowArray();
        $this->assertNotNull($lead, (string) session()->getFlashdata('error'));
        $this->assertSame('custom_tour', $lead['source']);
        $this->assertNull($lead['customer_email']);
        $this->assertStringContainsString('Hà Nội', $lead['message']);
        $this->assertStringContainsString('8 tuổi', $lead['message']);
        $this->assertStringContainsString('Tour biển', $lead['message']);
        $this->assertNotNull(session()->getFlashdata('success'));
    }

    public function testNewTripDoesNotOverwriteEarlierTripFromSameCustomer(): void
    {
        $this->submit();
        $this->submit(['destination' => 'Nhật Bản']);
        $this->assertSame(2, $this->db->table('crm_leads')->countAllResults());
    }

    public function testInvalidGuestCountIsRejected(): void
    {
        $this->submit(['adults' => '0']);
        $this->assertSame(0, $this->db->table('crm_leads')->countAllResults());
        $this->assertNotNull(session()->getFlashdata('error'));
    }

    public function testInvalidFormTokenIsRejected(): void
    {
        $this->submit(['contact_form_token' => 'wrong-token']);
        $this->assertSame(0, $this->db->table('crm_leads')->countAllResults());
        $this->assertNotNull(session()->getFlashdata('error'));
    }

    public function testConsentMustBeAffirmative(): void
    {
        $this->submit(['privacy_agree' => '0']);
        $this->assertSame(0, $this->db->table('crm_leads')->countAllResults());
    }

    public function testInvalidCaptchaDoesNotSaveRequest(): void
    {
        $http = $this->createMock(CURLRequest::class);
        $http->method('post')->willReturn(service('response')->setJSON(['success' => false]));
        Services::injectMock('curlrequest', $http);
        $this->submit();
        $this->assertSame(0, $this->db->table('crm_leads')->countAllResults());
        $this->assertNotNull(session()->getFlashdata('error'));
    }

    public function testStorageFailureDoesNotClaimSuccess(): void
    {
        \Config\Database::forge('tests')->dropTable('crm_leads');
        $this->submit();
        $this->assertNull(session()->getFlashdata('success'));
        $this->assertStringContainsString('Chưa lưu được', (string) session()->getFlashdata('error'));
    }
}
