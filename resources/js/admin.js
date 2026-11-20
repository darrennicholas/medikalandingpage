import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const csrfToken = document.querySelector('input[name="_token"]')?.value || '';

if (document.body.classList.contains('admin-body')) {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-date-picker-target]');
        const input = trigger && document.getElementById(trigger.dataset.datePickerTarget);
        if (!input) return;

        if (typeof input.showPicker === 'function') {
            input.showPicker();
        } else {
            input.focus();
            input.click();
        }
    });

    document.querySelectorAll('.admin-main form[method="get" i]:not(.admin-dashboard-range)').forEach((form, index) => {
        const filterId = form.id || `admin-filter-panel-${index + 1}`;
        form.id = filterId;
        form.dataset.cmsCollapsibleFilter = '';
        form.hidden = true;

        const toggle = document.createElement('button');
        const icon = document.createElement('span');
        const label = document.createElement('span');
        const chevron = document.createElement('span');

        toggle.type = 'button';
        toggle.className = 'admin-filter-toggle';
        toggle.setAttribute('aria-controls', filterId);
        toggle.setAttribute('aria-expanded', 'false');
        icon.className = 'material-symbols-rounded';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = 'tune';
        label.textContent = 'Filter';
        chevron.className = 'material-symbols-rounded admin-filter-toggle__chevron';
        chevron.setAttribute('aria-hidden', 'true');
        chevron.textContent = 'expand_more';
        toggle.append(icon, label, chevron);
        form.before(toggle);

        toggle.addEventListener('click', () => {
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!isExpanded));
            label.textContent = isExpanded ? 'Filter' : 'Sembunyikan Filter';
            chevron.textContent = isExpanded ? 'expand_more' : 'expand_less';
            form.hidden = isExpanded;
        });
    });

    const menu = document.createElement('div');
    menu.className = 'admin-select-menu';
    menu.id = 'admin-select-menu';
    menu.hidden = true;
    menu.setAttribute('role', 'listbox');
    document.body.append(menu);
    let activeSelect = null;
    let activeIndex = -1;

    const closeSelectMenu = () => {
        if (activeSelect) {
            activeSelect.setAttribute('aria-expanded', 'false');
            activeSelect.removeAttribute('aria-controls');
        }
        activeSelect = null;
        menu.hidden = true;
    };
    const highlightOption = (index) => {
        activeIndex = index;
        menu.querySelectorAll('button').forEach((button, buttonIndex) => button.classList.toggle('is-active', buttonIndex === index));
        menu.querySelectorAll('button')[index]?.scrollIntoView({ block: 'nearest' });
    };
    const chooseOption = (index) => {
        const option = activeSelect?.options[index];
        if (!option || option.disabled || option.parentElement?.disabled) return;
        const select = activeSelect;
        const changed = select.selectedIndex !== index;
        select.selectedIndex = index;
        closeSelectMenu();
        select.focus({ preventScroll: true });
        if (changed) {
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };
    const openSelectMenu = (select) => {
        if (select.disabled || select.multiple || select.size > 1) return;
        if (activeSelect === select && !menu.hidden) { closeSelectMenu(); return; }
        closeSelectMenu();
        activeSelect = select;
        menu.replaceChildren();
        const style = getComputedStyle(select);
        menu.style.font = style.font;
        menu.style.color = style.color;
        Array.from(select.options).forEach((option, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'admin-select-menu__option';
            button.textContent = option.textContent.trim();
            button.disabled = option.disabled || Boolean(option.parentElement?.disabled);
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', String(index === select.selectedIndex));
            button.addEventListener('pointerdown', (event) => event.preventDefault());
            button.addEventListener('click', () => chooseOption(index));
            menu.append(button);
        });
        const rect = select.getBoundingClientRect();
        const below = window.innerHeight - rect.bottom - 8;
        const above = rect.top - 8;
        const opensAbove = below < 180 && above > below;
        const height = Math.max(70, Math.min(280, opensAbove ? above : below));
        menu.style.left = `${Math.max(4, rect.left)}px`;
        menu.style.width = `${Math.min(rect.width, window.innerWidth - 8)}px`;
        menu.style.maxHeight = `${height}px`;
        menu.style.top = opensAbove ? 'auto' : `${rect.bottom + 4}px`;
        menu.style.bottom = opensAbove ? `${window.innerHeight - rect.top + 4}px` : 'auto';
        menu.hidden = false;
        select.setAttribute('aria-expanded', 'true');
        select.setAttribute('aria-controls', menu.id);
        highlightOption(Math.max(0, select.selectedIndex));
    };
    document.addEventListener('pointerdown', (event) => {
        const select = event.target.closest('.admin-body select:not([multiple])');
        if (!select || select.disabled || select.size > 1) return;
        event.preventDefault();
        select.focus({ preventScroll: true });
        openSelectMenu(select);
    });
    document.addEventListener('click', (event) => {
        if (activeSelect && event.target !== activeSelect && !menu.contains(event.target)) closeSelectMenu();
    });
    document.addEventListener('keydown', (event) => {
        const select = event.target.closest('.admin-body select:not([multiple])');
        if (!select || select.disabled || select.size > 1) return;
        if (activeSelect !== select) {
            if (['ArrowDown', 'ArrowUp', ' ', 'Enter'].includes(event.key)) { event.preventDefault(); openSelectMenu(select); }
            return;
        }
        if (event.key === 'Escape' || event.key === 'Tab') { closeSelectMenu(); if (event.key === 'Escape') event.preventDefault(); return; }
        if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); chooseOption(activeIndex); return; }
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            const buttons = Array.from(menu.querySelectorAll('button'));
            const direction = event.key === 'ArrowDown' ? 1 : -1;
            let next = activeIndex;
            do { next += direction; } while (next >= 0 && next < buttons.length && buttons[next].disabled);
            if (next >= 0 && next < buttons.length) highlightOption(next);
        }
    });
    window.addEventListener('resize', closeSelectMenu);
    document.addEventListener('scroll', (event) => { if (!menu.contains(event.target)) closeSelectMenu(); }, true);
}

const responseMessage = async (response) => {
    const result = await response.json().catch(() => ({}));
    if (!response.ok || result.success === false) {
        const validationMessages = Object.values(result.errors || {}).flat().join('\n');
        const error = new Error(validationMessages || result.message || 'Permintaan tidak dapat diproses.');
        error.usages = result.data?.usages;
        throw error;
    }

    return result;
};

const showError = (element, message) => {
    hideGlobalLoading();
    if (element) {
        element.textContent = message;
        element.hidden = false;
    }
    showSweetAlert({ icon: 'error', title: 'Permintaan gagal', text: message });
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

const closeSweetAlert = (confirmed = false) => {
    if (sweetAlert) sweetAlert.hidden = true;
    sweetAlertResolver?.(confirmed);
    sweetAlertResolver = null;
};

const showSweetAlert = ({ icon = 'success', title = 'Berhasil', text = '', showCancel = false, confirmText = 'Oke', cancelText = 'Batal' } = {}) => new Promise((resolve) => {
    hideGlobalLoading();
    if (!sweetAlert) {
        if (showCancel) {
            resolve(false);
            return;
        }
        window.alert(text || title);
        resolve(true);
        return;
    }

    const titleElement = sweetAlert.querySelector('[data-sweet-alert-title]');
    const messageElement = sweetAlert.querySelector('[data-sweet-alert-message]');
    const confirmButton = sweetAlert.querySelector('[data-sweet-alert-confirm]');
    const cancelButton = sweetAlert.querySelector('[data-sweet-alert-cancel]');
    sweetAlertResolver = resolve;
    sweetAlert.dataset.state = icon;
    if (titleElement) titleElement.textContent = title;
    if (messageElement) messageElement.textContent = text;
    if (confirmButton) confirmButton.textContent = confirmText;
    if (cancelButton) {
        cancelButton.textContent = cancelText;
        cancelButton.hidden = !showCancel;
    }
    sweetAlert.hidden = false;
    (showCancel ? cancelButton : confirmButton)?.focus();
});

document.querySelectorAll('[data-sweet-alert-confirm]').forEach((button) => button.addEventListener('click', () => closeSweetAlert(true)));
document.querySelectorAll('[data-sweet-alert-cancel]').forEach((button) => button.addEventListener('click', () => closeSweetAlert(false)));

document.addEventListener('change', (event) => {
    const mediaSelect = event.target.closest('[data-media-preview-select]');
    if (mediaSelect) {
        const preview = mediaSelect.closest('.admin-field')?.querySelector('[data-media-preview]');
        const option = mediaSelect.selectedOptions[0];
        if (!preview) return;

        preview.replaceChildren();
        if (option?.dataset.imageUrl) {
            if (option.dataset.mediaType?.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = option.dataset.imageUrl;
                video.controls = true;
                video.muted = true;
                video.playsInline = true;
                video.preload = 'metadata';
                video.setAttribute('aria-label', `Pratinjau ${option.textContent.trim()}`);
                video.load();
                const name = document.createElement('span');
                name.textContent = option.textContent.trim();
                preview.append(video, name);
                return;
            }
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
        const submitLogin = (token) => {
            const formData = new FormData(form);
            formData.set('_token', token);

            return fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                credentials: 'same-origin',
                body: formData,
            });
        };

        let response = await submitLogin(csrfToken);
        if (response.status === 419 || response.redirected) {
            const refreshResponse = await fetch(form.dataset.loginRefreshUrl || '/admin/login', {
                method: 'GET',
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!refreshResponse.ok) {
                throw new Error('Sesi login kedaluwarsa dan token baru tidak dapat dimuat. Muat ulang halaman, lalu coba lagi.');
            }

            const loginPage = new DOMParser().parseFromString(await refreshResponse.text(), 'text/html');
            const freshToken = loginPage.querySelector('input[name="_token"]')?.value;
            if (!freshToken) {
                throw new Error('Token sesi login tidak ditemukan. Muat ulang halaman, lalu coba lagi.');
            }

            const tokenField = form.querySelector('input[name="_token"]');
            if (tokenField) tokenField.value = freshToken;
            response = await submitLogin(freshToken);
        }

        if (response.redirected) {
            throw new Error('Login dialihkan ke halaman lain. Periksa sesi, lalu coba lagi.');
        }

        const result = await responseMessage(response);
        if (result.success !== true) {
            throw new Error(result.message || 'Login belum berhasil. Periksa email dan kata sandi.');
        }

        hideGlobalLoading();
        window.location.replace(result.data?.redirect_url || '/admin');
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
        button.setAttribute('aria-label', input.type === 'password' ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        button.setAttribute('aria-pressed', String(input.type === 'text'));
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
    form.addEventListener('submit', async (event) => {
        if (form.dataset.confirmed === 'true') {
            form.dataset.confirmed = 'false';
            return;
        }

        event.preventDefault();
        const confirmed = await showSweetAlert({ icon: 'warning', title: 'Konfirmasi tindakan', text: form.dataset.confirm || 'Apakah Anda yakin ingin menghapus data ini?', showCancel: true, confirmText: 'Ya, lanjutkan' });
        if (confirmed) {
            form.dataset.confirmed = 'true';
            form.requestSubmit();
        }
    });
});

document.querySelectorAll('[data-language-tab]').forEach((tab) => {
    tab.addEventListener('click', () => {
        const scopedGroup = tab.closest('[data-language-scope]');
        const scope = scopedGroup || tab.closest('form') || document;
        const belongsToGroup = (element) => element.closest('[data-language-scope]') === scopedGroup;

        scope.querySelectorAll('[data-language-tab]').forEach((item) => {
            if (belongsToGroup(item)) item.classList.toggle('is-active', item === tab);
        });
        scope.querySelectorAll('[data-language-panel]').forEach((panel) => {
            if (belongsToGroup(panel)) panel.classList.toggle('is-hidden', panel.dataset.languagePanel !== tab.dataset.languageTab);
        });
    });
});

const reindexRepeater = (list) => {
    const [type] = String(list.dataset.repeaterList || '').split('-');
    [...list.children].forEach((row, index) => {
        if (type === 'buttons') {
            const heading = row.querySelector('.admin-editor-repeater__item-heading strong');
            if (heading) heading.textContent = `Tombol ${index + 1}`;
        }
        row.querySelectorAll('[name]').forEach((field) => {
            field.name = field.name.replace(new RegExp(`\\[${type}\\]\\[(?:__index__|\\d+)\\]`, 'g'), `[${type}][${index}]`);
        });
    });
};

const initializeLiveEditor = (form) => {
    const editorType = form.dataset.liveEditor;
    const previewPanel = form.querySelector('.admin-editor-preview');
    const contentBlockKey = form.querySelector('[name="block_key"]')?.value;
    const autoResizeTextareas = [...form.querySelectorAll('textarea[data-auto-resize]')];
    const resizeTextarea = (textarea) => {
        textarea.style.height = 'auto';
        textarea.style.height = `${textarea.scrollHeight}px`;
    };

    autoResizeTextareas.forEach((textarea) => {
        resizeTextarea(textarea);
        textarea.addEventListener('input', () => resizeTextarea(textarea));
    });

    const updatePreview = () => {
        const activePanel = form.querySelector('[data-language-panel]:not(.is-hidden)') || form.querySelector('[data-language-panel="id"]');
        const language = activePanel?.dataset.languagePanel || 'id';
        const valueFor = (name) => {
            const value = activePanel?.querySelector(`[data-preview-field="${name}"]`)?.value?.trim() || '';
            if (contentBlockKey === 'about_company' && name === 'body') {
                return value
                    .replace(/<\/p\s*>\s*<p\b[^>]*>/gi, '\n\n')
                    .replace(/<br\s*\/?>/gi, '\n')
                    .replace(/<[^>]*>/g, '')
                    .trim();
            }

            return value;
        };
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
        if (editorType === 'hero') {
            const button = form.querySelector('[data-preview-output="cta_label"]');
            if (button) {
                const label = valueFor('cta_label');
                const url = form.querySelector('[name="cta_url"]')?.value.trim();
                button.textContent = label;
                button.hidden = !label || !url;
            }
        } else {
            setText('cta_label', 'Hubungi Kami');
        }

        const mediaSelect = form.querySelector('[data-hero-media], [data-block-media]');
        const mediaOption = mediaSelect?.selectedOptions?.[0];
        const imageUrl = mediaOption?.dataset.imageUrl || '';
        const heroPreview = form.querySelector('[data-hero-preview]');
        if (heroPreview) {
            const video = heroPreview.querySelector('.admin-hero-preview__video');
            if (mediaOption?.dataset.mediaType?.startsWith('video/') && imageUrl) {
                heroPreview.style.removeProperty('--hero-preview-image');
                const previewVideo = video || document.createElement('video');
                previewVideo.className = 'admin-hero-preview__video';
                previewVideo.src = imageUrl;
                previewVideo.autoplay = true;
                previewVideo.muted = true;
                previewVideo.loop = true;
                previewVideo.playsInline = true;
                previewVideo.preload = 'metadata';
                previewVideo.setAttribute('aria-hidden', 'true');
                if (!video) heroPreview.prepend(previewVideo);
                previewVideo.load();
                previewVideo.play().catch(() => {});
            } else {
                video?.remove();
                if (imageUrl) heroPreview.style.setProperty('--hero-preview-image', `url("${imageUrl}")`);
                else heroPreview.style.removeProperty('--hero-preview-image');
            }
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
    for (const language of ['id', 'en', 'zh']) {
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
        for (const language of ['id', 'en', 'zh']) {
            const firstLabel = form.querySelector(`[data-repeater-list="buttons-${language}"] [data-button-label]`)?.value.trim() || '';
            if (form.querySelector(`[data-legacy-cta-label="${language}"]`)) formData.set(`translations[${language}][cta_label]`, firstLabel);
        }
        const firstUrl = form.querySelector('[data-repeater-list="buttons-id"] [data-button-url]')?.value.trim() || '';
        if (form.querySelector('[data-legacy-cta-url]')) formData.set('cta_url', firstUrl);
    }
};

const submitContentForm = async (form, event) => {
    if (event.submitter?.hasAttribute('data-preview-unsaved')) return;
    event.preventDefault();
    const errors = form.querySelector('[data-form-errors]');
    errors.hidden = true;
    const submitButton = event.submitter;
    if (submitButton?.dataset.saveStatus === 'draft' && submitButton.formNoValidate) {
        const requiredFields = [...form.querySelectorAll('[required]')];
        requiredFields.forEach((field) => { field.required = false; });
        const valid = form.reportValidity();
        requiredFields.forEach((field) => { field.required = true; });
        if (!valid) return;
    }
    if (submitButton?.dataset.saveStatus !== 'draft') {
        const emptyRichField = [...form.querySelectorAll('textarea[data-rich-required]')]
            .find((field) => field.value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim() === '');
        if (emptyRichField) {
            const richHost = emptyRichField.closest('[data-rich-editor]');
            richHost?.classList.add('is-invalid');
            errors.textContent = 'Lengkapi kolom bertanda * yang masih kosong sebelum mempublikasikan.';
            errors.hidden = false;
            richHost?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            richHost?.querySelector('.ql-editor')?.focus();
            return;
        }
    }
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

    let endpoint = form.dataset.apiUrl;
    let method = isEditing ? 'PUT' : 'POST';
    if (['company-summary', 'contact-info'].includes(resource)) method = 'PUT';
    if (resource === 'content-blocks') {
        const blockKey = formData.get('block_key');
        endpoint = `/api/v1/admin/content-blocks/${encodeURIComponent(blockKey)}`;
        method = 'PUT';
    }
    if (resource === 'seo-metas') {
        const canonicalField = form.querySelector('[name="canonical_url"][data-canonical-default]');
        if (canonicalField && canonicalField.value.trim() === canonicalField.dataset.canonicalDefault) {
            formData.set('canonical_url', '');
        }
        const pageKey = formData.get('page_key');
        const language = formData.get('lang') || 'id';
        endpoint = `/api/v1/admin/seo-metas/${encodeURIComponent(pageKey)}?lang=${encodeURIComponent(language)}`;
        method = 'PUT';
    }

    formData.set('_method', method);
    try {
        const hasPendingUploads = Boolean(form.querySelector('select[data-pending-upload="1"]'));
        const result = await withGlobalLoading(hasPendingUploads ? 'Menyimpan media dan perubahan...' : 'Menyimpan perubahan...', async () => {
            await savePendingMediaSelects(form, formData);
            if (resource === 'documentations') serializeDocumentationGallery(formData, form);

            return responseMessage(await fetch(endpoint, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
                body: formData,
            }));
        });
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
            const enteredTitle = entry.querySelector('[data-certificate-title]')?.value.trim() || '';
            const title = enteredTitle || `Sertifikat #${index + 1}`;
            const tab = document.createElement('button');
            tab.type = 'button';
            tab.dataset.certificateEntryTab = String(index);
            tab.className = `admin-certificate-batch-tab${index === Number(form.dataset.activeIndex || 0) ? ' is-active' : ''}`;
            tab.textContent = title;
            tabs.append(tab);
            entry.querySelector('[data-certificate-entry-number]').textContent = String(index + 1);
            entry.querySelector('[data-certificate-entry-label]').textContent = `#${index + 1}`;
            entry.querySelector('[data-certificate-entry-toggle] .material-symbols-rounded').textContent = entry.classList.contains('is-collapsed') ? 'expand_more' : 'expand_less';
            entry.querySelector('[data-remove-certificate-entry]').hidden = entries.length === 1;
            const order = entry.querySelector('[data-certificate-field="display_order"]');
            if (order && !order.value) order.placeholder = String(index + 1);

            const titleOutput = entry.querySelector('[data-certificate-entry-title]');
            if (titleOutput) titleOutput.textContent = enteredTitle || 'Belum diberi judul';
            const mediaSelect = entry.querySelector('[data-certificate-field="media_id"]');
            const ready = Boolean(enteredTitle && (mediaSelect?.value || mediaSelect?._pendingUploadFile));
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
    const addEntry = (focusNew = true) => {
        if (!template || !list) return;
        list.append(template.content.cloneNode(true));
        form.dataset.activeIndex = String(list.children.length - 1);
        refreshCertificateBatch(form);
        if (focusNew) list.lastElementChild?.querySelector('[data-certificate-title]')?.focus();
    };

    addEntry(false);
    form.parentElement.querySelector('[data-add-certificate-entry]')?.addEventListener('click', () => addEntry());
    form.addEventListener('input', () => refreshCertificateBatch(form));
    form.addEventListener('change', () => refreshCertificateBatch(form));
    form.addEventListener('focusin', (event) => {
        const entry = event.target.closest('[data-certificate-entry]');
        if (!entry) return;
        const index = [...form.querySelectorAll('[data-certificate-entry]')].indexOf(entry);
        if (index >= 0 && index !== Number(form.dataset.activeIndex || 0)) {
            form.dataset.activeIndex = String(index);
            refreshCertificateBatch(form);
        }
    });
    form.parentElement.addEventListener('click', (event) => {
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

        const previewButton = event.target.closest('[data-certificate-preview]');
        if (previewButton) {
            const entries = [...form.querySelectorAll('[data-certificate-entry]')];
            const entry = entries[Number(form.dataset.activeIndex || 0)];
            if (!entry) return;
            const previewForm = document.createElement('form');
            previewForm.method = 'POST';
            previewForm.action = form.dataset.certificatePreviewUrl;
            previewForm.target = '_blank';
            previewForm.hidden = true;
            const addField = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                previewForm.append(input);
            };
            addField('_token', csrfToken);
            addField('preview_lang', previewButton.dataset.certificatePreview);
            entry.querySelectorAll('[data-certificate-field]').forEach((field) => addField(field.dataset.certificateField, field.value));
            document.body.append(previewForm);
            previewForm.submit();
            previewForm.remove();
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
            const missingEntry = entries.find((entry) => {
                const mediaSelect = entry.querySelector('[data-certificate-field="media_id"]');
                return !entry.querySelector('[data-certificate-title]')?.value.trim() || (!mediaSelect?.value && !mediaSelect?._pendingUploadFile);
            });
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
                await savePendingMediaSelects(form, new FormData());
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

document.querySelectorAll('.admin-cms-form[data-resource="products"]').forEach((form) => {
    const status = form.querySelector('[data-product-new-status]');
    const expiryField = form.querySelector('[data-product-new-until-field]');
    const expiryDate = form.querySelector('[data-product-new-until]');
    if (!status || !expiryField || !expiryDate) return;

    const updateExpiryField = () => {
        const isNew = status.value === 'new';
        expiryField.hidden = !isNew;
        expiryDate.required = isNew;
        if (!isNew) expiryDate.value = '';
    };

    status.addEventListener('change', updateExpiryField);
    updateExpiryField();
});

const appendMediaOption = (select, media) => {
    clearPendingMedia(select);
    const option = document.createElement('option');
    option.value = String(media.id);
    option.textContent = `${media.original_name} · ${media.mime_type.replace('image/', '').replace('video/', '').toUpperCase()} · ${Math.round(media.size_bytes / 1024)} KB`;
    option.dataset.imageUrl = media.admin_preview_url;
    option.dataset.mediaType = media.mime_type;
    select.add(option);
    select.value = String(media.id);
    select.dispatchEvent(new Event('change', { bubbles: true }));
};

const clearPendingMedia = (select) => {
    if (!select) return;
    const pendingOption = [...select.options].find((option) => option.value === select._pendingOptionValue);
    const selectedPendingOption = select.value === select._pendingOptionValue;
    pendingOption?.remove();
    if (selectedPendingOption) select.value = '';
    if (select._pendingPreviewUrl) URL.revokeObjectURL(select._pendingPreviewUrl);
    delete select._pendingUploadFile;
    delete select._pendingPreviewUrl;
    delete select._pendingOptionValue;
    delete select._pendingUploadUrl;
    delete select.dataset.pendingUpload;
};

const stageMediaFile = (file, select, uploadUrl, allowVideo = false) => {
    const extensionMime = /\.mp4$/i.test(file.name) ? 'video/mp4' : (/\.webm$/i.test(file.name) ? 'video/webm' : '');
    const mediaType = file.type || extensionMime;
    const isVideo = mediaType.startsWith('video/');
    const allowedTypes = allowVideo ? ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/webm'] : ['image/jpeg', 'image/png', 'image/webp'];
    if (mediaType && !allowedTypes.includes(mediaType)) {
        throw new Error(allowVideo ? 'Pilih JPG, PNG, WebP, MP4, atau WebM.' : 'Pilih gambar JPG, PNG, atau WebP.');
    }
    if (file.size > (isVideo ? 50 : 2) * 1024 * 1024) {
        throw new Error(isVideo ? 'Ukuran video maksimal 50 MB.' : 'Ukuran gambar maksimal 2 MB.');
    }

    clearPendingMedia(select);
    const previewUrl = URL.createObjectURL(file);
    const optionValue = `pending-media-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
    const option = document.createElement('option');
    option.value = optionValue;
    option.textContent = file.name;
    option.dataset.imageUrl = previewUrl;
    option.dataset.mediaType = mediaType;
    option.dataset.localPending = '1';
    select.add(option);
    select.value = optionValue;
    select._pendingUploadFile = file;
    select._pendingPreviewUrl = previewUrl;
    select._pendingOptionValue = optionValue;
    select._pendingUploadUrl = uploadUrl;
    select.dataset.pendingUpload = '1';
    select.dispatchEvent(new Event('change', { bubbles: true }));
};

const uploadMediaFile = async (file, uploadUrl) => {
    const formData = new FormData();
    formData.append('files[]', file);
    const result = await responseMessage(await fetch(uploadUrl, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: formData,
    }));
    const media = result.data?.[0];
    if (!media?.id || !media.admin_preview_url) throw new Error('File tersimpan, tetapi pratinjaunya belum tersedia. Coba muat ulang CMS.');

    return media;
};

const savePendingMediaSelects = async (form, formData) => {
    for (const select of form.querySelectorAll('select[data-pending-upload="1"]')) {
        const media = await uploadMediaFile(select._pendingUploadFile, select._pendingUploadUrl);
        appendMediaOption(select, media);
        if (select.name) formData.set(select.name, select.value);
    }
};

const serializeDocumentationGallery = (formData, form) => {
    for (const key of new Set([...formData.keys()])) {
        if (key === 'gallery_media_ids[]' || key.startsWith('gallery_captions[') || key.startsWith('gallery[')) formData.delete(key);
    }
    formData.set('gallery_present', '1');
    const selectedMedia = [...form.querySelectorAll('[name="gallery_media_ids[]"]:checked')];
    selectedMedia.forEach((input, index) => {
        const galleryItem = input.closest('[data-gallery-item]');
        formData.append(`gallery[${index}][media_id]`, input.value);
        formData.append(`gallery[${index}][caption_id]`, galleryItem?.querySelector('[data-gallery-caption-id]')?.value || '');
        formData.append(`gallery[${index}][caption_en]`, galleryItem?.querySelector('[data-gallery-caption-en]')?.value || '');
        formData.append(`gallery[${index}][caption_zh]`, galleryItem?.querySelector('[data-gallery-caption-zh]')?.value || '');
    });
};

const initializeMediaSelectUpload = (select) => {
    if (!select.dataset.pendingClearBound) {
        select.dataset.pendingClearBound = '1';
        select.addEventListener('change', () => {
            if (select._pendingUploadFile && select.value !== select._pendingOptionValue) clearPendingMedia(select);
        });
    }
    if (select.closest('.admin-cms-form[data-resource="products"]') && select.name !== 'media_id') return;
    if (select.dataset.inlineUploadReady || select.matches('[data-hero-media]')) return;
    const field = select.closest('.admin-field');
    if (!field) return;
    select.dataset.inlineUploadReady = '1';

    const uploadControl = document.createElement('div');
    uploadControl.className = 'admin-media-inline-upload';
    const uploadCopy = document.createElement('div');
    uploadCopy.className = 'admin-media-inline-upload__copy';
    uploadCopy.innerHTML = '<strong>Upload gambar dari perangkat</strong><small>Pratinjau muncul langsung. Gambar disimpan ke Media Library saat konten disimpan.</small>';
    const button = document.createElement('button');
    button.className = 'admin-button admin-button--soft';
    button.type = 'button';
    button.innerHTML = '<span class="material-symbols-rounded" aria-hidden="true">add_photo_alternate</span>Pilih gambar';
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/jpeg,image/png,image/webp';
    input.hidden = true;
    input.setAttribute('aria-label', 'Pilih gambar baru untuk diunggah');
    const errors = document.createElement('div');
    errors.className = 'admin-form__error';
    errors.hidden = true;
    button.addEventListener('click', () => input.click());
    input.addEventListener('change', async () => {
        const file = input.files?.[0];
        if (!file) return;
        try {
            stageMediaFile(file, select, '/api/v1/admin/media');
            input.value = '';
        } catch (error) {
            showError(errors, error.message);
            input.value = '';
        }
    });
    uploadControl.append(uploadCopy, button, input, errors);
    field.insertAdjacentElement('afterend', uploadControl);
};

const findMediaSelects = (root) => {
    if (root.matches?.('select[data-media-preview-select], select[data-block-media], select[name="og_media_id"]')) initializeMediaSelectUpload(root);
    root.querySelectorAll?.('select[data-media-preview-select], select[data-block-media], select[name="og_media_id"]').forEach(initializeMediaSelectUpload);
};

findMediaSelects(document);
new MutationObserver((mutations) => {
    mutations.forEach((mutation) => mutation.addedNodes.forEach((node) => {
        if (node instanceof Element) findMediaSelects(node);
    }));
}).observe(document.body, { childList: true, subtree: true });

document.querySelectorAll('[data-gallery-picker]').forEach((picker) => {
    const dialog = picker.querySelector('[data-gallery-caption-dialog]');
    if (!dialog) return;

    const languages = ['id', 'en', 'zh'];
    const dialogInputs = languages.map((language) => dialog.querySelector(`[data-gallery-dialog-caption="${language}"]`));
    picker.addEventListener('click', (event) => {
        const button = event.target.closest('[data-gallery-caption-open]');
        const item = button?.closest('[data-gallery-item]');
        if (!item) return;

        dialog._galleryItem = item;
        languages.forEach((language, index) => {
            dialogInputs[index].value = item.querySelector(`[data-gallery-caption-${language}]`)?.value || '';
        });
        dialog.showModal();
        dialogInputs[0]?.focus();
    });

    dialog.querySelector('[data-gallery-caption-cancel]')?.addEventListener('click', () => dialog.close());
    dialog.querySelector('[data-gallery-caption-save]')?.addEventListener('click', () => {
        const item = dialog._galleryItem;
        if (!item) return dialog.close();

        languages.forEach((language, index) => {
            const input = item.querySelector(`[data-gallery-caption-${language}]`);
            if (input) input.value = dialogInputs[index].value.trim();
        });
        const button = item.querySelector('[data-gallery-caption-open]');
        if (button) button.textContent = dialogInputs.some((input) => input.value.trim()) ? 'Ubah keterangan' : 'Tambah keterangan';
        dialog.close();
    });
});

document.querySelectorAll('[data-hero-upload]').forEach((input) => {
    input.closest('[data-hero-upload-container]')?.querySelector('[data-hero-upload-trigger]')?.addEventListener('click', () => input.click());
    input.addEventListener('change', async () => {
        const file = input.files?.[0];
        const form = input.closest('form');
        const select = form?.querySelector('[data-hero-media]');
        const errors = input.closest('[data-hero-upload-container]')?.querySelector('[data-hero-upload-errors]');
        if (!file || !select) return;
        if (errors) errors.hidden = true;
        try {
            stageMediaFile(file, select, input.dataset.uploadUrl, true);
            input.value = '';
        } catch (error) {
            showError(errors, error.message);
            input.value = '';
        }
    });
});

document.querySelectorAll('[data-status-url]').forEach((button) => {
    button.addEventListener('click', async () => {
        const nextStatus = button.dataset.nextStatus;
        const statusDescription = {
            published: 'dipublikasikan',
            draft: 'disimpan sebagai draft',
            open: 'dibuka',
            closed: 'ditutup',
            active: 'diaktifkan',
            inactive: 'dinonaktifkan',
        }[nextStatus] || nextStatus;
        const confirmed = await showSweetAlert({
            icon: 'info',
            title: 'Konfirmasi perubahan status',
            text: `Status konten akan diubah menjadi ${statusDescription}. Lanjutkan?`,
            showCancel: true,
            confirmText: 'Ya, ubah status',
            cancelText: 'Batal',
        });
        if (!confirmed) return;

        button.disabled = true;
        try {
            const result = await withGlobalLoading('Memperbarui status...', async () => responseMessage(await fetch(button.dataset.statusUrl, {
                method: 'PATCH',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ status: nextStatus }),
            })));
            await showSweetAlert({ icon: 'success', title: 'Status diperbarui', text: result.message || 'Status konten berhasil diperbarui.' });
            window.location.reload();
        } catch (error) {
            await showSweetAlert({ icon: 'error', title: 'Status gagal diperbarui', text: error.message });
        } finally {
            button.disabled = false;
        }
    });
});

document.querySelectorAll('[data-delete-url]').forEach((button) => {
    button.addEventListener('click', async () => {
        const label = button.getAttribute('aria-label')?.replace(/^Hapus\s*/i, '').trim();
        const confirmed = await showSweetAlert({ icon: 'warning', title: 'Hapus data?', text: label ? `Data “${label}” akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.` : 'Data ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.', showCancel: true, confirmText: 'Ya, hapus' });
        if (!confirmed) return;
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
            alt_text_zh: card.querySelector('[name="alt_text_zh"]').value,
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
        const confirmed = await showSweetAlert({ icon: 'warning', title: 'Hapus media?', text: 'Media ini akan dihapus permanen. Media yang sedang digunakan mungkin tidak dapat dihapus.', showCancel: true, confirmText: 'Ya, hapus' });
        if (!confirmed) return;
        try {
            const result = await withGlobalLoading('Menghapus media...', async () => responseMessage(await fetch(card.dataset.apiUrl, { method: 'DELETE', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken } })));
            await showSweetAlert({ icon: 'success', title: 'Media dihapus', text: result.message || 'Media berhasil dihapus.' });
            card.remove();
        } catch (error) {
            const usages = Array.isArray(error.usages) ? error.usages : [];
            const message = usages.length ? `${error.message}\n\nDigunakan pada:\n${usages.map((usage) => `• ${usage}`).join('\n')}` : error.message;
            await showSweetAlert({ icon: 'error', title: 'Media gagal dihapus', text: message });
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

    document.querySelector('[data-featured-reset]')?.addEventListener('click', async () => {
        const confirmed = await showSweetAlert({ icon: 'warning', title: 'Hapus semua pilihan?', text: 'Landing page akan memakai enam produk Published pertama sesuai urutan katalog.', showCancel: true, confirmText: 'Ya, hapus pilihan' });
        if (!confirmed) return;
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

document.querySelectorAll('button[data-preview-url]').forEach((button) => {
    button.addEventListener('click', () => window.open(button.dataset.previewUrl, '_blank', 'noopener'));
});

document.querySelectorAll('[data-contact-preview]').forEach((input) => {
    input.addEventListener('input', () => {
        const preview = document.querySelector(`[data-contact-value="${input.dataset.contactPreview}"]`);
        if (preview) preview.textContent = input.value.trim() || '—';
    });
});

const flashError = document.querySelector('[data-admin-flash-error]')?.dataset.adminFlashError;
if (flashError) showSweetAlert({ icon: 'error', title: 'Permintaan gagal', text: flashError });

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
        counter.textContent = counter.dataset.countFor === 'meta_title'
            ? `Sisa ${Math.max(0, field.maxLength - field.value.length)} karakter`
            : `${field.value.length}/${field.maxLength} karakter`;
        const preview = document.querySelector(`[data-seo-preview-${counter.dataset.countFor === 'meta_title' ? 'title' : 'description'}]`);
        if (preview) preview.textContent = field.value.trim() || (counter.dataset.countFor === 'meta_title' ? 'Judul halaman | PT Interskala Medika Indonesia' : 'Informasi resmi PT Interskala Medika Indonesia.');
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

// Generate editable slugs for content forms.
document.querySelectorAll('.admin-cms-form').forEach((form) => {
    for (const language of ['id', 'en', 'zh']) {
        const source = form.querySelector(`[name="translations[${language}][name]"], [name="translations[${language}][title]"]`);
        const slug = form.querySelector(`[name="translations[${language}][slug]"]`);
        if (!source || !slug) continue;
        let edited = Boolean(slug.value);
        slug.addEventListener('input', () => { edited = Boolean(slug.value); });
        source.addEventListener('input', () => { if (!edited) slug.value = source.value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''); });
    }
});
const richLinkDialog = document.querySelector('[data-rich-link-dialog]');
const richLinkForm = richLinkDialog?.querySelector('[data-rich-link-form]');
const richLinkLabel = richLinkForm?.elements.namedItem('label');
const richLinkUrl = richLinkForm?.elements.namedItem('url');
const richLinkError = richLinkDialog?.querySelector('[data-rich-link-error]');
const richLinkRemove = richLinkDialog?.querySelector('[data-rich-link-remove]');
let richLinkContext = null;

richLinkDialog?.querySelector('[data-rich-link-cancel]')?.addEventListener('click', () => richLinkDialog.close());
richLinkDialog?.addEventListener('close', () => { richLinkContext = null; });
richLinkRemove?.addEventListener('click', () => {
    if (!richLinkContext) return;
    const { editor, range } = richLinkContext;
    editor.formatText(range.index, range.length, 'link', false, Quill.sources.USER);
    richLinkDialog.close();
    editor.setSelection(range.index, range.length, Quill.sources.SILENT);
});
richLinkForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!richLinkContext) return;
    const url = richLinkUrl.value.trim();
    const label = richLinkLabel.value.trim();
    if (!label || !/^(https:\/\/[^\s/]+|\/(?!\/))/i.test(url) || /[\s\\]/.test(url)) {
        richLinkError.textContent = 'Isi teks tautan dan gunakan URL HTTPS atau path halaman yang valid.';
        richLinkError.hidden = false;
        return;
    }
    const { editor, range } = richLinkContext;
    if (range.length && editor.getText(range.index, range.length) === label) {
        editor.formatText(range.index, range.length, 'link', url, Quill.sources.USER);
    } else {
        if (range.length) editor.deleteText(range.index, range.length, Quill.sources.USER);
        editor.insertText(range.index, label, 'link', url, Quill.sources.USER);
    }
    richLinkDialog.close();
    editor.setSelection(range.index + label.length, 0, Quill.sources.SILENT);
});

document.querySelectorAll('[data-rich-editor]').forEach((host) => {
    if (host.dataset.richInitialized === 'true') return;

    const field = host.querySelector('textarea');
    if (!field) return;

    const editorElement = document.createElement('div');
    editorElement.className = 'admin-rich-editor__content';
    host.append(editorElement);

    const editor = new Quill(editorElement, {
        theme: 'snow',
        placeholder: host.dataset.placeholder || 'Tulis konten di sini...',
        formats: ['bold', 'italic', 'underline', 'color', 'list', 'link'],
        modules: {
            toolbar: [
                ['bold', 'italic'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
            ],
        },
    });
    const toolbar = editor.getModule('toolbar');
    const linkButton = toolbar.container.querySelector('.ql-link');
    linkButton?.setAttribute('aria-label', 'Tambah atau ubah tautan');
    linkButton?.setAttribute('title', 'Tambah atau ubah tautan');
    const syncLinkButtonState = () => {
        const range = editor.getSelection();
        const format = range ? editor.getFormat(range.index, range.length) : {};
        const active = Boolean(format.link);

        linkButton?.classList.toggle('ql-active', active);
        linkButton?.setAttribute('aria-pressed', String(active));
    };
    toolbar.addHandler('link', () => {
        let range = editor.getSelection(true);
        if (!range) {
            editor.focus();
            range = { index: Math.max(editor.getLength() - 1, 0), length: 0 };
            editor.setSelection(range.index, 0, Quill.sources.SILENT);
        }

        if (!richLinkDialog || !richLinkForm) return;
        const currentLink = editor.getFormat(range.index, range.length).link || '';
        if (currentLink && range.length === 0) {
            const leaf = editor.getLeaf(range.index)[0];
            const anchor = leaf?.domNode?.parentElement?.closest('a');
            const blot = anchor ? Quill.find(anchor) : null;
            if (blot) range = { index: editor.getIndex(blot), length: blot.length() };
        }
        richLinkContext = { editor, range };
        richLinkLabel.value = range.length ? editor.getText(range.index, range.length) : '';
        richLinkUrl.value = currentLink;
        richLinkRemove.hidden = !currentLink;
        richLinkError.hidden = true;
        richLinkDialog.showModal();
        (richLinkLabel.value ? richLinkUrl : richLinkLabel).focus();
    });
    linkButton?.addEventListener('click', syncLinkButtonState);
    editor.on('selection-change', syncLinkButtonState);
    editor.on('text-change', syncLinkButtonState);
    toolbar.addHandler('italic', () => {
        const range = editor.getSelection(true);
        if (!range) return;

        if (range.length > 0) {
            const isItalic = Boolean(editor.getFormat(range.index, range.length).italic);
            editor.formatText(range.index, range.length, 'italic', !isItalic, Quill.sources.USER);
            editor.setSelection(range.index, range.length, Quill.sources.SILENT);

            return;
        }

        const text = editor.getText();
        let start = range.index;
        let end = range.index;
        const isWordCharacter = (character) => Boolean(character && /[\p{L}\p{N}_-]/u.test(character));

        while (start > 0 && isWordCharacter(text[start - 1])) start -= 1;
        while (end < text.length && isWordCharacter(text[end])) end += 1;

        if (end > start) {
            const isItalic = Boolean(editor.getFormat(start, end - start).italic);
            editor.formatText(start, end - start, 'italic', !isItalic, Quill.sources.USER);
            editor.setSelection(range.index, 0, Quill.sources.SILENT);

            return;
        }

        editor.format('italic', !editor.getFormat(range.index).italic, Quill.sources.USER);
    });
    host.dataset.richInitialized = 'true';
    editorElement.setAttribute('aria-label', host.dataset.placeholder || 'Editor teks');

    const parsed = new DOMParser().parseFromString(field.value, 'text/html');
    parsed.querySelectorAll('script,style,iframe,object,embed').forEach((node) => node.remove());
    parsed.querySelectorAll('*').forEach((node) => [...node.attributes].forEach((attribute) => {
        if (attribute.name.startsWith('on') || (attribute.name === 'href' && !/^(https:\/\/|\/(?!\/))/i.test(attribute.value))) node.removeAttribute(attribute.name);
    }));
    editor.clipboard.dangerouslyPasteHTML(parsed.body.innerHTML, 'silent');

    field.hidden = true;
    field.required = false;

    const hasContent = () => {
        if (editor.getText().replace(/\u200B/g, '').trim() !== '') return true;

        return Boolean(editor.root.querySelector('li, a, img, iframe'));
    };

    const sync = () => {
        field.value = hasContent() ? editor.root.innerHTML : '';
        field.dispatchEvent(new Event('input', { bubbles: true }));
    };

    editor.on('text-change', sync);
    field.form?.addEventListener('submit', sync, true);
    field.form?.addEventListener('formdata', sync);
    host.addEventListener('focusin', () => host.classList.remove('is-invalid'));
    sync();
});

const sitemapPreviewModal = document.querySelector('[data-sitemap-modal]');
if (sitemapPreviewModal) {
    const setSitemapPreviewOpen = (open) => {
        sitemapPreviewModal.hidden = !open;
        document.body.classList.toggle('admin-sitemap-modal-open', open);
        if (open) {
            sitemapPreviewModal.querySelector('[data-close-sitemap-modal]')?.focus();
        }
    };
    document.querySelectorAll('[data-open-sitemap-preview]').forEach((button) => {
        button.addEventListener('click', () => setSitemapPreviewOpen(true));
    });
    sitemapPreviewModal.querySelectorAll('[data-close-sitemap-modal]').forEach((button) => {
        button.addEventListener('click', () => setSitemapPreviewOpen(false));
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !sitemapPreviewModal.hidden) {
            setSitemapPreviewOpen(false);
        }
    });
}
