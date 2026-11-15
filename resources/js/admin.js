const csrfToken = document.querySelector('input[name="_token"]')?.value || '';

const responseMessage = async (response) => {
    const result = await response.json().catch(() => ({}));
    if (!response.ok || result.success === false) {
        const validationMessages = Object.values(result.errors || {}).flat().join('\n');
        throw new Error(validationMessages || result.message || 'Permintaan tidak dapat diproses.');
    }

    return result;
};

const showError = (element, message) => {
    hideGlobalLoading();
    if (!element) {
        showSweetAlert({ icon: 'error', title: 'Permintaan gagal', text: message });
        return;
    }

    element.textContent = message;
    element.hidden = false;
};

const loadingModal = document.querySelector('[data-global-loading]');
const loadingMessage = document.querySelector('[data-global-loading-message]');
const sweetAlert = document.querySelector('[data-sweet-alert]');
let sweetAlertResolver = null;

const showGlobalLoading = (message = 'Memproses permintaan...') => {
    if (loadingMessage) loadingMessage.textContent = message;
    if (loadingModal) loadingModal.hidden = false;
};

const hideGlobalLoading = () => {
    if (loadingModal) loadingModal.hidden = true;
};

const withGlobalLoading = async (message, task) => {
    showGlobalLoading(message);
    try {
        return await task();
    } finally {
        hideGlobalLoading();
    }
};

const closeSweetAlert = () => {
    if (sweetAlert) sweetAlert.hidden = true;
    sweetAlertResolver?.();
    sweetAlertResolver = null;
};

const showSweetAlert = ({ icon = 'success', title = 'Berhasil', text = '' } = {}) => new Promise((resolve) => {
    hideGlobalLoading();
    if (!sweetAlert) {
        window.alert(text || title);
        resolve();
        return;
    }

    const titleElement = sweetAlert.querySelector('[data-sweet-alert-title]');
    const messageElement = sweetAlert.querySelector('[data-sweet-alert-message]');
    sweetAlertResolver = resolve;
    sweetAlert.dataset.state = icon;
    if (titleElement) titleElement.textContent = title;
    if (messageElement) messageElement.textContent = text;
    sweetAlert.hidden = false;
    sweetAlert.querySelector('[data-sweet-alert-confirm]')?.focus();
});

document.querySelectorAll('[data-sweet-alert-confirm]').forEach((button) => button.addEventListener('click', closeSweetAlert));

document.addEventListener('change', (event) => {
    const mediaSelect = event.target.closest('[data-media-preview-select]');
    if (mediaSelect) {
        const preview = mediaSelect.closest('.admin-field')?.querySelector('[data-media-preview]');
        const option = mediaSelect.selectedOptions[0];
        if (!preview) return;

        preview.replaceChildren();
        if (option?.dataset.imageUrl) {
            const image = document.createElement('img');
            image.src = option.dataset.imageUrl;
            image.alt = option.textContent.trim();
            image.loading = 'lazy';
            const name = document.createElement('span');
            name.textContent = option.textContent.trim();
            preview.append(image, name);
        } else {
            const empty = document.createElement('span');
            empty.textContent = 'Pilih media untuk melihat pratinjau';
            preview.append(empty);
        }
    }
});

document.querySelector('[data-admin-login-form]')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const errors = form.querySelector('[data-login-errors]');
    if (errors) errors.hidden = true;
    showGlobalLoading('Memverifikasi akun CMS...');

    try {
        const result = await responseMessage(await fetch(form.action, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin',
            body: new FormData(form),
        }));
        hideGlobalLoading();
        await showSweetAlert({ icon: 'success', title: 'Login berhasil', text: result.message || 'Anda akan diarahkan ke dashboard CMS.' });
        window.location.assign(result.data?.redirect_url || '/admin');
    } catch (error) {
        hideGlobalLoading();
        showError(errors, error.message);
    }
});

document.querySelector('[data-admin-password-form]')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    showGlobalLoading('Menyimpan password baru...');

    try {
        const result = await responseMessage(await fetch(form.action, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin',
            body: new FormData(form),
        }));
        hideGlobalLoading();
        await showSweetAlert({ icon: 'success', title: 'Password berhasil diubah', text: result.message || 'Password akun Anda sudah diperbarui.' });
        window.location.assign(result.data?.redirect_url || form.dataset.redirectUrl || '/admin');
    } catch (error) {
        hideGlobalLoading();
        await showSweetAlert({ icon: 'error', title: 'Password belum tersimpan', text: error.message });
    }
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = button.closest('.admin-password-field')?.querySelector('input');
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        const icon = button.querySelector('.material-symbols-rounded');
        if (icon) {
            icon.textContent = input.type === 'password' ? 'visibility' : 'visibility_off';
        } else {
            button.textContent = input.type === 'password' ? 'Lihat' : 'Sembunyikan';
        }
        button.setAttribute('aria-label', input.type === 'password' ? 'Tampilkan password' : 'Sembunyikan password');
    });
});

const newPasswordInput = document.querySelector('[data-new-password]');
const passwordConfirmationInput = document.querySelector('[data-password-confirmation]');
const passwordMatchMessage = document.querySelector('[data-password-match]');

const updatePasswordGuidance = () => {
    if (!newPasswordInput) return;
    const value = newPasswordInput.value;
    const minimumLength = Number(newPasswordInput.dataset.passwordMinLength || 8);
    const requirements = {
        length: value.length >= minimumLength,
        lowercase: /[a-z]/.test(value),
        uppercase: /[A-Z]/.test(value),
        number: /\d/.test(value),
    };

    Object.entries(requirements).forEach(([key, isValid]) => {
        const item = document.querySelector(`[data-password-rule="${key}"]`);
        item?.classList.toggle('is-valid', isValid);
        const icon = item?.querySelector('.material-symbols-rounded');
        if (icon) icon.textContent = isValid ? 'check_circle' : 'circle';
    });

    if (passwordConfirmationInput && passwordMatchMessage) {
        const hasConfirmation = passwordConfirmationInput.value !== '';
        const matches = hasConfirmation && passwordConfirmationInput.value === value;
        passwordMatchMessage.textContent = matches
            ? 'Password baru cocok.'
            : (hasConfirmation ? 'Konfirmasi belum sama dengan password baru.' : 'Masukkan kembali password baru untuk konfirmasi.');
        passwordMatchMessage.classList.toggle('is-valid', matches);
        passwordMatchMessage.classList.toggle('is-invalid', hasConfirmation && !matches);
    }
};

newPasswordInput?.addEventListener('input', updatePasswordGuidance);
passwordConfirmationInput?.addEventListener('input', updatePasswordGuidance);
updatePasswordGuidance();

const adminShell = document.querySelector('[data-admin-shell]');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarClose = document.querySelector('[data-sidebar-close]');
const mobileSidebarQuery = window.matchMedia('(max-width: 760px)');

if (mobileSidebarQuery.matches) {
    sidebarToggle?.setAttribute('aria-expanded', 'false');
}

sidebarToggle?.addEventListener('click', () => {
    if (!adminShell) return;
    if (mobileSidebarQuery.matches) {
        const isOpen = adminShell.classList.toggle('is-sidebar-open');
        sidebarToggle.setAttribute('aria-expanded', String(isOpen));
        if (sidebarClose) sidebarClose.hidden = !isOpen;
        return;
    }

    const isCollapsed = adminShell.classList.toggle('is-sidebar-collapsed');
    sidebarToggle.setAttribute('aria-expanded', String(!isCollapsed));
});

sidebarClose?.addEventListener('click', () => {
    adminShell?.classList.remove('is-sidebar-open');
    sidebarToggle?.setAttribute('aria-expanded', 'false');
    sidebarClose.hidden = true;
});

mobileSidebarQuery.addEventListener('change', () => {
    adminShell?.classList.remove('is-sidebar-open', 'is-sidebar-collapsed');
    sidebarToggle?.setAttribute('aria-expanded', String(!mobileSidebarQuery.matches));
    if (sidebarClose) sidebarClose.hidden = true;
});

const navSearchInputs = [...document.querySelectorAll('[data-admin-nav-search]')];
const navLinks = [...document.querySelectorAll('[data-nav-link]')];
const navSections = [...document.querySelectorAll('[data-nav-section]')];

const setSectionExpanded = (section, isExpanded) => {
    const toggle = section.querySelector('[data-nav-section-toggle]');
    const content = section.querySelector('[data-nav-section-content]');
    if (!toggle || !content) return;
    toggle.setAttribute('aria-expanded', String(isExpanded));
    content.hidden = !isExpanded;
};

navSections.forEach((section) => {
    section.querySelector('[data-nav-section-toggle]')?.addEventListener('click', () => {
        const toggle = section.querySelector('[data-nav-section-toggle]');
        setSectionExpanded(section, toggle?.getAttribute('aria-expanded') !== 'true');
    });
});

navSearchInputs.forEach((input) => {
    input.addEventListener('input', () => {
        const query = input.value.trim().toLocaleLowerCase('id');
        navSearchInputs.forEach((otherInput) => {
            if (otherInput !== input) otherInput.value = input.value;
        });
        navLinks.forEach((link) => {
            link.hidden = query !== '' && !link.textContent.trim().toLocaleLowerCase('id').includes(query);
        });
        document.querySelectorAll('[data-nav-category]').forEach((category) => {
            const heading = category.querySelector('.admin-nav__heading')?.textContent.trim().toLocaleLowerCase('id') || '';
            if (query !== '' && heading.includes(query)) {
                category.querySelectorAll('[data-nav-link]').forEach((link) => { link.hidden = false; });
            }
        });
        navSections.forEach((section) => {
            const toggle = section.querySelector('[data-nav-section-toggle]');
            const sectionLabel = toggle?.textContent.trim().toLocaleLowerCase('id') || '';
            if (query !== '' && sectionLabel.includes(query)) {
                section.querySelectorAll('[data-nav-link]').forEach((link) => { link.hidden = false; });
            }
            const links = [...section.querySelectorAll('[data-nav-link]')];
            const hasVisibleLink = links.some((link) => !link.hidden);
            section.hidden = query !== '' && !hasVisibleLink;
            if (query !== '' && hasVisibleLink) {
                setSectionExpanded(section, true);
            } else if (query === '') {
                setSectionExpanded(section, toggle?.dataset.initialExpanded === 'true');
            }
        });
        document.querySelectorAll('[data-nav-category]').forEach((category) => {
            const links = [...category.querySelectorAll('[data-nav-link]')];
            category.hidden = query !== '' && !links.some((link) => !link.hidden);
        });
    });
});

document.querySelectorAll('[data-nav-section-toggle]').forEach((toggle) => {
    toggle.dataset.initialExpanded = toggle.getAttribute('aria-expanded') === 'true' ? 'true' : 'false';
});

document.querySelector('[data-notification-toggle]')?.addEventListener('click', (event) => {
    const button = event.currentTarget;
    const panel = document.querySelector('[data-notification-panel]');
    if (!panel) return;
    const isOpening = panel.hidden;
    panel.hidden = !isOpening;
    button.setAttribute('aria-expanded', String(isOpening));
});

document.addEventListener('click', (event) => {
    const notification = document.querySelector('.admin-notifications');
    const panel = document.querySelector('[data-notification-panel]');
    const button = document.querySelector('[data-notification-toggle]');
    if (notification && panel && !notification.contains(event.target)) {
        panel.hidden = true;
        button?.setAttribute('aria-expanded', 'false');
    }
});

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    const body = document.body;
    const icon = button.querySelector('[data-theme-icon]');
    try {
        const savedTheme = window.localStorage.getItem('oneis-cms-theme');
        if (savedTheme === 'dark') body.dataset.adminTheme = 'dark';
    } catch { }
    if (icon) icon.textContent = body.dataset.adminTheme === 'dark' ? 'light_mode' : 'dark_mode';

    button.addEventListener('click', () => {
        body.dataset.adminTheme = body.dataset.adminTheme === 'dark' ? 'light' : 'dark';
        if (icon) icon.textContent = body.dataset.adminTheme === 'dark' ? 'light_mode' : 'dark_mode';
        try {
            window.localStorage.setItem('oneis-cms-theme', body.dataset.adminTheme);
        } catch { }
    });
});

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm || 'Apakah Anda yakin ingin menghapus data ini?')) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-language-tab]').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('[data-language-tab]').forEach((item) => item.classList.toggle('is-active', item === tab));
        document.querySelectorAll('[data-language-panel]').forEach((panel) => panel.classList.toggle('is-hidden', panel.dataset.languagePanel !== tab.dataset.languageTab));
    });
});

const reindexRepeater = (list) => {
    const [type] = String(list.dataset.repeaterList || '').split('-');
    [...list.children].forEach((row, index) => {
        row.querySelectorAll('[name]').forEach((field) => {
            field.name = field.name.replace(new RegExp(`\\[${type}\\]\\[(?:__index__|\\d+)\\]`, 'g'), `[${type}][${index}]`);
        });
    });
};

const initializeLiveEditor = (form) => {
    const editorType = form.dataset.liveEditor;
    const previewPanel = form.querySelector('.admin-editor-preview');

    const updatePreview = () => {
        const activePanel = form.querySelector('[data-language-panel]:not(.is-hidden)') || form.querySelector('[data-language-panel="id"]');
        const language = activePanel?.dataset.languagePanel || 'id';
        const valueFor = (name) => activePanel?.querySelector(`[data-preview-field="${name}"]`)?.value?.trim() || '';
        const setText = (name, fallback = '', hideWhenEmpty = false) => {
            const output = form.querySelector(`[data-preview-output="${name}"]`);
            if (output) {
                const value = valueFor(name) || fallback;
                output.textContent = value;
                if (hideWhenEmpty) output.hidden = !value;
            }
        };

        setText('title', editorType === 'cta' ? '' : (form.dataset.previewTitleFallback || (editorType === 'hero' ? 'Judul banner tampil di sini' : 'Judul jangkauan')), editorType === 'cta');
        setText('subtitle', '');
        setText('body', editorType === 'cta' ? '' : (form.dataset.previewBodyFallback || 'Deskripsi jangkauan tampil di sini.'), editorType === 'cta');
        setText('cta_label', 'Hubungi Kami');

        const mediaSelect = form.querySelector('[data-hero-media], [data-block-media]');
        const imageUrl = mediaSelect?.selectedOptions?.[0]?.dataset.imageUrl || '';
        const heroPreview = form.querySelector('[data-hero-preview]');
        if (heroPreview) {
            if (imageUrl) heroPreview.style.setProperty('--hero-preview-image', `url("${imageUrl}")`);
            else heroPreview.style.removeProperty('--hero-preview-image');
        }
        const blockImage = form.querySelector('[data-block-image-preview]');
        if (blockImage && mediaSelect) {
            blockImage.replaceChildren();
            if (imageUrl) {
                const image = document.createElement('img');
                image.src = imageUrl;
                image.alt = mediaSelect.selectedOptions[0].textContent.trim();
                blockImage.append(image);
            } else {
                const empty = document.createElement('span');
                empty.textContent = blockImage.dataset.emptyText || 'Pilih gambar ilustrasi atau peta';
                blockImage.append(empty);
            }
        }

        const metricsPreview = form.querySelector('[data-preview-metrics]');
        if (metricsPreview) {
            const metricList = form.querySelector(`[data-repeater-list="metrics-${language}"]`);
            const metrics = [...(metricList?.querySelectorAll('[data-repeater-item]') || [])]
                .map((row) => ({ value: row.querySelector('[data-metric-value]')?.value.trim() || '', label: row.querySelector('[data-metric-label]')?.value.trim() || '' }))
                .filter((metric) => metric.value && metric.label);
            let previewMetrics = metrics;
            if (previewMetrics.length === 0 && !metricList?.children.length) {
                try {
                    previewMetrics = JSON.parse(metricsPreview.dataset.defaultMetrics || '[]');
                } catch {
                    previewMetrics = [];
                }
            }
            metricsPreview.replaceChildren();
            previewMetrics.forEach((metric) => {
                const item = document.createElement('div');
                const value = document.createElement('strong');
                const label = document.createElement('span');
                value.textContent = metric.value || '';
                label.textContent = metric.label || '';
                item.append(value, label);
                metricsPreview.append(item);
            });
        }

        const buttonsPreview = form.querySelector('[data-preview-buttons]');
        const buttonList = form.querySelector(`[data-repeater-list="buttons-${language}"]`);
        if (buttonsPreview && buttonList) {
            const buttons = [...buttonList.querySelectorAll('[data-repeater-item]')]
                .map((row) => ({
                    label: row.querySelector('[data-button-label]')?.value.trim() || '',
                    url: row.querySelector('[data-button-url]')?.value.trim() || '',
                    style: row.querySelector('[data-button-style]')?.value || 'primary',
                    icon: row.querySelector('[data-button-icon]')?.value.trim() || '',
                }))
                .filter((button) => button.label && button.url);
            buttonsPreview.replaceChildren();
            buttons.forEach((button) => {
                const item = document.createElement('span');
                const style = ['primary', 'secondary', 'tertiary'].includes(button.style) ? button.style : 'primary';
                item.className = `contact-cta__button${style === 'primary' ? '' : ` contact-cta__button--${style}`}`;
                item.textContent = button.label;
                if (button.icon) {
                    const icon = document.createElement('span');
                    icon.className = 'material-symbols-rounded';
                    icon.setAttribute('aria-hidden', 'true');
                    icon.textContent = button.icon;
                    item.prepend(icon);
                }
                buttonsPreview.append(item);
            });

            if (buttons.length === 0) {
                const legacyLabel = form.querySelector(`[data-legacy-cta-label="${language}"]`)?.value.trim() || '';
                const legacyUrl = form.querySelector('[data-legacy-cta-url]')?.value.trim() || '';
                if (legacyLabel && legacyUrl) {
                    const item = document.createElement('span');
                    item.className = 'contact-cta__button';
                    item.textContent = legacyLabel;
                    buttonsPreview.append(item);
                }
            }

            const legacyLabel = form.querySelector(`[data-legacy-cta-label="${language}"]`);
            if (legacyLabel) legacyLabel.value = buttonList.querySelector('[data-button-label]')?.value.trim() || '';
            if (language === 'id') {
                const legacyUrl = form.querySelector('[data-legacy-cta-url]');
                if (legacyUrl) legacyUrl.value = buttonList.querySelector('[data-button-url]')?.value.trim() || '';
            }
        }
    };

    form.addEventListener('input', updatePreview);
    form.addEventListener('change', updatePreview);
    form.querySelectorAll('[data-language-tab]').forEach((tab) => tab.addEventListener('click', updatePreview));
    form.querySelectorAll('[data-set-status]').forEach((button) => {
        button.addEventListener('click', () => {
            const status = button.dataset.setStatus;
            form.querySelector('[data-editor-status]').value = status;
            form.querySelectorAll('[data-set-status]').forEach((option) => option.classList.toggle('is-selected', option === button));
            const statusLabel = form.querySelector('[data-editor-status-label]');
            if (statusLabel) {
                statusLabel.textContent = status === 'published' ? 'Published' : 'Draft';
                statusLabel.className = `admin-status admin-status--${status}`;
            }
        });
    });
    form.querySelectorAll('[data-preview-device]').forEach((button) => {
        button.addEventListener('click', () => {
            form.querySelectorAll('[data-preview-device]').forEach((option) => option.classList.toggle('is-active', option === button));
            previewPanel?.classList.toggle('is-mobile-preview', button.dataset.previewDevice === 'mobile');
        });
    });
    updatePreview();
};

document.querySelectorAll('[data-live-editor]').forEach(initializeLiveEditor);

document.addEventListener('click', (event) => {
    const addButton = event.target.closest('[data-repeater-add]');
    if (addButton) {
        const repeaterName = addButton.dataset.repeaterAdd;
        const list = document.querySelector(`[data-repeater-list="${repeaterName}"]`);
        const template = document.querySelector(`template[data-repeater-template="${repeaterName}"]`);
        if (!list || !template) return;
        const maxItems = repeaterName.startsWith('metrics-') ? 6 : 4;
        if (list.children.length >= maxItems) {
            showSweetAlert({ icon: 'info', title: 'Batas item tercapai', text: `Maksimal ${maxItems} item dapat ditambahkan.` });
            return;
        }
        list.append(template.content.cloneNode(true));
        reindexRepeater(list);
        list.closest('form')?.dispatchEvent(new Event('input', { bubbles: true }));
        return;
    }

    const removeButton = event.target.closest('[data-repeater-remove]');
    if (removeButton) {
        const list = removeButton.closest('[data-repeater-list]');
        removeButton.closest('[data-repeater-item]')?.remove();
        if (list) reindexRepeater(list);
        list?.closest('form')?.dispatchEvent(new Event('input', { bubbles: true }));
    }
});

const normalizeEditorRepeaters = (formData, form) => {
    for (const language of ['id', 'en']) {
        for (const type of ['metrics', 'buttons']) {
            const list = form.querySelector(`[data-repeater-list="${type}-${language}"]`);
            if (!list) continue;
            const prefix = `translations[${language}][${type}]`;
            [...new Set([...formData.keys()].filter((name) => name.startsWith(prefix)))].forEach((name) => formData.delete(name));
            formData.delete(`translations[${language}][${type}_present]`);
            formData.append(`translations[${language}][${type}_present]`, '1');

            [...list.querySelectorAll('[data-repeater-item]')].forEach((row, index) => {
                const fields = type === 'metrics'
                    ? { value: row.querySelector('[data-metric-value]')?.value.trim() || '', label: row.querySelector('[data-metric-label]')?.value.trim() || '' }
                    : {
                        label: row.querySelector('[data-button-label]')?.value.trim() || '',
                        url: row.querySelector('[data-button-url]')?.value.trim() || '',
                        style: row.querySelector('[data-button-style]')?.value || 'primary',
                        icon: row.querySelector('[data-button-icon]')?.value.trim() || '',
                    };
                const hasContent = type === 'metrics'
                    ? Boolean(fields.value || fields.label)
                    : Boolean(fields.label || fields.url);
                if (!hasContent) return;
                Object.entries(fields).forEach(([field, value]) => formData.append(`${prefix}[${index}][${field}]`, value));
            });
        }
    }

    if (form.dataset.liveEditor === 'cta') {
        for (const language of ['id', 'en']) {
            const firstLabel = form.querySelector(`[data-repeater-list="buttons-${language}"] [data-button-label]`)?.value.trim() || '';
            if (form.querySelector(`[data-legacy-cta-label="${language}"]`)) formData.set(`translations[${language}][cta_label]`, firstLabel);
        }
        const firstUrl = form.querySelector('[data-repeater-list="buttons-id"] [data-button-url]')?.value.trim() || '';
        if (form.querySelector('[data-legacy-cta-url]')) formData.set('cta_url', firstUrl);
    }
};

const submitContentForm = async (form, event) => {
    event.preventDefault();
    const errors = form.querySelector('[data-form-errors]');
    errors.hidden = true;
    const submitButton = event.submitter;
    const resource = form.dataset.resource;
    const isEditing = form.dataset.isEditing === '1';
    const formData = new FormData(form);
    if (submitButton?.dataset.saveStatus) {
        formData.set('status', submitButton.dataset.saveStatus);
    }
    normalizeEditorRepeaters(formData, form);

    if (resource === 'products') {
        const parameters = String(formData.get('parameters') || '').split(',').map((item) => item.trim()).filter(Boolean);
        formData.delete('parameters');
        parameters.forEach((parameter) => formData.append('parameters[]', parameter));
    }

    if (resource === 'documentations') {
        const selectedMedia = [...form.querySelectorAll('[name="gallery_media_ids[]"]:checked')];
        formData.set('gallery_present', '1');
        formData.delete('gallery_media_ids[]');
        selectedMedia.forEach((input, index) => {
            const galleryItem = input.closest('[data-gallery-item]');
            formData.append(`gallery[${index}][media_id]`, input.value);
            formData.append(`gallery[${index}][caption_id]`, galleryItem?.querySelector('[data-gallery-caption-id]')?.value || '');
            formData.append(`gallery[${index}][caption_en]`, galleryItem?.querySelector('[data-gallery-caption-en]')?.value || '');
        });
    }

    let endpoint = form.dataset.apiUrl;
    let method = isEditing ? 'PUT' : 'POST';
    if (['company-summary', 'contact-info'].includes(resource)) method = 'PUT';
    if (resource === 'content-blocks') {
        const blockKey = formData.get('block_key');
        endpoint = `/api/v1/admin/content-blocks/${encodeURIComponent(blockKey)}`;
        method = 'PUT';
    }
    if (resource === 'seo-metas') {
        const pageKey = formData.get('page_key');
        const language = formData.get('lang') || 'id';
        endpoint = `/api/v1/admin/seo-metas/${encodeURIComponent(pageKey)}?lang=${encodeURIComponent(language)}`;
        method = 'PUT';
    }

    formData.set('_method', method);
    try {
        const result = await withGlobalLoading('Menyimpan perubahan...', async () => responseMessage(await fetch(endpoint, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            body: formData,
        })));
        await showSweetAlert({ icon: 'success', title: 'Perubahan tersimpan', text: result.message || 'Konten berhasil disimpan.' });
        window.location.assign(form.dataset.redirectUrl || `/admin/content/${resource}`);
    } catch (error) {
        showError(errors, error.message);
    }
};

const refreshCertificateBatch = (form) => {
    const entries = [...form.querySelectorAll('[data-certificate-entry]')];
    const tabs = form.parentElement.querySelector('[data-certificate-entry-tabs]');
    const count = form.parentElement.querySelector('[data-certificate-entry-count]');
    const total = form.querySelector('[data-certificate-entry-total]');

    if (tabs) {
        tabs.replaceChildren();
        entries.forEach((entry, index) => {
            const title = entry.querySelector('[data-certificate-title]')?.value.trim() || `Sertifikat Baru #${index + 1}`;
            const tab = document.createElement('button');
            tab.type = 'button';
            tab.dataset.certificateEntryTab = String(index);
            tab.className = `admin-certificate-batch-tab${index === Number(form.dataset.activeIndex || 0) ? ' is-active' : ''}`;
            tab.textContent = `${index + 1}  ${title}`;
            tabs.append(tab);
            entry.querySelector('[data-certificate-entry-number]').textContent = String(index + 1);
            entry.querySelector('[data-certificate-entry-label]').textContent = `#${index + 1}`;
            entry.querySelector('[data-certificate-entry-toggle] .material-symbols-rounded').textContent = entry.classList.contains('is-collapsed') ? 'expand_more' : 'expand_less';
            entry.querySelector('[data-remove-certificate-entry]').hidden = entries.length === 1;
            const order = entry.querySelector('[data-certificate-field="display_order"]');
            if (order && !order.value) order.placeholder = String(index + 1);

            const titleOutput = entry.querySelector('[data-certificate-entry-title]');
            if (titleOutput) titleOutput.textContent = title || '(Belum diberi judul)';
            const ready = Boolean(title && entry.querySelector('[data-certificate-field="media_id"]')?.value);
            const state = entry.querySelector('[data-certificate-entry-state]');
            if (state) {
                state.textContent = ready ? 'Siap' : (entry.dataset.recordId ? 'Tersimpan' : 'Draft');
                state.className = `admin-status ${ready ? 'admin-status--published' : 'admin-status--draft'}`;
            }
        });
    }

    if (count) count.textContent = `${entries.length} item`;
    if (total) total.textContent = `${entries.length} Sertifikat`;
};

document.querySelectorAll('[data-certificate-batch]').forEach((form) => {
    const template = document.querySelector('[data-certificate-entry-template]');
    const list = form.querySelector('[data-certificate-entry-list]');
    const errors = form.querySelector('[data-certificate-batch-error]');
    const addEntry = () => {
        if (!template || !list) return;
        list.append(template.content.cloneNode(true));
        form.dataset.activeIndex = String(list.children.length - 1);
        refreshCertificateBatch(form);
        list.lastElementChild?.querySelector('[data-certificate-title]')?.focus();
    };

    addEntry();
    form.parentElement.querySelector('[data-add-certificate-entry]')?.addEventListener('click', addEntry);
    form.addEventListener('input', () => refreshCertificateBatch(form));
    form.addEventListener('change', () => refreshCertificateBatch(form));
    form.addEventListener('click', (event) => {
        const entryTab = event.target.closest('[data-certificate-entry-tab]');
        if (entryTab) {
            form.dataset.activeIndex = entryTab.dataset.certificateEntryTab;
            const entry = form.querySelectorAll('[data-certificate-entry]')[Number(form.dataset.activeIndex)];
            if (entry?.classList.contains('is-collapsed')) {
                entry.classList.remove('is-collapsed');
                entry.querySelector('[data-certificate-entry-body]').hidden = false;
                entry.querySelector('[data-certificate-entry-toggle]').setAttribute('aria-expanded', 'true');
            }
            entry?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            refreshCertificateBatch(form);
            return;
        }

        const languageTab = event.target.closest('[data-batch-language]');
        if (languageTab) {
            const entry = languageTab.closest('[data-certificate-entry]');
            entry.querySelectorAll('[data-batch-language]').forEach((tab) => tab.classList.toggle('is-active', tab === languageTab));
            entry.querySelectorAll('[data-batch-language-panel]').forEach((panel) => panel.classList.toggle('is-hidden', panel.dataset.batchLanguagePanel !== languageTab.dataset.batchLanguage));
            return;
        }

        const toggle = event.target.closest('[data-certificate-entry-toggle]');
        if (toggle) {
            const entry = toggle.closest('[data-certificate-entry]');
            entry.classList.toggle('is-collapsed');
            toggle.setAttribute('aria-expanded', String(!entry.classList.contains('is-collapsed')));
            entry.querySelector('[data-certificate-entry-body]').hidden = entry.classList.contains('is-collapsed');
            refreshCertificateBatch(form);
            return;
        }

        const remove = event.target.closest('[data-remove-certificate-entry]');
        if (remove && form.querySelectorAll('[data-certificate-entry]').length > 1) {
            const removedIndex = [...form.querySelectorAll('[data-certificate-entry]')].indexOf(remove.closest('[data-certificate-entry]'));
            const activeIndex = Number(form.dataset.activeIndex || 0);
            remove.closest('[data-certificate-entry]').remove();
            const lastIndex = form.querySelectorAll('[data-certificate-entry]').length - 1;
            const nextActiveIndex = removedIndex === activeIndex ? Math.min(removedIndex, lastIndex) : (removedIndex < activeIndex ? activeIndex - 1 : activeIndex);
            form.dataset.activeIndex = String(nextActiveIndex);
            refreshCertificateBatch(form);
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (errors) errors.hidden = true;
        const entries = [...form.querySelectorAll('[data-certificate-entry]')];
        const status = event.submitter?.dataset.certificateBatchStatus || 'draft';
        if (status === 'published') {
            const missingEntry = entries.find((entry) => !entry.querySelector('[data-certificate-title]')?.value.trim() || !entry.querySelector('[data-certificate-field="media_id"]')?.value);
            if (missingEntry) {
                const itemNumber = entries.indexOf(missingEntry) + 1;
                showError(errors, `Sertifikat #${itemNumber} memerlukan judul Bahasa Indonesia dan gambar atau dokumen sebelum diterbitkan.`);
                missingEntry.querySelector('[data-certificate-title]')?.focus();
                return;
            }
        }

        let savedCount = 0;
        try {
            await withGlobalLoading(`Menyimpan ${entries.length} sertifikat...`, async () => {
                for (const [index, entry] of entries.entries()) {
                    const formData = new FormData();
                    formData.set('_token', csrfToken);
                    formData.set('status', status);
                    formData.set('media_id', entry.querySelector('[data-certificate-field="media_id"]')?.value || '');
                    formData.set('issued_year', entry.querySelector('[data-certificate-field="issued_year"]')?.value || '');
                    formData.set('display_order', entry.querySelector('[data-certificate-field="display_order"]')?.value || String(index + 1));
                    entry.querySelectorAll('[data-certificate-field^="translations["]').forEach((field) => formData.set(field.dataset.certificateField, field.value));
                    const recordId = entry.dataset.recordId;
                    formData.set('_method', recordId ? 'PUT' : 'POST');
                    const endpoint = recordId ? `${form.dataset.apiUrl}/${recordId}` : form.dataset.apiUrl;
                    const result = await responseMessage(await fetch(endpoint, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
                        body: formData,
                    }));
                    if (result.data?.id) entry.dataset.recordId = result.data.id;
                    savedCount += 1;
                }
            });
            await showSweetAlert({ icon: 'success', title: 'Sertifikat tersimpan', text: `${savedCount} sertifikat berhasil disimpan${status === 'published' ? ' dan diterbitkan' : ' sebagai draft'}.` });
            window.location.assign(form.dataset.redirectUrl);
        } catch (error) {
            const partialProgress = savedCount > 0 ? `${savedCount} item sudah tersimpan. ` : '';
            showError(errors, `${partialProgress}${error.message}`);
        }
    });
});

document.querySelectorAll('[data-admin-form]').forEach((form) => form.addEventListener('submit', (event) => submitContentForm(form, event)));

document.querySelectorAll('[data-status-url]').forEach((button) => {
    button.addEventListener('click', async () => {
        if (!window.confirm(`Ubah status konten menjadi ${button.dataset.nextStatus}?`)) return;
        try {
            const result = await withGlobalLoading('Memperbarui status...', async () => responseMessage(await fetch(button.dataset.statusUrl, {
                method: 'PATCH',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ status: button.dataset.nextStatus }),
            })));
            await showSweetAlert({ icon: 'success', title: 'Status diperbarui', text: result.message || 'Status konten berhasil diperbarui.' });
            window.location.reload();
        } catch (error) {
            await showSweetAlert({ icon: 'error', title: 'Status gagal diperbarui', text: error.message });
        }
    });
});

document.querySelectorAll('[data-delete-url]').forEach((button) => {
    button.addEventListener('click', async () => {
        if (!window.confirm('Apakah Anda yakin ingin menghapus data ini?')) return;
        try {
            const result = await withGlobalLoading('Menghapus konten...', async () => responseMessage(await fetch(button.dataset.deleteUrl, {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            })));
            await showSweetAlert({ icon: 'success', title: 'Konten dihapus', text: result.message || 'Konten berhasil dihapus.' });
            window.location.reload();
        } catch (error) {
            await showSweetAlert({ icon: 'error', title: 'Konten gagal dihapus', text: error.message });
        }
    });
});

document.querySelectorAll('[data-reorder-direction]').forEach((button) => {
    button.addEventListener('click', async () => {
        const row = button.closest('[data-reorder-id], [data-slide-row], [data-category-row]');
        const list = button.closest('[data-reorder-list]');
        if (!row || !list) return;

        const ids = list.dataset.reorderList.split(',').filter(Boolean);
        const recordId = row.dataset.reorderId || row.dataset.slideId;
        const resource = list.dataset.reorderResource || 'hero-slides';
        const contentType = resource === 'hero-slides' ? 'slide' : 'kategori';
        const currentIndex = ids.indexOf(recordId);
        const nextIndex = currentIndex + (button.dataset.reorderDirection === 'up' ? -1 : 1);
        if (currentIndex < 0 || nextIndex < 0 || nextIndex >= ids.length) return;
        [ids[currentIndex], ids[nextIndex]] = [ids[nextIndex], ids[currentIndex]];

        try {
            const result = await withGlobalLoading(`Menyimpan urutan ${contentType}...`, async () => responseMessage(await fetch(`/api/v1/admin/${resource}/reorder`, {
                method: 'PUT',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ ids: ids.map(Number) }),
            })));
            await showSweetAlert({ icon: 'success', title: 'Urutan diperbarui', text: result.message || `Urutan ${contentType} berhasil disimpan.` });
            window.location.reload();
        } catch (error) {
            await showSweetAlert({ icon: 'error', title: 'Urutan gagal disimpan', text: error.message });
        }
    });
});

document.querySelectorAll('[data-gallery-move]').forEach((button) => {
    button.addEventListener('click', () => {
        const item = button.closest('[data-gallery-item]');
        const sibling = button.dataset.galleryMove === 'up' ? item?.previousElementSibling : item?.nextElementSibling;
        if (!item || !sibling) return;
        if (button.dataset.galleryMove === 'up') item.parentElement.insertBefore(item, sibling);
        else item.parentElement.insertBefore(sibling, item);
    });
});

const categoryModal = document.querySelector('[data-category-modal]');
if (categoryModal) {
    const categoryForm = categoryModal.querySelector('[data-admin-form]');
    const categoryFields = {
        nameId: categoryModal.querySelector('#category-name-id'),
        slugId: categoryModal.querySelector('#category-slug-id'),
        nameEn: categoryModal.querySelector('#category-name-en'),
        slugEn: categoryModal.querySelector('#category-slug-en'),
        order: categoryModal.querySelector('#category-display-order'),
    };
    const categoryTitle = categoryModal.querySelector('[data-category-modal-title]');
    const categoryPreview = categoryModal.querySelector('[data-category-preview]');
    const categoryPreviewLanguage = categoryModal.querySelector('[data-category-preview-language]');
    let activeCategoryLanguage = 'id';
    let categorySlugIdWasEdited = false;
    let categorySlugEnWasEdited = false;

    const slugifyCategory = (value) => value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 100);

    const updateCategoryPreview = () => {
        const field = activeCategoryLanguage === 'id' ? categoryFields.nameId : categoryFields.nameEn;
        categoryPreview.textContent = field.value.trim() || categoryFields.nameId.value.trim() || '(Nama kategori)';
        categoryPreviewLanguage.textContent = activeCategoryLanguage.toUpperCase();
    };

    const setCategoryLanguage = (language) => {
        activeCategoryLanguage = language;
        categoryModal.querySelectorAll('[data-category-language]').forEach((tab) => {
            const selected = tab.dataset.categoryLanguage === language;
            tab.classList.toggle('is-active', selected);
            tab.setAttribute('aria-selected', String(selected));
        });
        updateCategoryPreview();
    };

    const closeCategoryModal = () => {
        categoryModal.hidden = true;
        document.body.classList.remove('admin-category-modal-open');
    };

    const openCategoryModal = (record = null) => {
        categoryForm.reset();
        categoryForm.dataset.isEditing = record ? '1' : '0';
        categoryForm.dataset.apiUrl = record
            ? `/api/v1/admin/product-categories/${record.id}`
            : '/api/v1/admin/product-categories';
        categorySlugIdWasEdited = Boolean(record?.slugId);
        categorySlugEnWasEdited = Boolean(record?.slugEn);
        categoryFields.nameId.value = record?.nameId || '';
        categoryFields.slugId.value = record?.slugId || '';
        categoryFields.nameEn.value = record?.nameEn || '';
        categoryFields.slugEn.value = record?.slugEn || '';
        categoryFields.order.value = record?.order || String(Number(categoryModal.dataset.nextOrder || 1));
        categoryModal.querySelectorAll('[name="status"]').forEach((radio) => {
            radio.checked = radio.value === (record?.status || 'draft');
        });
        categoryModal.querySelector('[data-form-errors]').hidden = true;
        categoryTitle.textContent = record ? 'Ubah Kategori Pemeriksaan' : 'Tambah Kategori Pemeriksaan';
        setCategoryLanguage('id');
        hideGlobalLoading();
        categoryModal.hidden = false;
        document.body.classList.add('admin-category-modal-open');
        categoryFields.nameId.focus();
    };

    document.querySelector('[data-open-category-modal]')?.addEventListener('click', () => openCategoryModal());
    document.querySelectorAll('[data-edit-category]').forEach((button) => {
        button.addEventListener('click', () => openCategoryModal({
            id: button.dataset.categoryId,
            nameId: button.dataset.categoryNameId,
            slugId: button.dataset.categorySlugId,
            nameEn: button.dataset.categoryNameEn,
            slugEn: button.dataset.categorySlugEn,
            order: button.dataset.categoryOrder,
            status: button.dataset.categoryStatus,
        }));
    });
    categoryModal.querySelectorAll('[data-close-category-modal]').forEach((button) => button.addEventListener('click', closeCategoryModal));
    categoryModal.querySelectorAll('[data-category-language]').forEach((button) => {
        button.addEventListener('click', () => setCategoryLanguage(button.dataset.categoryLanguage));
    });
    categoryFields.nameId.addEventListener('input', () => {
        if (!categorySlugIdWasEdited) categoryFields.slugId.value = slugifyCategory(categoryFields.nameId.value);
        updateCategoryPreview();
    });
    categoryFields.nameEn.addEventListener('input', () => {
        if (!categorySlugEnWasEdited) categoryFields.slugEn.value = slugifyCategory(categoryFields.nameEn.value);
        updateCategoryPreview();
    });
    categoryFields.slugId.addEventListener('input', () => { categorySlugIdWasEdited = categoryFields.slugId.value !== ''; });
    categoryFields.slugEn.addEventListener('input', () => { categorySlugEnWasEdited = categoryFields.slugEn.value !== ''; });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !categoryModal.hidden) closeCategoryModal();
    });
}

document.querySelectorAll('[data-media-upload]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const errors = form.querySelector('[data-media-errors]');
        errors.hidden = true;
        try {
            const result = await withGlobalLoading('Mengunggah media...', async () => responseMessage(await fetch(form.dataset.apiUrl, {
                method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: new FormData(form),
            })));
            await showSweetAlert({ icon: 'success', title: 'Media berhasil diunggah', text: result.message || 'File sudah tersimpan di Media Library.' });
            window.location.reload();
        } catch (error) {
            showError(errors, error.message);
        }
    });
});

document.querySelectorAll('[data-media-save]').forEach((button) => {
    button.addEventListener('click', async () => {
        const card = button.closest('[data-media-id]');
        const payload = {
            alt_text_id: card.querySelector('[name="alt_text_id"]').value,
            alt_text_en: card.querySelector('[name="alt_text_en"]').value,
        };
        try {
            const result = await withGlobalLoading('Menyimpan teks alternatif...', async () => responseMessage(await fetch(card.dataset.apiUrl, {
                method: 'PUT', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload),
            })));
            await showSweetAlert({ icon: 'success', title: 'Teks alternatif tersimpan', text: result.message || 'Metadata media berhasil diperbarui.' });
        } catch (error) {
            await showSweetAlert({ icon: 'error', title: 'Perubahan gagal disimpan', text: error.message });
        }
    });
});

document.querySelectorAll('[data-media-delete]').forEach((button) => {
    button.addEventListener('click', async () => {
        const card = button.closest('[data-media-id]');
        if (!window.confirm('Apakah Anda yakin ingin menghapus media ini?')) return;
        try {
            const result = await withGlobalLoading('Menghapus media...', async () => responseMessage(await fetch(card.dataset.apiUrl, { method: 'DELETE', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken } })));
            await showSweetAlert({ icon: 'success', title: 'Media dihapus', text: result.message || 'Media berhasil dihapus.' });
            card.remove();
        } catch (error) {
            await showSweetAlert({ icon: 'error', title: 'Media gagal dihapus', text: error.message });
        }
    });
});

const featuredForm = document.querySelector('[data-featured-form]');

if (featuredForm) {
    const selectedList = featuredForm.querySelector('[data-featured-selected-list]');
    const availableList = featuredForm.querySelector('[data-featured-available-list]');
    const selectedEmpty = featuredForm.querySelector('[data-featured-selected-empty]');
    const availableEmpty = featuredForm.querySelector('[data-featured-empty-available]');
    const countElements = document.querySelectorAll('[data-featured-count]');
    const progress = document.querySelector('[data-featured-progress]');
    let draggedProduct = null;
    let featuredSearchTerm = '';

    const selectedItems = () => [...selectedList.querySelectorAll('[data-featured-selected-item]')];

    const updateFeaturedState = () => {
        const items = selectedItems();
        const selectedIds = new Set(items.map((item) => item.querySelector('[name="product_ids[]"]').value));

        countElements.forEach((element) => {
            element.textContent = String(items.length);
        });
        if (progress) {
            progress.style.width = `${Math.min(100, (items.length / 6) * 100)}%`;
        }
        if (selectedEmpty) {
            selectedEmpty.hidden = items.length > 0;
        }

        items.forEach((item, index) => {
            const rank = item.querySelector('.admin-featured-rank');
            if (rank) rank.textContent = String(index + 1);
            item.querySelector('[data-featured-move="up"]').disabled = index === 0;
            item.querySelector('[data-featured-move="down"]').disabled = index === items.length - 1;
        });

        const availableItems = [...availableList.querySelectorAll('[data-featured-available-item]')];
        let visibleAvailableCount = 0;
        availableItems.forEach((item) => {
            const isSelected = selectedIds.has(item.querySelector('[data-featured-add]').dataset.productId);
            const matchesSearch = item.dataset.searchValue.includes(featuredSearchTerm);
            item.hidden = isSelected || !matchesSearch;
            const addButton = item.querySelector('[data-featured-add]');
            addButton.disabled = !isSelected && items.length >= 6;
            if (!isSelected && matchesSearch) visibleAvailableCount += 1;
        });
        if (availableEmpty) {
            availableEmpty.textContent = featuredSearchTerm && visibleAvailableCount === 0
                ? 'Tidak ada produk yang cocok dengan pencarian.'
                : 'Semua produk kategori sudah dipilih.';
            availableEmpty.hidden = visibleAvailableCount > 0;
        }

        selectedList.querySelectorAll('[data-featured-slot]').forEach((slot) => slot.remove());
        for (let index = items.length + 1; index <= 6; index += 1) {
            const slot = document.createElement('li');
            slot.className = 'admin-featured-slot';
            slot.dataset.featuredSlot = '';
            const number = document.createElement('span');
            number.textContent = String(index);
            const icon = document.createElement('span');
            icon.className = 'material-symbols-rounded';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = 'add_circle';
            const label = document.createElement('span');
            label.textContent = `Slot kosong #${index} — pilih produk dari kolom kiri`;
            const status = document.createElement('small');
            status.textContent = 'Tersedia';
            slot.append(number, icon, label, status);
            selectedList.append(slot);
        }
    };

    const createFeaturedItem = (button) => {
        const item = document.createElement('li');
        item.className = 'admin-featured-selected-item';
        item.dataset.featuredSelectedItem = '';
        item.draggable = true;

        const handle = document.createElement('span');
        handle.className = 'admin-featured-drag material-symbols-rounded';
        handle.setAttribute('aria-label', 'Geser untuk mengurutkan');
        handle.title = 'Geser untuk mengurutkan';
        handle.setAttribute('aria-hidden', 'true');
        handle.textContent = 'drag_indicator';

        const rank = document.createElement('span');
        rank.className = 'admin-featured-rank';

        const imageUrl = button.dataset.productImage;
        if (imageUrl) {
            const image = document.createElement('img');
            image.src = imageUrl;
            image.alt = button.dataset.productName || '';
            image.loading = 'lazy';
            item.append(handle, rank, image);
        } else {
            const placeholder = document.createElement('span');
            placeholder.className = 'admin-featured-image-placeholder';
            const imageIcon = document.createElement('span');
            imageIcon.className = 'material-symbols-rounded';
            imageIcon.setAttribute('aria-hidden', 'true');
            imageIcon.textContent = 'image';
            placeholder.append(imageIcon);
            item.append(handle, rank, placeholder);
        }

        const details = document.createElement('div');
        const name = document.createElement('strong');
        name.textContent = button.dataset.productName || 'Produk tanpa nama';
        details.append(name);

        const productInput = document.createElement('input');
        productInput.type = 'hidden';
        productInput.name = 'product_ids[]';
        productInput.value = button.dataset.productId;

        const actions = document.createElement('div');
        actions.className = 'admin-featured-item-actions';
        [['up', 'keyboard_arrow_up', 'Naikkan urutan'], ['down', 'keyboard_arrow_down', 'Turunkan urutan']].forEach(([direction, iconName, label]) => {
            const action = document.createElement('button');
            action.type = 'button';
            action.dataset.featuredMove = direction;
            action.setAttribute('aria-label', label);
            const icon = document.createElement('span');
            icon.className = 'material-symbols-rounded';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = iconName;
            action.append(icon);
            actions.append(action);
        });
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.dataset.featuredRemove = '';
        remove.setAttribute('aria-label', 'Hapus dari unggulan');
        const deleteIcon = document.createElement('span');
        deleteIcon.className = 'material-symbols-rounded';
        deleteIcon.setAttribute('aria-hidden', 'true');
        deleteIcon.textContent = 'delete';
        remove.append(deleteIcon);
        actions.append(remove);

        item.append(details, productInput, actions);
        return item;
    };

    featuredForm.addEventListener('click', (event) => {
        const addButton = event.target.closest('[data-featured-add]');
        if (addButton && !addButton.disabled) {
            selectedList.append(createFeaturedItem(addButton));
            updateFeaturedState();
            return;
        }

        const removeButton = event.target.closest('[data-featured-remove]');
        if (removeButton) {
            removeButton.closest('[data-featured-selected-item]')?.remove();
            updateFeaturedState();
            return;
        }

        const moveButton = event.target.closest('[data-featured-move]');
        if (moveButton) {
            const item = moveButton.closest('[data-featured-selected-item]');
            const direction = moveButton.dataset.featuredMove;
            const items = selectedItems();
            const index = items.indexOf(item);
            const sibling = direction === 'up' ? items[index - 1] : items[index + 1];
            if (sibling) {
                selectedList.insertBefore(item, direction === 'up' ? sibling : sibling.nextSibling);
                updateFeaturedState();
            }
        }
    });

    selectedList.addEventListener('dragstart', (event) => {
        draggedProduct = event.target.closest('[data-featured-selected-item]');
        if (draggedProduct) event.dataTransfer.effectAllowed = 'move';
    });
    selectedList.addEventListener('dragover', (event) => {
        const target = event.target.closest('[data-featured-selected-item]');
        if (!draggedProduct || !target || draggedProduct === target) return;
        event.preventDefault();
        const bounds = target.getBoundingClientRect();
        selectedList.insertBefore(draggedProduct, event.clientY < bounds.top + bounds.height / 2 ? target : target.nextSibling);
    });
    selectedList.addEventListener('drop', (event) => {
        if (!draggedProduct) return;
        event.preventDefault();
        draggedProduct = null;
        updateFeaturedState();
    });
    selectedList.addEventListener('dragend', () => {
        draggedProduct = null;
        updateFeaturedState();
    });

    featuredForm.querySelector('[data-featured-search]')?.addEventListener('input', (event) => {
        featuredSearchTerm = event.currentTarget.value.trim().toLocaleLowerCase();
        updateFeaturedState();
    });

    document.querySelector('[data-featured-reset]')?.addEventListener('click', () => {
        if (!window.confirm('Hapus semua pilihan unggulan? Landing page akan memakai enam produk Published pertama sesuai urutan katalog.')) return;
        selectedItems().forEach((item) => item.remove());
        updateFeaturedState();
    });

    featuredForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const errors = featuredForm.querySelector('[data-featured-errors]');
        errors.hidden = true;
        const productIds = selectedItems().map((item) => Number(item.querySelector('[name="product_ids[]"]').value));
        try {
            const result = await withGlobalLoading('Menyimpan produk unggulan...', async () => responseMessage(await fetch(featuredForm.dataset.apiUrl, {
                method: 'PUT', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ product_ids: productIds }),
            })));
            await showSweetAlert({ icon: 'success', title: 'Produk unggulan tersimpan', text: result.message || 'Susunan produk unggulan sudah diperbarui.' });
            window.location.reload();
        } catch (error) {
            showError(errors, error.message);
        }
    });

    updateFeaturedState();
}

document.querySelectorAll('[data-preview-url]').forEach((button) => {
    button.addEventListener('click', () => window.open(button.dataset.previewUrl, '_blank', 'noopener'));
});

document.querySelectorAll('[data-contact-preview]').forEach((input) => {
    input.addEventListener('input', () => {
        const preview = document.querySelector(`[data-contact-value="${input.dataset.contactPreview}"]`);
        if (preview) preview.textContent = input.value.trim() || '—';
    });
});

const flashSuccess = document.querySelector('[data-admin-flash-success]')?.dataset.adminFlashSuccess;
if (flashSuccess) {
    showSweetAlert({ icon: 'success', title: 'Berhasil', text: flashSuccess });
}

document.addEventListener('submit', (event) => {
    if (event.defaultPrevented || event.target.matches('[data-admin-login-form], [data-admin-password-form], [data-admin-form], [data-media-upload], [data-featured-form]')) return;
    showGlobalLoading('Memproses permintaan...');
});

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank' || link.hasAttribute('download')) return;

    const destination = new URL(link.href, window.location.href);
    if (destination.origin === window.location.origin && destination.pathname.startsWith('/admin') && (destination.pathname !== window.location.pathname || destination.search !== window.location.search)) {
        showGlobalLoading('Memuat halaman CMS...');
    }
});

window.addEventListener('pageshow', hideGlobalLoading);

document.querySelector('[data-media-upload-trigger]')?.addEventListener('click', () => {
    const uploadPanel = document.querySelector('[data-media-upload-panel]');
    if (uploadPanel) {
        uploadPanel.hidden = false;
        uploadPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});

document.querySelector('[data-media-upload-close]')?.addEventListener('click', () => {
    const uploadPanel = document.querySelector('[data-media-upload-panel]');
    if (uploadPanel) uploadPanel.hidden = true;
});

document.querySelector('[data-seo-page-select]')?.addEventListener('change', (event) => {
    const url = new URL(window.location.href);
    url.searchParams.set('page', event.target.value);
    window.location.assign(url);
});

document.querySelectorAll('[data-count-for]').forEach((counter) => {
    const field = document.querySelector(`[name="${counter.dataset.countFor}"]`);
    if (!field) return;
    const updateCounter = () => {
        counter.textContent = `${field.value.length}/${field.maxLength} karakter`;
        const preview = document.querySelector(`[data-seo-preview-${counter.dataset.countFor === 'meta_title' ? 'title' : 'description'}]`);
        if (preview) preview.textContent = field.value.trim() || (counter.dataset.countFor === 'meta_title' ? 'Judul halaman | ONEIS Healthcare Indonesia' : 'Informasi resmi ONEIS Healthcare Indonesia.');
    };
    field.addEventListener('input', updateCounter);
    updateCounter();
});

document.querySelectorAll('[data-permission-check]').forEach((button) => {
    button.addEventListener('click', () => {
        const form = button.closest('[data-permission-form]');
        form?.querySelectorAll('.permission-checkbox').forEach((checkbox) => {
            checkbox.checked = button.dataset.permissionCheck === 'all';
        });
    });
});

document.querySelectorAll('[data-permission-default]').forEach((button) => {
    button.addEventListener('click', () => {
        const form = button.closest('[data-permission-form]');
        const defaults = JSON.parse(form.dataset.permissionDefaults || '{}');
        form.querySelectorAll('.permission-checkbox').forEach((checkbox) => {
            const module = checkbox.name.match(/^permissions\[([^\]]+)\]/)?.[1];
            checkbox.checked = (defaults[module] || []).includes(checkbox.value);
        });
    });
});
