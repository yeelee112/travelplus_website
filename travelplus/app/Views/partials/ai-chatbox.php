<?php
$currentLocale = $currentLocale ?? service('request')->getLocale() ?? 'vi';
$chatUi = $currentLocale === 'en'
    ? [
        'button' => 'Travel Plus AI',
        'title' => 'Travel Plus AI',
        'subtitle' => 'Website-based answers only',
        'placeholder' => 'Ask about tours, visa, hotels, booking policy...',
        'send' => 'Send',
        'thinking' => 'Checking website data...',
        'error' => 'AI Travel Plus is temporarily unavailable.',
        'welcome' => 'Ask about tours, visa support, hotels, transport, or Travel Plus policies.',
        'online' => 'Online',
        'assistantName' => 'Travel Plus AI',
        'suggestions' => [
            'Do you support visa services?',
            'What payment methods are available?',
            'Do you have hotel booking support?',
            'How can I request a custom tour?',
        ],
      ]
    : [
        'button' => 'AI Travel Plus',
        'title' => 'AI Travel Plus',
        'subtitle' => 'Chỉ trả lời theo dữ liệu website',
        'placeholder' => 'Hỏi về tour, visa, khách sạn, chính sách đặt chỗ...',
        'send' => 'Gửi',
        'thinking' => 'Đang kiểm tra dữ liệu website...',
        'error' => 'AI Travel Plus đang tạm thời không khả dụng.',
        'welcome' => 'Bạn có thể hỏi về tour, visa, khách sạn, vận chuyển hoặc chính sách của Travel Plus.',
        'online' => 'Đang hoạt động',
        'assistantName' => 'AI Travel Plus',
        'suggestions' => [
            'Travel Plus có hỗ trợ visa không?',
            'Website đang có những phương thức thanh toán nào?',
            'Có dịch vụ đặt khách sạn không?',
            'Tôi muốn tạo tour theo yêu cầu thì làm thế nào?',
        ],
      ];
$contactUi = $currentLocale === 'en'
    ? [
        'button' => 'Contact Travel Plus',
        'panel' => 'Choose a contact channel',
        'messenger' => 'Messenger',
        'phone' => 'Call hotline',
        'zalo' => 'Zalo',
    ]
    : [
        'button' => 'Liên hệ Travel Plus',
        'panel' => 'Chọn kênh liên hệ',
        'messenger' => 'Messenger',
        'phone' => 'Gọi hotline',
        'zalo' => 'Zalo',
    ];
$contactPhone = '+84795681568';
$contactPhoneDisplay = $currentLocale === 'en'
    ? '(+84) 79 568 1 568'
    : '079 568 1 568';
$messengerUrl = 'https://m.me/uuthedulich.vietnam';
$zaloUrl = 'https://zalo.me/84795681568';
?>
<div class="tp-ai-chatbox" id="tp-ai-chatbox" data-endpoint="<?= esc(base_url('api/ai-chat')) ?>" data-locale="<?= esc($currentLocale) ?>">
    <div class="tp-ai-chatbox__contact">
        <div class="tp-ai-chatbox__contact-panel" id="tp-ai-contact-panel" hidden>
            <span><?= esc($contactUi['panel']) ?></span>
            <a class="tp-ai-chatbox__contact-option tp-ai-chatbox__contact-option--phone" href="tel:<?= esc($contactPhone, 'attr') ?>">
                <i class="bi bi-telephone-fill"></i>
                <strong><?= esc($contactPhoneDisplay) ?></strong>
            </a>
            <a class="tp-ai-chatbox__contact-option tp-ai-chatbox__contact-option--messenger" href="<?= esc($messengerUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-messenger"></i>
                <strong><?= esc($contactUi['messenger']) ?></strong>
                <small>Travel Plus</small>
            </a>
            <a class="tp-ai-chatbox__contact-option tp-ai-chatbox__contact-option--zalo" href="<?= esc($zaloUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer">
                <i aria-hidden="true">Z</i>
                <strong><?= esc($contactUi['zalo']) ?></strong>
                <small>Travelplus</small>
            </a>
        </div>
        <button type="button" class="tp-ai-chatbox__contact-toggle" aria-expanded="false" aria-controls="tp-ai-contact-panel" aria-label="<?= esc($contactUi['button'], 'attr') ?>">
            <i class="bi bi-telephone-fill"></i>
        </button>
    </div>

    <button type="button" class="tp-ai-chatbox__toggle" aria-expanded="false" aria-controls="tp-ai-chatbox-panel">
        <span class="tp-ai-chatbox__toggle-icon"><i class="bi bi-chat-dots-fill"></i></span>
        <span class="tp-ai-chatbox__toggle-copy">
            <strong><?= esc($chatUi['button']) ?></strong>
        </span>
    </button>

    <section class="tp-ai-chatbox__panel" id="tp-ai-chatbox-panel" hidden>
        <header class="tp-ai-chatbox__header">
            <div class="tp-ai-chatbox__identity">
                <div class="tp-ai-chatbox__avatar">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="tp-ai-chatbox__identity-copy">
                    <h3><?= esc($chatUi['title']) ?></h3>
                    <p><span class="tp-ai-chatbox__online-dot"></span><?= esc($chatUi['online']) ?></p>
                </div>
            </div>
            <button type="button" class="tp-ai-chatbox__close" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <div class="tp-ai-chatbox__messages" data-role="messages">
            <div class="tp-ai-chatbox__message tp-ai-chatbox__message--assistant">
                <div class="tp-ai-chatbox__message-avatar">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="tp-ai-chatbox__message-main">
                    <div class="tp-ai-chatbox__message-author"><?= esc($chatUi['assistantName']) ?></div>
                    <div class="tp-ai-chatbox__bubble"><?= esc($chatUi['welcome']) ?></div>
                    <div class="tp-ai-chatbox__suggestions" data-role="suggestions">
                        <?php foreach (($chatUi['suggestions'] ?? []) as $suggestion): ?>
                        <button type="button" class="tp-ai-chatbox__chip" data-suggestion="<?= esc($suggestion) ?>"><?= esc($suggestion) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <form class="tp-ai-chatbox__form" data-role="form">
            <div class="tp-ai-chatbox__composer">
                <textarea name="message" rows="1" placeholder="<?= esc($chatUi['placeholder']) ?>"></textarea>
                <button type="submit" class="tp-ai-chatbox__send" aria-label="<?= esc($chatUi['send']) ?>">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
            <div class="tp-ai-chatbox__statusline">
                <small data-role="status"></small>
            </div>
        </form>
    </section>

    <script type="application/json" data-ai-chatbox-i18n><?= json_encode([
        'thinking' => $chatUi['thinking'],
        'error' => $chatUi['error'],
        'assistantName' => $chatUi['assistantName'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
</div>
