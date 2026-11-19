const siteHeader = document.querySelector('#site-header');
const menuToggle = document.querySelector('#menu-toggle');
const primaryNavigation = document.querySelector('#primary-navigation');

const updateHeader = () => {
    siteHeader?.classList.toggle('is-scrolled', window.scrollY > 40);
};

updateHeader();
window.addEventListener('scroll', updateHeader, { passive: true });

const closeMenu = () => {
    menuToggle?.classList.remove('is-active');
    menuToggle?.setAttribute('aria-expanded', 'false');
    menuToggle?.setAttribute('aria-label', 'Buka menu');
    primaryNavigation?.classList.remove('is-open');
    document.body.classList.remove('menu-open');
};

menuToggle?.addEventListener('click', () => {
    const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
    menuToggle.classList.toggle('is-active', !isOpen);
    menuToggle.setAttribute('aria-expanded', String(!isOpen));
    menuToggle.setAttribute('aria-label', isOpen ? 'Buka menu' : 'Tutup menu');
    primaryNavigation?.classList.toggle('is-open', !isOpen);
    document.body.classList.toggle('menu-open', !isOpen);
});

primaryNavigation?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMenu);
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeMenu();
    }
});

const brandTrack = document.querySelector('#brand-track');
const brandDots = document.querySelector('#brand-dots');

if (brandTrack && brandDots) {
    const originalSlides = Array.from(brandTrack.children);
    const clonedSlideCount = Math.min(6, originalSlides.length);
    const leadingClones = document.createDocumentFragment();
    const trailingClones = document.createDocumentFragment();
    let currentIndex = clonedSlideCount;
    let activeDotIndex = 0;
    let loaderResetTimer;

    originalSlides.slice(-clonedSlideCount).forEach((slide) => {
        const clone = slide.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        leadingClones.appendChild(clone);
    });

    originalSlides.slice(0, clonedSlideCount).forEach((slide) => {
        const clone = slide.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        trailingClones.appendChild(clone);
    });

    brandTrack.prepend(leadingClones);
    brandTrack.appendChild(trailingClones);

    const brandLoader = document.createElement('span');
    brandLoader.className = 'brand-carousel__loader';
    brandLoader.setAttribute('aria-hidden', 'true');
    brandDots.appendChild(brandLoader);

    const positionBrandLoader = (animate, index) => {
        const dotStyles = window.getComputedStyle(brandDots);
        const dotSize = parseFloat(dotStyles.getPropertyValue('--brand-dot-size'));
        const dotGap = parseFloat(dotStyles.getPropertyValue('--brand-dot-gap'));
        const loaderWidth = parseFloat(dotStyles.getPropertyValue('--brand-loader-width'));
        const leftOffset = index * (dotSize + dotGap) - (loaderWidth - dotSize) / 2;

        brandLoader.style.transition = animate ? 'transform 1500ms linear' : 'none';
        brandLoader.style.transform = `translate3d(${leftOffset}px, -50%, 0)`;
    };

    const renderCarousel = (animate = true, loaderIndex = activeDotIndex) => {
        const slideWidth = brandTrack.children[0].getBoundingClientRect().width;
        brandTrack.style.transition = animate ? 'transform 1500ms linear' : 'none';
        brandTrack.style.transform = 'translate3d(-' + currentIndex * slideWidth + 'px, 0, 0)';
        positionBrandLoader(animate, loaderIndex);

        brandDots.querySelectorAll('button').forEach((dot, index) => {
            dot.classList.toggle('is-active', index === activeDotIndex);
            dot.setAttribute('aria-current', index === activeDotIndex ? 'true' : 'false');
        });
    };

    originalSlides.forEach((slide, index) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.setAttribute('aria-label', 'Tampilkan brand ' + (index + 1));
        dot.addEventListener('click', () => {
            window.clearTimeout(loaderResetTimer);
            currentIndex = clonedSlideCount + index;
            activeDotIndex = index;
            renderCarousel();
        });
        brandDots.appendChild(dot);
    });

    const moveCarouselRight = () => {
        if (currentIndex === 0) {
            currentIndex = originalSlides.length;
            renderCarousel(false);
            void brandTrack.offsetWidth;
        }

        currentIndex -= 1;

        if (activeDotIndex === originalSlides.length - 1) {
            activeDotIndex = 0;
            renderCarousel(true, originalSlides.length);
            loaderResetTimer = window.setTimeout(() => {
                positionBrandLoader(false, 0);
            }, 1450);
        } else {
            activeDotIndex += 1;
            renderCarousel();
        }
    };

    renderCarousel(false);
    window.addEventListener('resize', () => renderCarousel(false));

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        window.setInterval(moveCarouselRight, 1500);
    }
}

const certificateCarousel = document.querySelector('[data-certificate-carousel]');

if (certificateCarousel) {
    const certificateTrack = certificateCarousel.querySelector('#certificate-track');
    const certificateDots = certificateCarousel.querySelector('#certificate-dots');
    const certificateControls = certificateCarousel.querySelector('[data-certificate-controls]');
    const certificates = Array.from(certificateTrack?.children || []);
    let currentCertificate = 0;
    let autoplay;

    const isDesktop = () => window.matchMedia('(min-width: 992px)').matches;
    const lastPosition = () => Math.max(0, certificates.length - 3);
    const stopAutoplay = () => window.clearInterval(autoplay);
    const startAutoplay = () => {
        stopAutoplay();
        if (!isDesktop() || certificates.length <= 3 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        autoplay = window.setInterval(() => {
            currentCertificate = currentCertificate >= lastPosition() ? 0 : currentCertificate + 1;
            renderCertificates();
        }, 3500);
    };

    const renderCertificates = () => {
        if (!certificateTrack || !certificateDots) return;

        if (!isDesktop()) {
            currentCertificate = 0;
            certificateTrack.style.transform = '';
            certificates.forEach((certificate) => {
                certificate.inert = false;
                certificate.removeAttribute('aria-hidden');
            });
            certificateDots.replaceChildren();
            if (certificateControls) certificateControls.hidden = true;
            stopAutoplay();
            return;
        }

        currentCertificate = Math.min(currentCertificate, lastPosition());
        const firstCard = certificates[0];
        const cardGap = Number.parseFloat(getComputedStyle(certificateTrack).columnGap) || 0;
        const slideWidth = (firstCard?.getBoundingClientRect().width || 0) + cardGap;
        certificateTrack.style.transform = `translate3d(-${currentCertificate * slideWidth}px, 0, 0)`;
        certificates.forEach((certificate, index) => {
            const isVisible = certificates.length <= 3 || (index >= currentCertificate && index < currentCertificate + 3);
            certificate.inert = !isVisible;
            certificate.setAttribute('aria-hidden', String(!isVisible));
        });
        if (certificateControls) certificateControls.hidden = certificates.length <= 3;
        certificateDots.replaceChildren();

        for (let index = 0; index <= lastPosition(); index += 1) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.classList.toggle('is-active', index === currentCertificate);
            dot.setAttribute('aria-label', `Tampilkan sertifikat mulai dari nomor ${index + 1}`);
            dot.setAttribute('aria-current', index === currentCertificate ? 'true' : 'false');
            dot.addEventListener('click', () => {
                currentCertificate = index;
                renderCertificates();
            });
            certificateDots.appendChild(dot);
        }
    };

    renderCertificates();
    startAutoplay();
    window.addEventListener('resize', () => {
        renderCertificates();
        startAutoplay();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoplay();
        } else {
            startAutoplay();
        }
    });
}

const navigateWithFade = (url) => {
    window.location.assign(url);
};

const navigateToLanguage = (language) => {
    if (!['id', 'en', 'zh'].includes(language)) return;

    const alternate = document.querySelector(`link[rel="alternate"][hreflang="${language}"]`);
    if (alternate?.href) {
        navigateWithFade(alternate.href + window.location.search + window.location.hash);
        return;
    }
    const currentPath = window.location.pathname.replace(/^\/(id|en|zh)(?=\/|$)/, '');
    const localizedPath = '/' + language + currentPath;
    navigateWithFade(localizedPath + window.location.search + window.location.hash);
};

const languageSwitchers = document.querySelectorAll('[data-language-switcher]');

languageSwitchers.forEach((switcher) => {
    switcher.querySelectorAll('[data-language-option]').forEach((option) => {
        option.addEventListener('click', (event) => {
            event.preventDefault();
            navigateToLanguage(option.dataset.languageOption);
        });
    });
});

document.addEventListener('click', (event) => {
    languageSwitchers.forEach((switcher) => {
        if (switcher.open && !switcher.contains(event.target)) {
            switcher.open = false;
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    languageSwitchers.forEach((switcher) => {
        if (!switcher.open) return;

        switcher.open = false;
        switcher.querySelector('.language-switcher__trigger')?.focus({ preventScroll: true });
    });
});

const newsGrid = document.querySelector('#news-grid');

if (newsGrid) {
    const searchInput = document.querySelector('#news-search');
    const yearFilter = document.querySelector('#news-filter');
    const sortOrder = document.querySelector('#news-sort');
    const noResults = document.querySelector('#news-no-results');
    const newsCards = Array.from(newsGrid.querySelectorAll('.news-card'));

    const updateNews = () => {
        const searchTerm = searchInput.value.trim().toLocaleLowerCase('id');
        const selectedYear = yearFilter.value;
        let visibleCount = 0;

        newsCards.sort((first, second) => {
            const order = first.dataset.date.localeCompare(second.dataset.date);
            return sortOrder.value === 'newest' ? -order : order;
        });

        newsCards.forEach((card) => {
            const searchableText = `${card.querySelector('h2').textContent} ${card.querySelector('p').textContent}`;
            const matchesSearch = searchableText.toLocaleLowerCase('id').includes(searchTerm);
            const matchesYear = selectedYear === 'all' || card.dataset.year === selectedYear;
            card.hidden = !(matchesSearch && matchesYear);
            visibleCount += Number(!card.hidden);
            newsGrid.appendChild(card);
        });

        noResults.hidden = visibleCount > 0;
    };

    searchInput.addEventListener('input', updateNews);
    [yearFilter, sortOrder].forEach((select) => {
        select.addEventListener('change', () => {
            updateNews();
            select.closest('details').open = false;
        });
    });
}

const articleForm = document.querySelector('#article-create-form');
const articleStorageKey = 'imi-medika-article-drafts';
const createArticleId = () => globalThis.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(36).slice(2)}`;

const readSavedArticles = () => {
    try {
        const savedArticles = JSON.parse(window.localStorage.getItem(articleStorageKey) ?? '[]');

        if (!Array.isArray(savedArticles)) {
            return [];
        }

        let updated = false;
        savedArticles.forEach((article) => {
            if (!article.id) {
                article.id = createArticleId();
                updated = true;
            }
        });
        if (updated) {
            window.localStorage.setItem(articleStorageKey, JSON.stringify(savedArticles));
        }
        return savedArticles;
    } catch {
        return [];
    }
};

const showArticleAlert = ({ titleText, messageText, type = 'success', confirmText = 'Oke', cancelText = '' }) => new Promise((resolve) => {
    const overlay = document.createElement('div');
    overlay.className = 'imi-sweet-alert';
    overlay.setAttribute('role', 'alertdialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-labelledby', 'imi-sweet-alert-title');
    overlay.setAttribute('aria-describedby', 'imi-sweet-alert-message');

    const closeAlert = (result) => {
        if (overlay.classList.contains('is-closing')) {
            return;
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            overlay.remove();
            resolve(result);

            return;
        }

        overlay.classList.add('is-closing');
        overlay.addEventListener('animationend', () => {
            overlay.remove();
            resolve(result);
        }, { once: true });
    };

    const dialog = document.createElement('div');
    dialog.className = 'imi-sweet-alert__dialog';

    const icon = document.createElement('span');
    icon.className = `imi-sweet-alert__icon imi-sweet-alert__icon--${type}`;
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = type === 'success' ? '✓' : type === 'warning' ? '!' : '×';

    const title = document.createElement('h2');
    title.className = 'imi-sweet-alert__title';
    title.id = 'imi-sweet-alert-title';
    title.textContent = titleText;

    const message = document.createElement('p');
    message.className = 'imi-sweet-alert__message';
    message.id = 'imi-sweet-alert-message';
    message.textContent = messageText;

    const actions = document.createElement('div');
    actions.className = 'imi-sweet-alert__actions';

    if (cancelText) {
        const cancelButton = document.createElement('button');
        cancelButton.className = 'imi-sweet-alert__cancel';
        cancelButton.type = 'button';
        cancelButton.textContent = cancelText;
        cancelButton.addEventListener('click', () => {
            closeAlert(false);
        }, { once: true });
        actions.appendChild(cancelButton);
    }

    const confirmButton = document.createElement('button');
    confirmButton.className = `imi-sweet-alert__confirm${type === 'warning' ? ' imi-sweet-alert__confirm--warning' : ''}`;
    confirmButton.type = 'button';
    confirmButton.textContent = confirmText;
    confirmButton.addEventListener('click', () => {
        closeAlert(true);
    }, { once: true });
    actions.appendChild(confirmButton);

    dialog.append(icon, title, message, actions);
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);
    confirmButton.focus();
});

window.showPublicError = (message) => showArticleAlert({
    titleText: document.documentElement.lang === 'zh-CN' ? '失败' : (document.documentElement.lang === 'en' ? 'Failed' : 'Gagal'),
    messageText: message || (document.documentElement.lang === 'zh-CN' ? '无法完成该请求，请重试。' : (document.documentElement.lang === 'en' ? 'The request could not be completed. Please try again.' : 'Permintaan tidak dapat diproses. Silakan coba lagi.')),
    type: 'error',
    confirmText: document.documentElement.lang === 'zh-CN' ? '确定' : (document.documentElement.lang === 'en' ? 'OK' : 'Oke'),
});

if (articleForm) {
    const formStatus = document.querySelector('#article-form-status');

    articleForm.elements.date.value = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);

    articleForm.elements.title.addEventListener('input', () => {
        if (!articleForm.elements.slug.value.trim()) {
            articleForm.elements.slug.value = articleForm.elements.title.value
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
        }
    });

    articleForm.querySelectorAll('[data-editor-prefix], [data-editor-wrap]').forEach((button) => {
        button.addEventListener('click', () => {
            const editor = articleForm.elements.content;
            const start = editor.selectionStart;
            const end = editor.selectionEnd;
            const selectedText = editor.value.slice(start, end);
            const wrapper = button.dataset.editorWrap;
            const prefix = button.dataset.editorPrefix;
            const replacement = wrapper
                ? `${wrapper}${selectedText || 'teks'}${wrapper}`
                : `${prefix}${selectedText}`;

            editor.setRangeText(replacement, start, end, 'select');
            editor.focus();
        });
    });

    articleForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(articleForm);
        const coverFile = formData.get('cover');
        const saveButton = articleForm.querySelector('.article-form__save');

        saveButton.disabled = true;
        formStatus.textContent = '';

        try {
            let coverImage = '';

            if (coverFile instanceof File && coverFile.size > 0) {
                const bitmap = await createImageBitmap(coverFile);
                const scale = Math.min(1, 1200 / bitmap.width, 900 / bitmap.height);
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(bitmap.width * scale);
                canvas.height = Math.round(bitmap.height * scale);
                canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                coverImage = canvas.toDataURL('image/jpeg', 0.82);
                bitmap.close();
            }

            const newArticle = {
                id: createArticleId(),
                category: String(formData.get('category')),
                title: String(formData.get('title')).trim(),
                author: String(formData.get('author')).trim(),
                date: String(formData.get('date')),
                slug: String(formData.get('slug')).trim(),
                tags: String(formData.get('tags')).trim(),
                content: String(formData.get('content')).trim(),
                coverAlt: String(formData.get('coverAlt')).trim(),
                coverName: coverFile?.name ?? '',
                coverImage,
            };

            window.localStorage.setItem(articleStorageKey, JSON.stringify([newArticle, ...readSavedArticles()]));
            await showArticleAlert({ titleText: document.documentElement.lang === 'en' ? 'Success!' : 'Berhasil!', messageText: document.documentElement.lang === 'en' ? 'The article was added.' : 'Artikel berhasil ditambahkan.', confirmText: document.documentElement.lang === 'en' ? 'OK' : 'Oke' });
            window.location.assign(articleForm.dataset.listUrl);
        } catch {
            formStatus.textContent = document.documentElement.lang === 'en' ? 'The draft could not be saved in this browser. Please try again.' : 'Draft tidak dapat disimpan di browser ini. Coba lagi.';
            window.showPublicError(formStatus.textContent);
            saveButton.disabled = false;
        }
    });
}

const articleList = document.querySelector('#article-list');

if (articleList) {
    const emptyState = document.querySelector('#article-empty');
    const noResults = document.querySelector('#article-no-results');
    const searchInput = document.querySelector('#article-search');
    const categoryFilter = document.querySelector('#article-filter');
    const sortOrder = document.querySelector('#article-sort');
    const articles = readSavedArticles();

    const createTextElement = (tagName, className, text) => {
        const element = document.createElement(tagName);
        element.className = className;
        element.textContent = text;

        return element;
    };

    const renderArticles = () => {
        const searchTerm = searchInput.value.trim().toLocaleLowerCase('id');
        const selectedCategory = categoryFilter.value;
        const sortedArticles = [...articles].sort((first, second) => {
            const dateOrder = first.date.localeCompare(second.date);

            return sortOrder.value === 'newest' ? -dateOrder : dateOrder;
        });
        const visibleArticles = sortedArticles.filter((article) => {
            const matchesSearch = `${article.title} ${article.content} ${article.tags}`.toLocaleLowerCase('id').includes(searchTerm);
            const matchesCategory = selectedCategory === 'all' || article.category === selectedCategory;

            return matchesSearch && matchesCategory;
        });

        articleList.replaceChildren();
        visibleArticles.forEach((article) => {
            const card = document.createElement('article');
            card.className = 'article-entry';
            const cover = document.createElement('div');
            cover.className = 'article-entry__cover';

            if (article.coverImage) {
                const image = document.createElement('img');
                image.src = article.coverImage;
                image.alt = article.coverAlt || article.title;
                image.loading = 'lazy';
                cover.appendChild(image);
            } else {
                cover.classList.add('article-entry__cover--empty');
                cover.setAttribute('aria-hidden', 'true');
            }

            card.appendChild(cover);

            const body = document.createElement('div');
            body.className = 'article-entry__body';
            body.appendChild(createTextElement('h2', '', article.title));

            const date = document.createElement('time');
            date.dateTime = article.date;
            date.textContent = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(`${article.date}T00:00:00`));
            body.appendChild(date);

            const content = createTextElement('p', 'article-entry__content', article.content);
            body.appendChild(content);

            const actions = document.createElement('div');
            actions.className = 'article-entry__actions';

            content.classList.add('is-collapsed');
            const readMore = document.createElement('a');
            readMore.className = 'article-entry__read-more';
            readMore.textContent = 'Selengkapnya';
            readMore.href = `${articleList.dataset.detailUrl}?id=${encodeURIComponent(article.id)}`;
            actions.appendChild(readMore);

            const deleteArticle = document.createElement('button');
            deleteArticle.className = 'article-entry__delete';
            deleteArticle.type = 'button';
            deleteArticle.textContent = 'Hapus';
            deleteArticle.setAttribute('aria-label', `Hapus artikel ${article.title}`);
            deleteArticle.addEventListener('click', async () => {
                const shouldDelete = await showArticleAlert({
                    titleText: 'Hapus artikel?',
                    messageText: `Artikel "${article.title}" akan dihapus dari daftar. Tindakan ini tidak dapat dibatalkan.`,
                    type: 'warning',
                    confirmText: 'Ya, hapus',
                    cancelText: 'Batal',
                });

                if (!shouldDelete) {
                    return;
                }

                const articleIndex = articles.indexOf(article);

                if (articleIndex === -1) {
                    return;
                }

                articles.splice(articleIndex, 1);

                try {
                    window.localStorage.setItem(articleStorageKey, JSON.stringify(articles));
                    renderArticles();
                } catch {
                    articles.splice(articleIndex, 0, article);
                    await showArticleAlert({
                        titleText: document.documentElement.lang === 'en' ? 'Failed to delete' : 'Gagal menghapus',
                        messageText: document.documentElement.lang === 'en' ? 'The article could not be removed from browser storage.' : 'Artikel tidak dapat dihapus dari penyimpanan browser.',
                        type: 'error',
                    });
                }
            });
            actions.appendChild(deleteArticle);
            body.appendChild(actions);

            card.appendChild(body);
            articleList.appendChild(card);
        });

        emptyState.hidden = articles.length > 0;
        noResults.hidden = articles.length === 0 || visibleArticles.length > 0;
    };

    [searchInput, categoryFilter, sortOrder].forEach((control) => {
        control.addEventListener('input', renderArticles);
        control.addEventListener('change', renderArticles);
    });

    renderArticles();
}

const articleDetail = document.querySelector('#article-detail');

if (articleDetail) {
    const articleId = new URLSearchParams(window.location.search).get('id');
    const article = readSavedArticles().find((item) => item.id === articleId);
    const missingMessage = document.querySelector('#article-detail-missing');

    if (article) {
        document.querySelector('#article-detail-title').textContent = article.title;
        const date = document.querySelector('#article-detail-date');
        date.dateTime = article.date;
        date.textContent = new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(new Date(`${article.date}T00:00:00`));
        document.querySelector('#article-detail-author').textContent = article.author || '';
        document.querySelector('#article-detail-content').textContent = article.content;
        const cover = document.querySelector('#article-detail-cover');
        if (article.coverImage) {
            cover.src = article.coverImage;
            cover.alt = article.coverAlt || article.title;
            cover.hidden = false;
        }
        document.title = `${article.title} - Interskala Medika Indonesia`;
        articleDetail.hidden = false;
    } else {
        missingMessage.hidden = false;
    }
}

const publicGalleryLinks = Array.from(document.querySelectorAll('[data-public-gallery]'));
if (publicGalleryLinks.length) {
    const dialog = document.createElement('dialog');
    dialog.className = 'public-lightbox';
    const english = document.documentElement.lang === 'en';
    dialog.innerHTML = `<div class="public-lightbox__content"><button type="button" class="public-lightbox__close" aria-label="${english ? 'Close' : 'Tutup'}">×</button><button type="button" class="public-lightbox__previous" aria-label="${english ? 'Previous image' : 'Gambar sebelumnya'}">‹</button><figure><img alt=""><figcaption></figcaption></figure><button type="button" class="public-lightbox__next" aria-label="${english ? 'Next image' : 'Gambar berikutnya'}">›</button></div>`;
    document.body.appendChild(dialog);
    const image = dialog.querySelector('img');
    image.addEventListener('error', () => { if (dialog.open) dialog.close(); window.showPublicError?.(document.documentElement.lang === 'en' ? 'The image could not be loaded.' : 'Gambar tidak dapat dimuat.'); });
    const caption = dialog.querySelector('figcaption');
    let items = [];
    let index = 0;
    let opener = null;
    const display = () => {
        const item = items[index];
        image.src = item.href;
        image.alt = item.querySelector('img')?.alt || '';
        caption.textContent = item.dataset.galleryCaption || '';
        dialog.querySelector('.public-lightbox__previous').hidden = items.length < 2;
        dialog.querySelector('.public-lightbox__next').hidden = items.length < 2;
    };
    const move = (direction) => { index = (index + direction + items.length) % items.length; display(); };
    publicGalleryLinks.forEach((link) => link.addEventListener('click', (event) => {
        event.preventDefault();
        opener = link;
        items = publicGalleryLinks.filter((item) => item.dataset.publicGallery === link.dataset.publicGallery);
        index = items.indexOf(link);
        display();
        dialog.showModal();
    }));
    dialog.querySelector('.public-lightbox__close').addEventListener('click', () => dialog.close());
    dialog.querySelector('.public-lightbox__previous').addEventListener('click', () => move(-1));
    dialog.querySelector('.public-lightbox__next').addEventListener('click', () => move(1));
    dialog.addEventListener('close', () => opener?.focus());
    dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
    dialog.addEventListener('keydown', (event) => { if (event.key === 'ArrowLeft') move(-1); if (event.key === 'ArrowRight') move(1); });
    let touchStart = 0;
    dialog.addEventListener('touchstart', (event) => { touchStart = event.changedTouches[0].clientX; }, { passive: true });
    dialog.addEventListener('touchend', (event) => { const distance = event.changedTouches[0].clientX - touchStart; if (Math.abs(distance) > 50) move(distance < 0 ? 1 : -1); }, { passive: true });
}

const heroSlider = document.querySelector('[data-hero-slider]');
const heroSlides = heroSlider ? [...heroSlider.querySelectorAll('[data-hero-slide]')] : [];
if (heroSlides.length > 1) {
    let heroIndex = 0;
    let heroTimer;
    let touchStartX = null;
    const showHeroSlide = (index) => {
        heroIndex = (index + heroSlides.length) % heroSlides.length;
        heroSlides.forEach((slide, slideIndex) => {
            const active = slideIndex === heroIndex;
            slide.hidden = !active;
            slide.setAttribute('aria-hidden', String(!active));
        });
    };
    const stopHero = () => window.clearInterval(heroTimer);
    const startHero = () => {
        stopHero();
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) heroTimer = window.setInterval(() => showHeroSlide(heroIndex + 1), 6000);
    };
    heroSlider.querySelector('[data-hero-previous]')?.addEventListener('click', () => { showHeroSlide(heroIndex - 1); startHero(); });
    heroSlider.querySelector('[data-hero-next]')?.addEventListener('click', () => { showHeroSlide(heroIndex + 1); startHero(); });
    heroSlider.addEventListener('mouseenter', stopHero);
    heroSlider.addEventListener('mouseleave', startHero);
    heroSlider.addEventListener('focusin', stopHero);
    heroSlider.addEventListener('focusout', (event) => { if (!heroSlider.contains(event.relatedTarget)) startHero(); });
    heroSlider.addEventListener('touchstart', (event) => { touchStartX = event.changedTouches[0].clientX; }, { passive: true });
    heroSlider.addEventListener('touchend', (event) => {
        if (touchStartX === null) return;
        const distance = event.changedTouches[0].clientX - touchStartX;
        if (Math.abs(distance) > 50) { showHeroSlide(heroIndex + (distance < 0 ? 1 : -1)); startHero(); }
        touchStartX = null;
    }, { passive: true });
    startHero();
}

document.documentElement.classList.add('public-page-transition');
let publicNavigationStarted = false;

const publicRevealCandidates = document.querySelectorAll(
    'main > section, main > article, main .product-card, main .public-documentation__card, main .media-card, main .news-card, main .careers-card, main .certificate-card, main .stat-card, main .about-stats__card, main .vision-card, main .factory-card, main :is(h1, h2, h3, h4, p, img, video, button, a, li, blockquote), .site-footer',
);

if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
    const publicRevealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.01, rootMargin: '0px 0px 12% 0px' });

    publicRevealCandidates.forEach((element, index) => {
        element.classList.add('public-scroll-reveal');
        element.classList.add(`public-scroll-reveal--${index % 3}`);
        element.style.setProperty('--public-reveal-delay', `${(index % 3) * 45}ms`);
        publicRevealObserver.observe(element);
    });
}

document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    if (!(event.target instanceof Element)) return;

    const link = event.target.closest('a[href]');
    if (!link || link.hasAttribute('download') || link.target === '_blank' || link.hasAttribute('data-public-gallery')) return;

    const destination = new URL(link.href, window.location.href);
    if (!['http:', 'https:'].includes(destination.protocol) || destination.origin !== window.location.origin) return;
    if (/\.(pdf|png|jpe?g|gif|webp|svg|zip)(?:$|[?#])/i.test(destination.pathname)) return;
    if (destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash) return;
    if (destination.href === window.location.href) return;

    event.preventDefault();
    if (publicNavigationStarted) return;
    publicNavigationStarted = true;
    navigateWithFade(destination.href);
});

window.addEventListener('pageshow', () => {
    publicNavigationStarted = false;
    document.documentElement.classList.remove('is-leaving');
});
