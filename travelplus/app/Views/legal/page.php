<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('display');

$page = is_array($page ?? null) ? $page : [];
$sections = $page['sections'] ?? [];
$title = (string) ($page['title'] ?? 'Legal');
$subtitle = (string) ($page['subtitle'] ?? '');
$updatedAt = (string) ($page['updated_at'] ?? '');
$updatedLabel = (string) ($page['updated_label'] ?? 'Last updated');
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
                <?php if ($updatedAt !== ''): ?>
                    <time datetime="<?= esc($updatedAt, 'attr') ?>"><?= esc(app_datetime($updatedAt, 'd/m/Y')) ?></time>
                <?php endif; ?>
            </header>

            <div class="legal-content">
                <?php foreach ($sections as $section): ?>
                    <section class="travelplus-legal-section">
                        <h2><?= esc((string) ($section['heading'] ?? '')) ?></h2>

                        <?php foreach (($section['paragraphs'] ?? []) as $paragraph): ?>
                            <p><?= esc((string) $paragraph) ?></p>
                        <?php endforeach; ?>

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
        </div>
    </div>
</section>
<?= $this->endSection() ?>
