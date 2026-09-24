const fs=require('fs');const edit=(p,fn)=>fs.writeFileSync(p,fn(fs.readFileSync(p,'utf8')));
edit('app/Controllers/Admin/Tours.php',s=>s.replace("$formData['is_autumn'] = 0;","$formData['collection_ids'] = []; ").replace("            'is_autumn' => (string) ($post['is_autumn'] ?? '') === '1' ? 1 : 0,\n",'').replace("'is_autumn' => (int) ($tour['is_autumn'] ?? 0),","'collection_ids' => (new \\App\\Services\\TourCollectionService())->selected($tourId),").replace("'categories' => $this->getCategories(),","'collections' => (new \\App\\Services\\TourCollectionService())->all(),\n            'categories' => $this->getCategories(),").replace("return (int) $db->insertID();","$newId = (int) $db->insertID();\n            (new \\App\\Services\\TourCollectionService())->sync($newId, $post['collection_ids'] ?? []);\n            return $newId;").replace("$db->table('tours')->where('id', $tourId)->update($data);\n\n        return $tourId;","$db->table('tours')->where('id', $tourId)->update($data);\n        (new \\App\\Services\\TourCollectionService())->sync($tourId, $post['collection_ids'] ?? []);\n\n        return $tourId;"));
edit('app/Models/TourModel.php',s=>s.replace("        'is_autumn',\n",''));
edit('app/Views/admin/tours/form.php',s=>s.replace(/<div class="col-12">\s*<label class="form-check">\s*<input type="checkbox" name="is_autumn"[\s\S]*?<\/div>\s*<\/div>/,`<div class="col-12">
                    <fieldset><legend class="fs-6 fw-bold">Bộ sưu tập tour</legend>
                    <input type="hidden" name="collections_present" value="1">
                    <?php $selectedCollections = old('collections_present') !== null ? (array) old('collection_ids', []) : (array) ($formData['collection_ids'] ?? []); ?>
                    <div class="d-flex flex-wrap gap-4">
                    <?php foreach ($collections as $collection): ?>
                    <label class="form-check"><input type="checkbox" class="form-check-input" name="collection_ids[]" value="<?= (int) $collection['id'] ?>" <?= in_array((int)$collection['id'], array_map('intval',$selectedCollections),true) ? 'checked' : '' ?>>
                    <span class="form-check-label"><?= esc($collection['name_vi']) ?><?= empty($collection['is_active']) ? ' (đang ẩn)' : '' ?></span></label>
                    <?php endforeach ?></div>
                    <div class="help">Có thể chọn nhiều bộ sưu tập cho một tour. <a href="<?= site_url('admin/tour-collections') ?>" target="_blank" rel="noopener">Quản lý bộ sưu tập</a></div></fieldset>
                </div>`));
edit('app/Services/TourCatalogService.php',s=>s.replace('bool $autumnOnly = false',"string $collectionSlug = ''").replace('!$autumnOnly &&',"$collectionSlug === '' &&").replace("($autumnOnly && !$this->fieldExists('is_autumn', 'tours'))","($collectionSlug !== '' && (!$this->db->tableExists('tour_collections') || !$this->db->tableExists('tour_collection_tours')))").replace("if ($autumnOnly) {\n            $builder->where('t.is_autumn', 1);\n        }",`if ($collectionSlug !== '') {
            $builder->join('tour_collection_tours tct', 'tct.tour_id = t.id', 'inner')
                ->join('tour_collections tc', 'tc.id = tct.collection_id', 'inner')
                ->where('tc.slug', $collectionSlug)->where('tc.is_active', 1);
        }`));
edit('app/Controllers/AutumnTours.php',s=>s.replace("'inbound', true","'inbound', 'mua-thu'").replace("'autumn' => '1'","'collection' => 'mua-thu'"));
edit('app/Controllers/SearchController.php',s=>s.replace("$autumnOnly = (string) $this->request->getGet('autumn') === '1';",`$collectionSlug = trim((string) $this->request->getGet('collection'));
        if ($collectionSlug === '' && (string) $this->request->getGet('autumn') === '1') $collectionSlug = 'mua-thu';
        $collectionName = '';
        foreach ((new \\App\\Services\\TourCollectionService())->all() as $collection) {
            if ($collection['slug'] === $collectionSlug && !empty($collection['is_active'])) $collectionName = $locale === 'en' ? ($collection['name_en'] ?: $collection['name_vi']) : $collection['name_vi'];
        }`).replace('$excludedTourType, $autumnOnly)', '$excludedTourType, $collectionSlug)').replace('!$autumnOnly &&',"$collectionSlug === '' &&").replace("'autumn' => $autumnOnly ? '1' : '',","'collection' => $collectionSlug,").replace("$autumnOnly ? ($locale === 'en' ? 'Autumn tours' : 'Tour mùa thu') : $t('search.resultsTitle')","$collectionName !== '' ? $collectionName : $t('search.resultsTitle')").replace("'listingSearch' => [","'collectionSlug' => $collectionSlug,\n            'listingSearch' => ["));
edit('app/Views/sections/tour-list-filter.php',s=>s.replace(`<?php if ((string) service('request')->getGet('autumn') === '1'): ?><input type="hidden" name="autumn" value="1"><?php endif ?>`,`<?php if (!empty($collectionSlug)): ?><input type="hidden" name="collection" value="<?= esc($collectionSlug, 'attr') ?>"><?php endif ?>`));
edit('app/Config/Routes.php',s=>s.replace("$routes->GET('admin/tours',", "$routes->GET('admin/tour-collections', 'Admin\\\\TourCollections::index');\n$routes->POST('admin/tour-collections', 'Admin\\\\TourCollections::save');\n$routes->POST('admin/tour-collections/(:num)', 'Admin\\\\TourCollections::save/$1');\n$routes->GET('admin/tours',"));
edit('app/Views/admin/tours/index.php',s=>s.replace('<a class="btn btn-primary" href="<?= site_url(\'admin/tours/create\') ?>">', '<a class="btn btn-outline-primary" href="<?= site_url(\'admin/tour-collections\') ?>">Bộ sưu tập tour</a>\n                <a class="btn btn-primary" href="<?= site_url(\'admin/tours/create\') ?>">'));
