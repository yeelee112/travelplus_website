<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('display');

$page = is_array($page ?? null) ? $page : [];
$rawSections = is_array($page['sections'] ?? null) ? $page['sections'] : [];
$title = (string) ($page['title'] ?? 'Legal');
$subtitle = (string) ($page['subtitle'] ?? '');
$version = (string) ($page['version'] ?? '');
$effectiveDate = (string) ($page['effective_date'] ?? '');
$updatedAt = (string) ($page['updated_at'] ?? '');
$updatedLabel = (string) ($page['updated_label'] ?? 'Last updated');
$versionLabel = (string) ($page['version_label'] ?? 'Version');
$effectiveLabel = (string) ($page['effective_label'] ?? 'Effective date');
$tocLabel = (string) ($page['toc_label'] ?? 'Table of contents');
$relatedTitle = (string) ($page['related_title'] ?? 'Related policies');
$searchLabel = (string) ($page['search_label'] ?? 'Search');
$searchPlaceholder = (string) ($page['search_placeholder'] ?? '');
$noticeTitle = (string) ($page['notice_title'] ?? '');
$notice = (string) ($page['notice'] ?? '');
$cta = is_array($page['cta'] ?? null) ? $page['cta'] : [];
$faqs = is_array($page['faqs'] ?? null) ? $page['faqs'] : [];
$faqTitle = (string) ($page['faq_title'] ?? 'FAQ');
$relatedLinks = is_array($page['related_links'] ?? null) ? $page['related_links'] : [];
$formatDate = static function (string $value): string {
    $timestamp = strtotime($value);

    return $timestamp === false ? $value : date('d/m/Y', $timestamp);
};
$sectionId = static function (array $section, int $index): string {
    $id = trim((string) ($section['id'] ?? ''));
    if ($id !== '') {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '-', $id) ?: 'legal-section-' . ($index + 1);
    }

    return 'legal-section-' . ($index + 1);
};
$sections = [];
foreach (array_values($rawSections) as $index => $section) {
    if (! is_array($section)) {
        continue;
    }

    $section['_id'] = $sectionId($section, $index);
    $sections[] = $section;
}
?>

<section class="travelplus-legal-page">
    <div class="container">
        <div class="travelplus-legal-shell">
            <header class="travelplus-legal-head">
                <span><?= esc($updatedLabel) ?></span>
                <h1><?= esc($title) ?></h1>
                <?php if ($subtitle !== ''): ?>
                    <p><?= esc($subtitle) ?></p>
                <?php endif; ?>

                <div class="travelplus-legal-meta" aria-label="<?= esc($updatedLabel, 'attr') ?>">
                    <?php if ($version !== ''): ?>
                        <span><?= esc($versionLabel) ?> <?= esc($version) ?></span>
                    <?php endif; ?>
                    <?php if ($effectiveDate !== ''): ?>
                        <time datetime="<?= esc($effectiveDate, 'attr') ?>"><?= esc($effectiveLabel) ?> <?= esc($formatDate($effectiveDate)) ?></time>
                    <?php endif; ?>
                    <?php if ($updatedAt !== ''): ?>
                        <time datetime="<?= esc($updatedAt, 'attr') ?>"><?= esc($updatedLabel) ?> <?= esc($formatDate($updatedAt)) ?></time>
                    <?php endif; ?>
                </div>

            </header>

            <div class="travelplus-legal-layout">
                <aside class="travelplus-legal-sidebar" aria-label="<?= esc($tocLabel, 'attr') ?>">
                    <div class="travelplus-legal-sidebar__card">
                        <h2><?= esc($tocLabel) ?></h2>
                        <nav class="travelplus-legal-toc">
                            <?php foreach ($sections as $index => $section): ?>
                                <a href="#<?= esc((string) $section['_id'], 'attr') ?>" class="<?= $index === 0 ? 'is-active' : '' ?>" data-legal-toc-link>
                                    <span><?= esc(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                                    <?= esc((string) ($section['heading'] ?? '')) ?>
                                </a>
                            <?php endforeach; ?>
                        </nav>

                        <?php if ($relatedLinks !== []): ?>
                            <div class="travelplus-legal-related">
                                <h3><?= esc($relatedTitle) ?></h3>
                                <?php foreach ($relatedLinks as $link): ?>
                                    <?php
                                    $label = trim((string) ($link['label'] ?? ''));
                                    $url = trim((string) ($link['url'] ?? ''));
                                    ?>
                                    <?php if ($label !== '' && $url !== ''): ?>
                                        <a href="<?= esc($url, 'attr') ?>"><?= esc($label) ?> <i class="bi bi-arrow-up-right"></i></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </aside>

                <main class="travelplus-legal-main">
                    <div class="travelplus-legal-tools">
                        <label for="legalPageSearch"><?= esc($searchLabel) ?></label>
                        <div class="travelplus-legal-search">
                            <i class="bi bi-search"></i>
                            <input id="legalPageSearch" type="search" placeholder="<?= esc($searchPlaceholder, 'attr') ?>" data-legal-search>
                        </div>
                    </div>

                    <?php if ($notice !== ''): ?>
                        <div class="travelplus-legal-highlight">
                            <?php if ($noticeTitle !== ''): ?>
                                <strong><?= esc($noticeTitle) ?></strong>
                            <?php endif; ?>
                            <p><?= esc($notice) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="legal-content" data-legal-content>
                        <?php foreach ($sections as $section): ?>
                            <?php $id = (string) $section['_id']; ?>
                            <section class="travelplus-legal-section" id="<?= esc($id, 'attr') ?>" data-legal-section>
                                <h2>
                                    <?= esc((string) ($section['heading'] ?? '')) ?>
                                    <a href="#<?= esc($id, 'attr') ?>" class="travelplus-legal-anchor" aria-label="<?= esc((string) ($section['heading'] ?? ''), 'attr') ?>">
                                        <i class="bi bi-link-45deg"></i>
                                    </a>
                                </h2>

                                <?php foreach (($section['paragraphs'] ?? []) as $paragraph): ?>
                                    <p><?= esc((string) $paragraph) ?></p>
                                <?php endforeach; ?>

                                <?php if (! empty($section['note'])): ?>
                                    <div class="travelplus-legal-note">
                                        <i class="bi bi-info-circle"></i>
                                        <p><?= esc((string) $section['note']) ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (! empty($section['bullets'])): ?>
                                    <ul class="legal-bullet-list">
                                        <?php foreach ($section['bullets'] as $bullet): ?>
                                            <li><?= esc((string) $bullet) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>

                                <?php foreach (($section['subsections'] ?? []) as $subsection): ?>
                                    <div class="travelplus-legal-subsection">
                                        <h3><?= esc((string) ($subsection['heading'] ?? '')) ?></h3>

                                        <?php foreach (($subsection['paragraphs'] ?? []) as $paragraph): ?>
                                            <p><?= esc((string) $paragraph) ?></p>
                                        <?php endforeach; ?>

                                        <?php if (! empty($subsection['bullets'])): ?>
                                            <ul class="legal-bullet-list">
                                                <?php foreach ($subsection['bullets'] as $bullet): ?>
                                                    <li><?= esc((string) $bullet) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </section>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($faqs !== []): ?>
                        <section class="travelplus-legal-faq">
                            <h2><?= esc($faqTitle) ?></h2>
                            <div class="travelplus-legal-faq__list">
                                <?php foreach ($faqs as $faq): ?>
                                    <?php
                                    $question = trim((string) ($faq['question'] ?? ''));
                                    $answer = trim((string) ($faq['answer'] ?? ''));
                                    ?>
                                    <?php if ($question !== '' && $answer !== ''): ?>
                                        <details>
                                            <summary><?= esc($question) ?></summary>
                                            <p><?= esc($answer) ?></p>
                                        </details>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if ($cta !== []): ?>
                        <section class="travelplus-legal-cta">
                            <?php if (! empty($cta['eyebrow'])): ?>
                                <span><?= esc((string) $cta['eyebrow']) ?></span>
                            <?php endif; ?>
                            <?php if (! empty($cta['title'])): ?>
                                <h2><?= esc((string) $cta['title']) ?></h2>
                            <?php endif; ?>
                            <?php if (! empty($cta['description'])): ?>
                                <p><?= esc((string) $cta['description']) ?></p>
                            <?php endif; ?>
                            <div class="travelplus-legal-cta__actions">
                                <?php if (! empty($cta['primary_label']) && ! empty($cta['primary_url'])): ?>
                                    <a href="<?= esc((string) $cta['primary_url'], 'attr') ?>" class="primary-btn1">
                                        <?= esc((string) $cta['primary_label']) ?> <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (! empty($cta['secondary_label']) && ! empty($cta['secondary_url'])): ?>
                                    <a href="<?= esc((string) $cta['secondary_url'], 'attr') ?>" class="travelplus-legal-cta__secondary">
                                        <?= esc((string) $cta['secondary_label']) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.travelplus-legal-page').forEach(function (root) {
    const searchInput = root.querySelector('[data-legal-search]');
    const sections = Array.from(root.querySelectorAll('[data-legal-section]'));
    const tocLinks = Array.from(root.querySelectorAll('[data-legal-toc-link]'));

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const keyword = searchInput.value.trim().toLowerCase();

            sections.forEach(function (section) {
                const matched = keyword === '' || section.textContent.toLowerCase().includes(keyword);
                section.hidden = !matched;
            });
        });
    }

    if ('IntersectionObserver' in window && sections.length > 0) {
        const setActive = function (id) {
            tocLinks.forEach(function (link) {
                link.classList.toggle('is-active', link.getAttribute('href') === '#' + id);
            });
        };
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    setActive(entry.target.id);
                }
            });
        }, { rootMargin: '-28% 0px -58% 0px', threshold: 0.01 });

        sections.forEach(function (section) {
            observer.observe(section);
        });
    }
});
</script>
<?= $this->endSection() ?>
