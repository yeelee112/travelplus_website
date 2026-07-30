(function () {
    'use strict';

    const datasets = new Map();
    let controlSequence = 0;

    const normalizeText = function (value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'D')
            .toLocaleLowerCase('vi')
            .trim();
    };

    const loadDataset = function (url) {
        if (!datasets.has(url)) {
            datasets.set(url, fetch(url, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' }
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('Address dataset request failed.');
                }

                return response.json();
            }).then(function (dataset) {
                if (!dataset || dataset.province_count !== 34 || dataset.ward_count !== 3321 || !Array.isArray(dataset.provinces)) {
                    throw new Error('Address dataset is invalid.');
                }

                return dataset;
            }));
        }

        return datasets.get(url);
    };

    const getFieldLabel = function (select) {
        const explicitLabel = select.id ? document.querySelector('label[for="' + select.id + '"]') : null;
        const field = select.closest('.form-inner, .travelplus-auth-field');
        const fallbackLabel = field ? field.querySelector('label, :scope > span') : null;

        return String((explicitLabel || fallbackLabel || {}).textContent || select.name || '').trim();
    };

    const createSearchControl = function (select) {
        controlSequence += 1;

        const listId = 'travelplus-address-options-' + controlSequence;
        const wrapper = document.createElement('div');
        const input = document.createElement('input');
        const searchIcon = document.createElement('i');
        const panel = document.createElement('div');
        const list = document.createElement('div');
        const empty = document.createElement('p');
        let visibleOptions = [];
        let activeIndex = -1;

        wrapper.className = 'travelplus-search-select';
        input.type = 'search';
        input.className = 'travelplus-search-select__input';
        input.autocomplete = 'off';
        input.spellcheck = false;
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-controls', listId);
        input.setAttribute('aria-label', getFieldLabel(select));
        input.setAttribute('aria-required', select.required ? 'true' : 'false');
        searchIcon.className = 'bi bi-search travelplus-search-select__icon';
        searchIcon.setAttribute('aria-hidden', 'true');
        panel.className = 'travelplus-search-select__panel';
        panel.hidden = true;
        list.className = 'travelplus-search-select__list';
        list.id = listId;
        list.setAttribute('role', 'listbox');
        empty.className = 'travelplus-search-select__empty';
        empty.textContent = document.documentElement.lang === 'en' ? 'No matching results' : 'Không tìm thấy kết quả phù hợp';
        empty.hidden = true;

        panel.append(list, empty);
        wrapper.append(input, searchIcon, panel);
        select.classList.add('travelplus-native-select');
        select.setAttribute('aria-hidden', 'true');
        select.tabIndex = -1;
        select.insertAdjacentElement('afterend', wrapper);

        const options = function () {
            return Array.from(select.options).filter(function (option) {
                return option.value !== '';
            });
        };

        const selectedText = function () {
            const selected = select.options[select.selectedIndex];
            return select.value && selected ? selected.text.trim() : '';
        };

        const close = function (restoreValue) {
            wrapper.classList.remove('is-open');
            panel.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            activeIndex = -1;

            if (restoreValue !== false) {
                input.value = selectedText();
            }
        };

        const closeOthers = function () {
            document.querySelectorAll('.travelplus-search-select.is-open').forEach(function (other) {
                if (other !== wrapper) {
                    other.dispatchEvent(new CustomEvent('travelplus:close-search-select'));
                }
            });
        };

        const setActive = function (nextIndex) {
            if (visibleOptions.length === 0) {
                activeIndex = -1;
                return;
            }

            activeIndex = Math.max(0, Math.min(nextIndex, visibleOptions.length - 1));
            list.querySelectorAll('[role="option"]').forEach(function (button, index) {
                const active = index === activeIndex;
                button.classList.toggle('is-active', active);
                if (active) {
                    input.setAttribute('aria-activedescendant', button.id);
                    button.scrollIntoView({ block: 'nearest' });
                }
            });
        };

        const choose = function (option) {
            select.value = option.value;
            input.value = option.text.trim();
            input.setAttribute('aria-invalid', 'false');
            close(false);
            select.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const render = function (query) {
            const normalizedQuery = normalizeText(query);
            visibleOptions = options().filter(function (option) {
                return normalizedQuery === '' || normalizeText(option.text).includes(normalizedQuery);
            });
            list.replaceChildren();
            empty.hidden = visibleOptions.length !== 0;

            visibleOptions.forEach(function (option, index) {
                const button = document.createElement('button');
                button.type = 'button';
                button.id = listId + '-option-' + index;
                button.className = 'travelplus-search-select__option';
                button.textContent = option.text.trim();
                button.setAttribute('role', 'option');
                button.setAttribute('aria-selected', option.value === select.value ? 'true' : 'false');
                button.addEventListener('pointerdown', function (event) {
                    event.preventDefault();
                });
                button.addEventListener('click', function () {
                    choose(option);
                });
                list.appendChild(button);
            });

            activeIndex = visibleOptions.findIndex(function (option) {
                return option.value === select.value;
            });
            if (activeIndex >= 0) {
                setActive(activeIndex);
            } else {
                input.removeAttribute('aria-activedescendant');
            }
        };

        const open = function () {
            if (select.disabled) {
                return;
            }

            closeOthers();
            wrapper.classList.add('is-open');
            panel.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            render('');
        };

        const refresh = function () {
            input.disabled = select.disabled;
            wrapper.classList.toggle('is-disabled', select.disabled);
            input.placeholder = select.options[0] ? select.options[0].text.trim() : '';
            input.value = selectedText();
            if (select.value) {
                input.setAttribute('aria-invalid', 'false');
            } else if (input.getAttribute('aria-invalid') !== 'true') {
                input.removeAttribute('aria-invalid');
            }
            if (select.disabled) {
                close(false);
            } else if (!panel.hidden) {
                render(input.value);
            }
        };

        input.addEventListener('focus', function () {
            open();
            input.select();
        });
        input.addEventListener('click', function () {
            open();
            input.select();
        });
        input.addEventListener('input', function () {
            if (panel.hidden) {
                open();
            }
            render(input.value);
        });
        input.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (panel.hidden) {
                    open();
                }
                setActive(activeIndex + (event.key === 'ArrowDown' ? 1 : -1));
                return;
            }

            if (event.key === 'Enter' && !panel.hidden && activeIndex >= 0) {
                event.preventDefault();
                choose(visibleOptions[activeIndex]);
                return;
            }

            if (event.key === 'Escape') {
                close(true);
            }
        });
        input.addEventListener('blur', function () {
            window.setTimeout(function () {
                if (!wrapper.contains(document.activeElement)) {
                    close(true);
                }
            }, 0);
        });
        select.addEventListener('invalid', function () {
            input.setAttribute('aria-invalid', 'true');
            input.focus();
        });
        select.addEventListener('change', refresh);
        wrapper.addEventListener('travelplus:close-search-select', function () {
            wrapper.classList.remove('is-open');
            close(true);
        });
        document.addEventListener('pointerdown', function (event) {
            if (!wrapper.contains(event.target)) {
                wrapper.classList.remove('is-open');
                close(true);
            }
        });

        refresh();

        return { refresh: refresh };
    };

    const initializeSelector = function (root) {
        const source = root.dataset.addressSource || '';
        const provinceSelect = root.querySelector('[data-address-province]');
        const wardSelect = root.querySelector('[data-address-ward]');
        const addressLine = root.querySelector('[data-address-line]');
        const fullAddress = root.querySelector('[data-address-full]');
        const status = root.querySelector('[data-address-status]');
        const initialWardCode = root.dataset.selectedWard || '';
        let dataset = null;

        if (!source || !provinceSelect || !wardSelect) {
            return;
        }

        const provinceControl = createSearchControl(provinceSelect);
        const wardControl = createSearchControl(wardSelect);

        const setStatus = function (message, isError) {
            if (!status) {
                return;
            }

            status.textContent = message || '';
            status.classList.toggle('is-error', Boolean(isError));
        };

        const setWardPlaceholder = function (message) {
            wardSelect.replaceChildren(new Option(message, ''));
            wardSelect.disabled = true;
            wardControl.refresh();
        };

        const updateFullAddress = function () {
            if (!fullAddress) {
                return;
            }

            const parts = [
                addressLine ? addressLine.value.trim() : '',
                wardSelect.value ? wardSelect.options[wardSelect.selectedIndex].text.trim() : '',
                provinceSelect.value ? provinceSelect.options[provinceSelect.selectedIndex].text.trim() : ''
            ].filter(Boolean);
            const nextValue = parts.join(', ');

            if (fullAddress.value === nextValue) {
                return;
            }

            fullAddress.value = nextValue;
            fullAddress.dispatchEvent(new Event('input', { bubbles: true }));
        };

        const renderWards = function (selectedWardCode) {
            const province = dataset && dataset.provinces.find(function (item) {
                return String(item.code) === provinceSelect.value;
            });

            if (!province || !Array.isArray(province.wards)) {
                setWardPlaceholder(root.dataset.wardFirst || 'Select province/city first');
                updateFullAddress();
                return;
            }

            const fragment = document.createDocumentFragment();
            fragment.appendChild(new Option(root.dataset.wardPlaceholder || 'Select ward/commune', ''));

            province.wards.forEach(function (ward) {
                const option = new Option(String(ward.name || ''), String(ward.code || ''));
                option.selected = String(ward.code || '') === String(selectedWardCode || '');
                fragment.appendChild(option);
            });

            wardSelect.replaceChildren(fragment);
            wardSelect.disabled = false;
            wardControl.refresh();
            updateFullAddress();
        };

        setStatus(root.dataset.loading || 'Loading address data...', false);
        setWardPlaceholder(root.dataset.wardFirst || 'Select province/city first');

        loadDataset(source).then(function (loadedDataset) {
            dataset = loadedDataset;
            setStatus('', false);
            provinceControl.refresh();
            renderWards(initialWardCode);
        }).catch(function () {
            setStatus(root.dataset.error || 'Address data could not be loaded.', true);
            setWardPlaceholder(root.dataset.wardFirst || 'Select province/city first');
        });

        provinceSelect.addEventListener('change', function () {
            renderWards('');
        });
        wardSelect.addEventListener('change', updateFullAddress);
        if (addressLine) {
            addressLine.addEventListener('input', updateFullAddress);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-address-selector]').forEach(initializeSelector);
    });
}());
