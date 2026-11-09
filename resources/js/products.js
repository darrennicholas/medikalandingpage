const categorySelect = document.querySelector('#filter-kategori');
const subcategorySelect = document.querySelector('#filter-subkategori');
const resetFilters = document.querySelector('#reset-filters');
const productResults = document.querySelector('#product-results');
const productPagination = document.querySelector('#product-pagination');
const productActiveHeading = document.querySelector('#product-active-heading');
const productNoMatches = document.querySelector('#product-no-matches');
const productResultsArea = document.querySelector('.product-results-area');
const productSections = Array.from(document.querySelectorAll('.product-category-section'));
const categoryFilterButtons = Array.from(document.querySelectorAll('[data-category-filter]'));
const categoryToggleButtons = Array.from(document.querySelectorAll('[data-category-toggle]'));
const brochureModal = document.querySelector('#product-modal');

if (categorySelect && subcategorySelect && resetFilters && productResults && productPagination) {
    const pageSize = 12;
    const productEntries = productSections.flatMap((section) =>
        Array.from(section.querySelectorAll('.product-card'), (card) => ({ card, section })),
    );
    let currentPage = 1;
    let selectedProductFilter = '';

    const isEnglish = () => document.documentElement.lang === 'en';

    const getActiveFilterButton = () => categoryFilterButtons.find((button) =>
        button.dataset.categoryFilter === categorySelect.value
        && (button.dataset.productFilter || '') === selectedProductFilter,
    );

    const getSelectedCategoryIds = () => {
        const activeFilterButton = getActiveFilterButton();
        const categoryIds = activeFilterButton?.dataset.categoryIds || activeFilterButton?.dataset.categoryFilter || '';

        return categoryIds.split(',').filter(Boolean);
    };

    const syncCategoryNavigation = () => {
        const activeFilterButton = getActiveFilterButton();
        const isAllProducts = categorySelect.value === 'all';

        categoryFilterButtons.forEach((button) => {
            const isSelectedCategory = button.dataset.categoryFilter === categorySelect.value;
            const isSelectedProduct = (button.dataset.productFilter || '') === selectedProductFilter;
            const isActive = isSelectedCategory && isSelectedProduct;

            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });

        productActiveHeading.hidden = isAllProducts;
        productActiveHeading.textContent = isAllProducts
            ? ''
            : activeFilterButton?.dataset.categoryTitle || activeFilterButton?.textContent.trim() || '';
        productResultsArea.classList.toggle('is-category-filtered', !isAllProducts);
    };

    const populateSubcategories = () => {
        const selectedCategoryIds = getSelectedCategoryIds();
        const subcategories = [...new Set(productEntries
            .filter(({ card }) => categorySelect.value === 'all' || selectedCategoryIds.includes(card.dataset.category))
            .map(({ card }) => card.dataset.subcategory))]
            .filter(Boolean);

        subcategorySelect.replaceChildren();
        const allOption = document.createElement('option');
        allOption.value = 'all';
        allOption.textContent = isEnglish() ? '-- All Subcategories --' : '-- Semua Subkategori --';
        subcategorySelect.appendChild(allOption);

        subcategories.forEach((subcategory) => {
            const option = document.createElement('option');
            option.value = subcategory;
            option.textContent = subcategory;
            subcategorySelect.appendChild(option);
        });

        subcategorySelect.disabled = subcategories.length === 0;
    };

    const pageNumbers = (totalPages) => {
        if (totalPages <= 7) {
            return Array.from({ length: totalPages }, (_, index) => index + 1);
        }

        const visible = new Set([1, totalPages, currentPage - 1, currentPage, currentPage + 1]);
        const pages = [...visible].filter((page) => page >= 1 && page <= totalPages).sort((first, second) => first - second);
        const result = [];

        pages.forEach((page, index) => {
            if (index > 0 && page - pages[index - 1] > 1) {
                result.push('…');
            }

            result.push(page);
        });

        return result;
    };

    const addPageButton = (label, page, disabled = false, active = false) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `product-pagination__button${active ? ' is-active' : ''}`;
        button.textContent = label;
        button.disabled = disabled;
        button.setAttribute('aria-label', typeof page === 'number' ? `Halaman ${page}` : label);

        if (active) {
            button.setAttribute('aria-current', 'page');
        }

        button.addEventListener('click', () => {
            currentPage = page;
            renderProducts();
            const top = document.querySelector('#product-categories').getBoundingClientRect().top + window.scrollY - 95;
            window.scrollTo({ top, behavior: 'smooth' });
        });

        productPagination.appendChild(button);
    };

    const renderProducts = () => {
        const selectedCategory = categorySelect.value;
        const selectedSubcategory = subcategorySelect.value;
        const selectedCategoryIds = getSelectedCategoryIds();
        const productFilterTerms = selectedProductFilter
            .split('|')
            .map((term) => term.trim().toLowerCase())
            .filter(Boolean);
        const matchingEntries = productEntries.filter(({ card }) =>
            (selectedCategory === 'all' || selectedCategoryIds.includes(card.dataset.category))
            && (selectedSubcategory === 'all' || card.dataset.subcategory === selectedSubcategory)
            && (productFilterTerms.length === 0 || productFilterTerms.some((term) => card.textContent.toLowerCase().includes(term))),
        );
        const shouldPaginate = selectedCategory !== 'all';
        const totalPages = shouldPaginate ? Math.ceil(matchingEntries.length / pageSize) : 1;
        currentPage = Math.min(currentPage, Math.max(totalPages, 1));
        const firstIndex = (currentPage - 1) * pageSize;
        const visibleEntries = shouldPaginate
            ? matchingEntries.slice(firstIndex, firstIndex + pageSize)
            : matchingEntries;
        const visibleCards = new Set(visibleEntries.map(({ card }) => card));

        productEntries.forEach(({ card }) => {
            card.hidden = !visibleCards.has(card);
        });

        productSections.forEach((section) => {
            const hasVisibleCard = visibleEntries.some((entry) => entry.section === section);
            const sectionCategoryIds = section.dataset.categoryIds.split(',').filter(Boolean);
            const isEmptySection = section.querySelectorAll('.product-card').length === 0;
            const selectedEmptyCategory = isEmptySection
                && (selectedCategory === 'all' || sectionCategoryIds.some((categoryId) => selectedCategoryIds.includes(categoryId)));
            section.hidden = !hasVisibleCard && !selectedEmptyCategory;
        });

        const hasSelectedEmptyCategory = productSections.some((section) =>
            section.querySelectorAll('.product-card').length === 0
            && (selectedCategory === 'all' || section.dataset.categoryIds.split(',').some((categoryId) => selectedCategoryIds.includes(categoryId))),
        );
        productNoMatches.hidden = matchingEntries.length > 0 || hasSelectedEmptyCategory;

        productResults.replaceChildren();
        const count = document.createElement('strong');
        count.textContent = String(matchingEntries.length);
        productResults.append(
            document.createTextNode(isEnglish() ? 'Showing ' : 'Menampilkan '),
            count,
            document.createTextNode(isEnglish() ? ' products' : ' produk'),
        );

        if (selectedCategory !== 'all') {
            const categoryName = categorySelect.selectedOptions[0].textContent;
            const highlight = document.createElement('span');
            highlight.className = 'product-results__highlight';
            highlight.textContent = categoryName;
            productResults.append(document.createTextNode(isEnglish() ? ' in ' : ' di kategori '), highlight);
        }

        if (selectedSubcategory !== 'all') {
            const highlight = document.createElement('span');
            highlight.className = 'product-results__highlight';
            highlight.textContent = selectedSubcategory;
            productResults.append(document.createTextNode(isEnglish() ? ' subcategory ' : ' subkategori '), highlight);
        }

        productPagination.replaceChildren();
        productPagination.hidden = !shouldPaginate || totalPages <= 1;
        syncCategoryNavigation();

        if (totalPages > 1) {
            const pageInfo = document.createElement('p');
            pageInfo.className = 'product-pagination__info';
            const firstVisible = firstIndex + 1;
            const lastVisible = Math.min(firstIndex + pageSize, matchingEntries.length);
            pageInfo.textContent = isEnglish()
                ? `Showing ${firstVisible}–${lastVisible} of ${matchingEntries.length} products`
                : `Menampilkan ${firstVisible}–${lastVisible} dari ${matchingEntries.length} produk`;
            productPagination.appendChild(pageInfo);

            addPageButton(isEnglish() ? '‹ Previous' : '‹ Prev', currentPage - 1, currentPage === 1);
            pageNumbers(totalPages).forEach((page) => {
                if (page === '…') {
                    const dots = document.createElement('span');
                    dots.className = 'product-pagination__dots';
                    dots.textContent = page;
                    productPagination.appendChild(dots);
                } else {
                    addPageButton(String(page), page, false, page === currentPage);
                }
            });
            addPageButton(isEnglish() ? 'Next ›' : 'Next ›', currentPage + 1, currentPage === totalPages);
        }
    };

    categorySelect.addEventListener('change', () => {
        selectedProductFilter = '';
        populateSubcategories();
        currentPage = 1;
        renderProducts();
    });

    subcategorySelect.addEventListener('change', () => {
        selectedProductFilter = '';
        currentPage = 1;
        renderProducts();
    });

    resetFilters.addEventListener('click', () => {
        categorySelect.value = 'all';
        selectedProductFilter = '';
        populateSubcategories();
        currentPage = 1;
        renderProducts();
    });

    categoryFilterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            categorySelect.value = button.dataset.categoryFilter;
            selectedProductFilter = button.dataset.productFilter || '';
            populateSubcategories();
            subcategorySelect.value = button.dataset.subcategoryFilter || 'all';
            currentPage = 1;
            renderProducts();
        });
    });

    categoryToggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const isExpanded = button.getAttribute('aria-expanded') === 'true';
            const childCategories = document.querySelector(`#${button.getAttribute('aria-controls')}`);

            if (!isExpanded) {
                categoryToggleButtons.forEach((otherButton) => {
                    if (otherButton === button) {
                        return;
                    }

                    otherButton.setAttribute('aria-expanded', 'false');
                    document.querySelector(`#${otherButton.getAttribute('aria-controls')}`).hidden = true;
                });
            }

            button.setAttribute('aria-expanded', String(!isExpanded));
            childCategories.hidden = isExpanded;
        });
    });

    document.querySelector('#language-picker')?.addEventListener('change', () => {
        if (subcategorySelect.options.length > 0) {
            subcategorySelect.options[0].textContent = isEnglish() ? '-- All Subcategories --' : '-- Semua Subkategori --';
        }

        renderProducts();
    });

    populateSubcategories();
    renderProducts();
}

if (brochureModal) {
    const frame = brochureModal.querySelector('#brochure-frame');
    const error = brochureModal.querySelector('#brochure-error');
    const errorMessage = error.querySelector('p');
    const productName = brochureModal.querySelector('#brochure-product-name');
    const downloadLink = brochureModal.querySelector('#brochure-download');
    const closeButton = brochureModal.querySelector('.product-modal__close');
    let previousFocus;

    const closeModal = () => {
        brochureModal.hidden = true;
        frame.src = 'about:blank';
        document.body.classList.remove('modal-open');
        previousFocus?.focus();
    };

    document.querySelectorAll('.product-card__brochure').forEach((button) => {
        button.addEventListener('click', () => {
            const brochureUrl = button.dataset.brochureUrl.trim();
            const driveFile = brochureUrl.match(/drive\.google\.com\/file\/d\/([^/]+)/);
            previousFocus = button;
            productName.textContent = button.dataset.productName;
            brochureModal.hidden = false;
            document.body.classList.add('modal-open');
            frame.hidden = !brochureUrl;
            error.hidden = Boolean(brochureUrl);
            downloadLink.hidden = !brochureUrl;

            if (brochureUrl) {
                frame.src = driveFile
                    ? `https://drive.google.com/file/d/${driveFile[1]}/preview`
                    : brochureUrl;
                downloadLink.href = brochureUrl;
            } else {
                errorMessage.textContent = document.documentElement.lang === 'en'
                    ? 'A brochure is not available for this product yet.'
                    : 'Brosur untuk produk ini belum tersedia.';
            }

            closeButton.focus();
        });
    });

    brochureModal.addEventListener('click', (event) => {
        if (event.target.closest('[data-close-modal]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !brochureModal.hidden) {
            closeModal();
        }
    });
}
