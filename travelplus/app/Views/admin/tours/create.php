<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Create Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f8; color: #172033; }
        .admin-shell { max-width: 1220px; margin: 32px auto; padding: 0 16px; }
        .admin-card { background: #fff; border: 1px solid #e6ebf0; border-radius: 18px; box-shadow: 0 16px 40px rgba(23,32,51,.06); padding: 28px; }
        .section-title { font-size: 18px; font-weight: 700; margin: 28px 0 14px; padding-top: 18px; border-top: 1px solid #edf1f5; }
        .section-title:first-of-type { border-top: 0; padding-top: 0; }
        label { font-weight: 600; margin-bottom: 6px; }
        textarea { min-height: 105px; }
        .help { color: #6b778c; font-size: 13px; }
        .repeat-item { border: 1px solid #e5ebf2; border-radius: 14px; padding: 16px; background: #fbfcfe; margin-bottom: 12px; }
        .new-country-fields { display: none; }
        .repeat-item.is-new-country .new-country-fields { display: flex; }
        .rich-editor-wrap { border: 1px solid #d8dee6; border-radius: 8px; background: #fff; overflow: hidden; }
        .rich-editor-toolbar { display: flex; gap: 6px; padding: 8px; border-bottom: 1px solid #edf1f5; background: #f8fafc; }
        .rich-editor-toolbar button { border: 1px solid #d8dee6; background: #fff; border-radius: 6px; padding: 4px 9px; font-weight: 700; }
        .rich-editor { min-height: 118px; padding: 10px 12px; outline: 0; }
        .rich-editor:focus { box-shadow: inset 0 0 0 2px rgba(13, 110, 253, .18); }
    </style>
</head>
<body>
<?php
    $countriesByParent = [];
    foreach ($countries as $country) {
        $countriesByParent[(int) $country['parent_id']][] = $country;
    }
?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Create tour</h1>
                <p class="text-muted mb-0">Internal form for inserting tour data without writing SQL.</p>
            </div>
            <a class="btn btn-outline-secondary" href="<?= site_url('/') ?>">Home</a>
        </div>

        <?php if (! empty($success)): ?>
            <div class="alert alert-success"><?= esc($success) ?></div>
        <?php endif; ?>

        <?php if (! empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= esc($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('admin/tours') ?>" enctype="multipart/form-data">
            <h2 class="section-title">Main Info</h2>
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Tour type</label>
                    <select name="tour_type" class="form-select" required>
                        <option value="outbound" <?= old('tour_type') === 'outbound' ? 'selected' : '' ?>>Outbound</option>
                        <option value="inbound" <?= old('tour_type') === 'inbound' ? 'selected' : '' ?>>Inbound</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label>Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Choose category --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category['id']) ?>" <?= old('category_id') == $category['id'] ? 'selected' : '' ?>>
                                #<?= esc($category['id']) ?> - <?= esc($category['name']) ?> (<?= esc($category['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Code</label>
                    <input name="code" class="form-control" value="<?= esc(old('code')) ?>" placeholder="TP-001">
                </div>
                <div class="col-md-2">
                    <label>SKU</label>
                    <input name="sku" class="form-control" value="<?= esc(old('sku')) ?>" placeholder="SKU-001">
                </div>
                <div class="col-md-2">
                    <label>Days</label>
                    <input type="number" min="1" name="duration_days" class="form-control" value="<?= esc(old('duration_days', '5')) ?>" required>
                </div>
                <div class="col-md-2">
                    <label>Nights</label>
                    <input type="number" min="0" name="duration_nights" class="form-control" value="<?= esc(old('duration_nights', '4')) ?>" required>
                </div>
                <div class="col-md-2">
                    <label>Max travelers</label>
                    <input type="number" min="0" name="max_travelers" class="form-control" value="<?= esc(old('max_travelers', '15')) ?>">
                </div>
                <div class="col-md-3">
                    <label>Adult price</label>
                    <input type="number" min="0" name="base_price" class="form-control" value="<?= esc(old('base_price')) ?>" placeholder="103900000">
                    <div class="help">Child/infant prices can be calculated from this later.</div>
                </div>
                <div class="col-md-3">
                    <label>Sale price</label>
                    <input type="number" min="0" name="sale_price" class="form-control" value="<?= esc(old('sale_price')) ?>">
                </div>
                <div class="col-md-6">
                    <label>Thumbnail</label>
                    <input name="thumbnail" class="form-control" value="<?= esc(old('thumbnail')) ?>" placeholder="assets/images/...">
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?= old('status') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= old('status') === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <label class="form-check">
                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" <?= old('is_featured') ? 'checked' : '' ?>>
                        <span class="form-check-label">Featured tour</span>
                    </label>
                </div>
            </div>

            <h2 class="section-title">Locations</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Departure location</label>
                    <select name="departure_location_id" class="form-select" required>
                        <option value="">-- Choose departure --</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?= esc($location['id']) ?>" <?= old('departure_location_id') == $location['id'] ? 'selected' : '' ?>>
                                #<?= esc($location['id']) ?> - <?= esc($location['name']) ?> (<?= esc($location['type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Primary destination</label>
                    <select name="primary_destination_id" class="form-select">
                        <option value="">-- Optional --</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= esc($country['id']) ?>" <?= old('primary_destination_id') == $country['id'] ? 'selected' : '' ?>>
                                #<?= esc($country['id']) ?> - <?= esc($country['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h2 class="section-title">Destinations</h2>
            <div id="destinationRows">
                <div class="repeat-item destination-row">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Continent</label>
                            <select name="destinations[0][continent_id]" class="form-select js-continent-select">
                                <option value="">-- Choose continent --</option>
                                <?php foreach ($continents as $continent): ?>
                                    <option value="<?= esc($continent['id']) ?>"><?= esc($continent['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Country</label>
                            <select name="destinations[0][country_id]" class="form-select js-country-select">
                                <option value="">-- Choose existing country --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Or create country</label>
                            <button type="button" class="btn btn-outline-primary w-100 js-toggle-new-country">Create new country</button>
                        </div>
                    </div>
                    <div class="row g-3 mt-1 new-country-fields">
                        <div class="col-md-3"><input name="destinations[0][new_country_name_vi]" class="form-control" placeholder="Country name VI, e.g. Canada"></div>
                        <div class="col-md-3"><input name="destinations[0][new_country_slug_vi]" class="form-control" placeholder="slug vi, e.g. canada"></div>
                        <div class="col-md-3"><input name="destinations[0][new_country_name_en]" class="form-control" placeholder="Country name EN"></div>
                        <div class="col-md-2"><input name="destinations[0][new_country_slug_en]" class="form-control" placeholder="slug en"></div>
                        <div class="col-md-1"><input name="destinations[0][new_country_code]" class="form-control" placeholder="CA"></div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-success" id="addDestination">Add destination</button>

            <h2 class="section-title">Content VI</h2>
            <div class="row g-3">
                <div class="col-md-6"><label>Name VI</label><input name="name_vi" class="form-control" value="<?= esc(old('name_vi')) ?>" required></div>
                <div class="col-md-6"><label>Slug VI</label><input name="slug_vi" class="form-control" value="<?= esc(old('slug_vi')) ?>" required></div>
                <div class="col-md-12"><label>Short description VI</label><textarea name="short_description_vi" class="form-control"><?= esc(old('short_description_vi')) ?></textarea></div>
                <div class="col-md-6"><label>Overview VI</label><textarea name="overview_vi" class="form-control"><?= esc(old('overview_vi')) ?></textarea></div>
                <div class="col-md-6"><label>Description VI</label><textarea name="description_vi" class="form-control"><?= esc(old('description_vi')) ?></textarea></div>
            </div>

            <h2 class="section-title">Content EN</h2>
            <div class="row g-3">
                <div class="col-md-6"><label>Name EN</label><input name="name_en" class="form-control" value="<?= esc(old('name_en')) ?>"></div>
                <div class="col-md-6"><label>Slug EN</label><input name="slug_en" class="form-control" value="<?= esc(old('slug_en')) ?>"></div>
                <div class="col-md-12"><label>Short description EN</label><textarea name="short_description_en" class="form-control"><?= esc(old('short_description_en')) ?></textarea></div>
                <div class="col-md-6"><label>Overview EN</label><textarea name="overview_en" class="form-control"><?= esc(old('overview_en')) ?></textarea></div>
                <div class="col-md-6"><label>Description EN</label><textarea name="description_en" class="form-control"><?= esc(old('description_en')) ?></textarea></div>
            </div>

            <h2 class="section-title">Departure</h2>
            <div class="row g-3">
                <div class="col-md-3"><label>Date</label><input type="date" name="departure_date" class="form-control" value="<?= esc(old('departure_date')) ?>"></div>
                <div class="col-md-3"><label>Slots</label><input type="number" min="0" name="available_slots" class="form-control" value="<?= esc(old('available_slots')) ?>"></div>
                <div class="col-md-3"><label>Adult price</label><input type="number" min="0" name="price" class="form-control" value="<?= esc(old('price')) ?>"></div>
                <div class="col-md-3"><label>Status</label><select name="departure_status" class="form-select"><option value="open">Open</option><option value="closed">Closed</option></select></div>
            </div>

            <h2 class="section-title">Itinerary Days</h2>
            <div id="itineraryRows">
                <div class="repeat-item">
                    <div class="row g-3">
                        <div class="col-md-2"><label>Day</label><input type="number" min="1" name="itinerary_days[0][day_number]" class="form-control" value="1"></div>
                        <div class="col-md-5"><label>Title VI</label><input name="itinerary_days[0][title_vi]" class="form-control"></div>
                        <div class="col-md-5"><label>Title EN</label><input name="itinerary_days[0][title_en]" class="form-control"></div>
                        <div class="col-md-4"><label>Meals</label><input name="itinerary_days[0][meals]" class="form-control" placeholder="B/L/D"></div>
                        <div class="col-md-4"><label>Hotel</label><input name="itinerary_days[0][hotel_name]" class="form-control"></div>
                        <div class="col-md-4"><label>Transport</label><input name="itinerary_days[0][transport_summary]" class="form-control"></div>
                        <div class="col-md-6">
                            <label>Description VI</label>
                            <textarea name="itinerary_days[0][description_vi]" class="form-control d-none js-rich-source"></textarea>
                            <div class="rich-editor-wrap js-rich-wrap">
                                <div class="rich-editor-toolbar">
                                    <button type="button" data-command="bold">B</button>
                                    <button type="button" data-command="italic">I</button>
                                    <button type="button" data-command="insertUnorderedList">List</button>
                                    <button type="button" data-command="removeFormat">Clear</button>
                                </div>
                                <div class="rich-editor js-rich-editor" contenteditable="true"></div>
                            </div>
                            <div class="help mt-1">Boi den text roi bam B de in dam diem den.</div>
                        </div>
                        <div class="col-md-6">
                            <label>Description EN</label>
                            <textarea name="itinerary_days[0][description_en]" class="form-control d-none js-rich-source"></textarea>
                            <div class="rich-editor-wrap js-rich-wrap">
                                <div class="rich-editor-toolbar">
                                    <button type="button" data-command="bold">B</button>
                                    <button type="button" data-command="italic">I</button>
                                    <button type="button" data-command="insertUnorderedList">List</button>
                                    <button type="button" data-command="removeFormat">Clear</button>
                                </div>
                                <div class="rich-editor js-rich-editor" contenteditable="true"></div>
                            </div>
                            <div class="help mt-1">Select text and press B to highlight destinations.</div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-success" id="addItinerary">Add day</button>

            <h2 class="section-title">Media / Gallery</h2>
            <div id="mediaRows">
                <div class="repeat-item">
                    <div class="row g-3">
                        <div class="col-md-3"><label>Type</label><select name="media[0][type]" class="form-select"><option value="banner">Banner</option><option value="cover">Cover</option><option value="gallery">Gallery</option><option value="video">Video</option></select></div>
                        <div class="col-md-5"><label>Upload image</label><input type="file" name="media_files[]" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></div>
                        <div class="col-md-4"><label>Title / Alt text</label><input name="media[0][alt_text]" class="form-control" placeholder="Tháp Eiffel"></div>
                        <input type="hidden" name="media[0][file_path]" value="">
                    </div>
                    <div class="help mt-2">DB sẽ lưu path tương đối, ví dụ: uploads/tours/12/gallery/image.webp</div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-success" id="addMedia">Add media</button>

            <h2 class="section-title">FAQ</h2>
            <div id="faqRows">
                <div class="repeat-item">
                    <div class="row g-3">
                        <div class="col-md-6"><label>Question VI</label><input name="faqs[0][question_vi]" class="form-control"></div>
                        <div class="col-md-6"><label>Question EN</label><input name="faqs[0][question_en]" class="form-control"></div>
                        <div class="col-md-6"><label>Answer VI</label><textarea name="faqs[0][answer_vi]" class="form-control"></textarea></div>
                        <div class="col-md-6"><label>Answer EN</label><textarea name="faqs[0][answer_en]" class="form-control"></textarea></div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-success" id="addFaq">Add FAQ</button>

            <h2 class="section-title">SEO</h2>
            <div class="row g-3">
                <div class="col-md-6"><label>Meta title VI</label><input name="meta_title_vi" class="form-control" value="<?= esc(old('meta_title_vi')) ?>"></div>
                <div class="col-md-6"><label>Meta title EN</label><input name="meta_title_en" class="form-control" value="<?= esc(old('meta_title_en')) ?>"></div>
                <div class="col-md-6"><label>Meta description VI</label><textarea name="meta_description_vi" class="form-control"><?= esc(old('meta_description_vi')) ?></textarea></div>
                <div class="col-md-6"><label>Meta description EN</label><textarea name="meta_description_en" class="form-control"><?= esc(old('meta_description_en')) ?></textarea></div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary btn-lg" type="submit">Save tour</button>
                <a class="btn btn-outline-secondary btn-lg" href="<?= site_url('admin/tours/create') ?>">Reset</a>
            </div>
        </form>
    </div>
</main>

<script>
const countriesByParent = <?= json_encode($countriesByParent, JSON_UNESCAPED_UNICODE) ?>;
const continents = <?= json_encode($continents, JSON_UNESCAPED_UNICODE) ?>;
let destinationIndex = 1;
let itineraryIndex = 1;
let mediaIndex = 1;
let faqIndex = 1;

function fillCountries(row) {
    const continentSelect = row.querySelector('.js-continent-select');
    const countrySelect = row.querySelector('.js-country-select');
    const countries = countriesByParent[continentSelect.value] || [];
    countrySelect.innerHTML = '<option value="">-- Choose existing country --</option>';
    countries.forEach(country => {
        const option = document.createElement('option');
        option.value = country.id;
        option.textContent = `#${country.id} - ${country.name}`;
        countrySelect.appendChild(option);
    });
}

function bindDestinationRow(row) {
    row.querySelector('.js-continent-select').addEventListener('change', () => fillCountries(row));
    row.querySelector('.js-toggle-new-country').addEventListener('click', () => row.classList.toggle('is-new-country'));
}

document.querySelectorAll('.destination-row').forEach(bindDestinationRow);

function syncRichEditor(wrap) {
    const source = wrap.parentElement.querySelector('.js-rich-source');
    const editor = wrap.querySelector('.js-rich-editor');

    if (source && editor) {
        source.value = editor.innerHTML.trim();
    }
}

function bindRichEditor(scope = document) {
    scope.querySelectorAll('.js-rich-wrap').forEach(wrap => {
        if (wrap.dataset.ready === '1') {
            return;
        }

        wrap.dataset.ready = '1';
        const source = wrap.parentElement.querySelector('.js-rich-source');
        const editor = wrap.querySelector('.js-rich-editor');

        if (source && editor && source.value) {
            editor.innerHTML = source.value;
        }

        wrap.querySelectorAll('[data-command]').forEach(button => {
            button.addEventListener('click', () => {
                editor.focus();
                document.execCommand(button.dataset.command, false, null);
                syncRichEditor(wrap);
            });
        });

        editor.addEventListener('input', () => syncRichEditor(wrap));
        editor.addEventListener('blur', () => syncRichEditor(wrap));
    });
}

bindRichEditor();

document.querySelector('form').addEventListener('submit', () => {
    document.querySelectorAll('.js-rich-wrap').forEach(syncRichEditor);
});

document.getElementById('addDestination').addEventListener('click', () => {
    const wrapper = document.createElement('div');
    wrapper.className = 'repeat-item destination-row';
    wrapper.innerHTML = `
        <div class="row g-3">
            <div class="col-md-4">
                <label>Continent</label>
                <select name="destinations[${destinationIndex}][continent_id]" class="form-select js-continent-select">
                    <option value="">-- Choose continent --</option>
                    ${continents.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-4"><label>Country</label><select name="destinations[${destinationIndex}][country_id]" class="form-select js-country-select"><option value="">-- Choose existing country --</option></select></div>
            <div class="col-md-4"><label>Or create country</label><button type="button" class="btn btn-outline-primary w-100 js-toggle-new-country">Create new country</button></div>
        </div>
        <div class="row g-3 mt-1 new-country-fields">
            <div class="col-md-3"><input name="destinations[${destinationIndex}][new_country_name_vi]" class="form-control" placeholder="Country name VI"></div>
            <div class="col-md-3"><input name="destinations[${destinationIndex}][new_country_slug_vi]" class="form-control" placeholder="slug vi"></div>
            <div class="col-md-3"><input name="destinations[${destinationIndex}][new_country_name_en]" class="form-control" placeholder="Country name EN"></div>
            <div class="col-md-2"><input name="destinations[${destinationIndex}][new_country_slug_en]" class="form-control" placeholder="slug en"></div>
            <div class="col-md-1"><input name="destinations[${destinationIndex}][new_country_code]" class="form-control" placeholder="CA"></div>
        </div>`;
    document.getElementById('destinationRows').appendChild(wrapper);
    bindDestinationRow(wrapper);
    destinationIndex++;
});

document.getElementById('addItinerary').addEventListener('click', () => {
    const div = document.createElement('div');
    div.className = 'repeat-item';
    div.innerHTML = `<div class="row g-3">
        <div class="col-md-2"><label>Day</label><input type="number" min="1" name="itinerary_days[${itineraryIndex}][day_number]" class="form-control" value="${itineraryIndex + 1}"></div>
        <div class="col-md-5"><label>Title VI</label><input name="itinerary_days[${itineraryIndex}][title_vi]" class="form-control"></div>
        <div class="col-md-5"><label>Title EN</label><input name="itinerary_days[${itineraryIndex}][title_en]" class="form-control"></div>
        <div class="col-md-4"><label>Meals</label><input name="itinerary_days[${itineraryIndex}][meals]" class="form-control"></div>
        <div class="col-md-4"><label>Hotel</label><input name="itinerary_days[${itineraryIndex}][hotel_name]" class="form-control"></div>
        <div class="col-md-4"><label>Transport</label><input name="itinerary_days[${itineraryIndex}][transport_summary]" class="form-control"></div>
        <div class="col-md-6">
            <label>Description VI</label>
            <textarea name="itinerary_days[${itineraryIndex}][description_vi]" class="form-control d-none js-rich-source"></textarea>
            <div class="rich-editor-wrap js-rich-wrap">
                <div class="rich-editor-toolbar">
                    <button type="button" data-command="bold">B</button>
                    <button type="button" data-command="italic">I</button>
                    <button type="button" data-command="insertUnorderedList">List</button>
                    <button type="button" data-command="removeFormat">Clear</button>
                </div>
                <div class="rich-editor js-rich-editor" contenteditable="true"></div>
            </div>
            <div class="help mt-1">Boi den text roi bam B de in dam diem den.</div>
        </div>
        <div class="col-md-6">
            <label>Description EN</label>
            <textarea name="itinerary_days[${itineraryIndex}][description_en]" class="form-control d-none js-rich-source"></textarea>
            <div class="rich-editor-wrap js-rich-wrap">
                <div class="rich-editor-toolbar">
                    <button type="button" data-command="bold">B</button>
                    <button type="button" data-command="italic">I</button>
                    <button type="button" data-command="insertUnorderedList">List</button>
                    <button type="button" data-command="removeFormat">Clear</button>
                </div>
                <div class="rich-editor js-rich-editor" contenteditable="true"></div>
            </div>
            <div class="help mt-1">Select text and press B to highlight destinations.</div>
        </div>
    </div>`;
    document.getElementById('itineraryRows').appendChild(div);
    bindRichEditor(div);
    itineraryIndex++;
});

document.getElementById('addMedia').addEventListener('click', () => {
    const div = document.createElement('div');
    div.className = 'repeat-item';
    div.innerHTML = `<div class="row g-3">
        <div class="col-md-3"><label>Type</label><select name="media[${mediaIndex}][type]" class="form-select"><option value="banner">Banner</option><option value="cover">Cover</option><option value="gallery">Gallery</option><option value="video">Video</option></select></div>
        <div class="col-md-5"><label>Upload image</label><input type="file" name="media_files[]" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></div>
        <div class="col-md-4"><label>Title / Alt text</label><input name="media[${mediaIndex}][alt_text]" class="form-control" placeholder="Tên địa điểm"></div>
        <input type="hidden" name="media[${mediaIndex}][file_path]" value="">
    </div>
    <div class="help mt-2">Dùng type Gallery cho slider Địa điểm khám phá.</div>`;
    document.getElementById('mediaRows').appendChild(div);
    mediaIndex++;
});

document.getElementById('addFaq').addEventListener('click', () => {
    const div = document.createElement('div');
    div.className = 'repeat-item';
    div.innerHTML = `<div class="row g-3">
        <div class="col-md-6"><label>Question VI</label><input name="faqs[${faqIndex}][question_vi]" class="form-control"></div>
        <div class="col-md-6"><label>Question EN</label><input name="faqs[${faqIndex}][question_en]" class="form-control"></div>
        <div class="col-md-6"><label>Answer VI</label><textarea name="faqs[${faqIndex}][answer_vi]" class="form-control"></textarea></div>
        <div class="col-md-6"><label>Answer EN</label><textarea name="faqs[${faqIndex}][answer_en]" class="form-control"></textarea></div>
    </div>`;
    document.getElementById('faqRows').appendChild(div);
    faqIndex++;
});
</script>
</body>
</html>
