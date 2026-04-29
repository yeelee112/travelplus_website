<?php if (!empty($breadcrumbs)): ?>
    <div class="container pt-3 pb-3">
        <nav aria-label="breadcrumb" class="breadcrumb-wrapper">
            <ol class="breadcrumb">

                <?php foreach ($breadcrumbs as $index => $crumb): ?>

                    <?php if ($index === 0): ?>
                        <li class="breadcrumb-item">
                            <a href="<?= localized_url('/') ?>" aria-label="Trang chủ" title="Trang chủ">
                                <i class="bi bi-house-fill main-color"></i>
                                <!-- Text ẩn cho SEO + Screen reader -->
                                <span class="visually-hidden">
                                    Trang chủ
                                </span>
                            </a>
                        </li>
                    <?php else: ?>
                        <?php if (isset($crumb['url'])): ?>
                            <li class="breadcrumb-item">
                                <a href="<?= $crumb['url'] ?>">
                                    <?= esc($crumb['label']) ?>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= esc($crumb['label']) ?>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>

    <!-- ================= SEO Schema ================= -->

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [

        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            {
              "@type": "ListItem",
              "position": <?= $index + 1 ?>,
              "name": "<?= esc($crumb['label']) ?>",
              <?php if (isset($crumb['url'])): ?>
                  "item": "<?= $crumb['url'] ?>"
              <?php endif; ?>
            }<?= $index < count($breadcrumbs) - 1 ? ',' : '' ?>
        <?php endforeach; ?>

      ]
    }
    </script>
    
<?php endif; ?>