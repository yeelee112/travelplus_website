const fs=require('fs');const edit=(p,f)=>fs.writeFileSync(p,f(fs.readFileSync(p,'utf8')));
edit('app/Controllers/BaseController.php',s=>s.replace("        service('renderer')->setVar('menu', $menu);",`        $navigationCollections = [];
        if ($usesSiteNavigation && !DatabaseAvailabilityService::isUnavailable()) {
            try {
                foreach ((new \\App\\Services\\TourCollectionService())->all() as $collection) {
                    if (empty($collection['is_active'])) continue;
                    $navigationCollections[] = [
                        'slug' => $collection['slug'],
                        'label' => $locale === 'en' ? ($collection['name_en'] ?: $collection['name_vi']) : $collection['name_vi'],
                        'url' => $collection['slug'] === 'mua-thu'
                            ? \\App\\Data\\LocalizedPathCatalog::url('autumn', $locale)
                            : \\App\\Data\\LocalizedPathCatalog::url('search', $locale) . '?' . http_build_query(['collection' => $collection['slug']]),
                    ];
                }
            } catch (Throwable $exception) {
                log_message('error', 'Collection navigation unavailable: {message}', ['message' => $exception->getMessage()]);
            }
        }
        service('renderer')->setVar('navigationCollections', $navigationCollections);
        service('renderer')->setVar('menu', $menu);`));
edit('app/Views/home/index.php',s=>s.replace("<?= $this->include('sections/hero-search') ?>","<?= $this->include('sections/hero-search') ?>\n    <?= $this->include('sections/home-autumn-banner') ?>"));
edit('app/Views/partials/header.php',s=>{let start=s.indexOf('<li class="menu-item-has-children position-inherit <?= $isActiveHeaderUrl($outboundUrl)');let end=s.indexOf("<?php if ($locale === 'en'): ?>",start);let part=s.slice(start,end);part=part.replaceAll('                            </div>\n                        </div>\n                    </div>',`                            </div>
                            <?= view('partials/collection-navigation', ['navigationCollections' => $navigationCollections ?? [], 'locale' => $locale]) ?>
                        </div>
                    </div>`).replaceAll('                            </div>\r\n                        </div>\r\n                    </div>',`                            </div>
                            <?= view('partials/collection-navigation', ['navigationCollections' => $navigationCollections ?? [], 'locale' => $locale]) ?>
                        </div>
                    </div>`);return s.slice(0,start)+part+s.slice(end);});
edit('app/Views/layouts/main.php',s=>s.replace('<link rel="stylesheet" href="<?= esc($styleAssetUrl) ?>">','<link rel="stylesheet" href="<?= esc($styleAssetUrl) ?>">\n<link rel="stylesheet" href="<?= esc(frontend_asset_url(\'assets/css/collection-discovery.css\'), \'attr\') ?>">'));
