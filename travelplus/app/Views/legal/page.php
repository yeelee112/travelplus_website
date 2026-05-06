<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$page = is_array($page ?? null) ? $page : [];
$sections = $page['sections'] ?? [];
$title = (string) ($page['title'] ?? 'Legal');
$subtitle = (string) ($page['subtitle'] ?? '');
$updatedAt = (string) ($page['updated_at'] ?? '');
?>

<div class="container pt-100 pb-100">
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">
            <div class="section-title text-center mb-50">
                <h2><?= esc($title) ?></h2>
                <?php if ($subtitle !== ''): ?>
                    <p><?= esc($subtitle) ?></p>
                <?php endif; ?>
                <?php if ($updatedAt !== ''): ?>
                    <p><strong>Ngày cập nhật:</strong> <?= esc($updatedAt) ?></p>
                <?php endif; ?>
            </div>

            <div class="legal-content">
                <?php foreach ($sections as $section): ?>
                    <section class="mb-40">
                        <h4 class="mb-20"><?= esc((string) ($section['heading'] ?? '')) ?></h4>

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
                            <div class="mt-25">
                                <h5 class="mb-15"><?= esc((string) ($subsection['heading'] ?? '')) ?></h5>

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
</div>
<?= $this->endSection() ?>
