const fs=require('fs');const p='app/Services/TourCatalogService.php';let s=fs.readFileSync(p,'utf8');const pos=s.indexOf('    public function searchTours(');s=s.slice(0,pos)+`    /** Available destinations across the whole collection, before destination/month filtering. */
    public function getCollectionDestinations(string $locale, string $collectionSlug, string $from, string $to, ?string $excludedTourType = null): array
    {
        if (!$this->hasSchemaForTourCatalog() || !$this->db->tableExists('tour_collections') || !$this->db->tableExists('tour_collection_tours')) return [];
        try {
            $countryId = "CASE WHEN t.tour_type = 'outbound' AND dl.type = 'country' THEN dl.id WHEN t.tour_type = 'outbound' AND dlp.type = 'country' THEN dlp.id WHEN t.tour_type = 'outbound' AND dlgp.type = 'country' THEN dlgp.id ELSE dl.id END";
            $name = "CASE WHEN t.tour_type = 'outbound' AND dl.type = 'country' THEN dltn.name WHEN t.tour_type = 'outbound' AND dlp.type = 'country' THEN dlptn.name WHEN t.tour_type = 'outbound' AND dlgp.type = 'country' THEN dlgptn.name ELSE dltn.name END";
            $slug = "CASE WHEN t.tour_type = 'outbound' AND dl.type = 'country' THEN dltn.slug WHEN t.tour_type = 'outbound' AND dlp.type = 'country' THEN dlptn.slug WHEN t.tour_type = 'outbound' AND dlgp.type = 'country' THEN dlgptn.slug ELSE dltn.slug END";
            $rows = $this->baseToursBuilder($locale, null, [], false, false, $excludedTourType)
                ->join('tour_collection_tours tct', 'tct.tour_id = t.id', 'inner')
                ->join('tour_collections tc', 'tc.id = tct.collection_id', 'inner')
                ->where('tc.slug', $collectionSlug)->where('tc.is_active', 1)
                ->where('td.departure_date >=', $from)->where('td.departure_date <=', $to)
                ->select($countryId . ' AS id, ' . $name . ' AS name, ' . $slug . ' AS slug', false)
                ->distinct()->get()->getResultArray();
            $options = [];
            foreach ($rows as $row) {
                if (empty($row['id']) || trim((string)$row['name']) === '') continue;
                $key = trim((string)$row['slug']) ?: 'location-' . $row['id'];
                $options[$key] = ['id'=>(int)$row['id'], $locale=>TextEncodingService::repairNullable($row['name'])];
            }
            uasort($options, static fn(array $a, array $b): int => strnatcasecmp($a[$locale], $b[$locale]));
            return $options;
        } catch (Throwable $exception) {
            log_message('error', 'Collection destinations unavailable: {message}', ['message'=>$exception->getMessage()]);
            return [];
        }
    }

`+s.slice(pos);s=s.replace("string $collectionSlug = ''\n", "string $collectionSlug = '',\n        int $destinationId = 0\n");s=s.replace("if ($collectionSlug === '' && $query === ''", "if ($destinationId <= 0 && $collectionSlug === '' && $query === ''");s=s.replace("        if ($query !== '') {\n            $slugKeyword", "        if ($destinationId > 0) {\n            $builder->groupStart()->where('dl.id', $destinationId)->orWhere('dlp.id', $destinationId)->orWhere('dlgp.id', $destinationId)->groupEnd();\n        }\n\n        if ($query !== '') {\n            $slugKeyword");fs.writeFileSync(p,s);
const c='app/Controllers/AutumnTours.php';s=fs.readFileSync(c,'utf8');const a=s.indexOf('        $destinations = [');const b=s.indexOf('        $month = ',a);s=s.slice(0,a)+s.slice(b);s=s.replace("        $result = (new \\App\\Services\\TourCatalogService())->searchTours(",`        $catalog = new \\App\\Services\\TourCatalogService();
        $destinations = $catalog->getCollectionDestinations($locale, 'mua-thu', $year . '-09-01', $year . '-11-30', $locale === 'en' ? 'domestic' : 'inbound');
        $destination = (string) $this->request->getGet('destination');
        $destination = isset($destinations[$destination]) ? $destination : '';
        $destinationId = $destination !== '' ? $destinations[$destination]['id'] : 0;
        $result = $catalog->searchTours(`).replace("$locale, $destination !== '' ? $destinations[$destination][$locale] : '',", "$locale, '',").replace("'inbound', 'mua-thu'", "'inbound', 'mua-thu', $destinationId").replace("'q' => $destination !== '' ? $destinations[$destination][$locale] : '',", "'destination_id' => $destinationId ?: '',");fs.writeFileSync(c,s);
const q='app/Controllers/SearchController.php';s=fs.readFileSync(q,'utf8').replace("$collectionSlug = trim", "$destinationId = max(0, (int) $this->request->getGet('destination_id'));\n        $collectionSlug = trim").replace('$excludedTourType, $collectionSlug);','$excludedTourType, $collectionSlug, $destinationId);').replace("'collection' => $collectionSlug,", "'destination_id' => $destinationId ?: '',\n            'collection' => $collectionSlug,").replace("if ($collectionSlug === '' && ((int)", "if ($destinationId === 0 && $collectionSlug === '' && ((int)");fs.writeFileSync(q,s);
const f='app/Views/sections/tour-list-filter.php';s=fs.readFileSync(f,'utf8').replace('<input type="hidden" name="collection"', '<input type="hidden" name="destination_id" value="<?= max(0, (int) service(\'request\')->getGet(\'destination_id\')) ?>"><input type="hidden" name="collection"');fs.writeFileSync(f,s);
