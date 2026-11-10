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

const certificateTrack = document.querySelector('#certificate-track');
const certificateDots = document.querySelector('#certificate-dots');

if (certificateTrack && certificateDots) {
    const certificates = Array.from(certificateTrack.children);
    let currentCertificate = 0;
    let autoplay;

    const visibleCertificates = () => {
        if (window.innerWidth <= 576) {
            return 1;
        }

        return window.innerWidth <= 768 ? 2 : 3;
    };

    const renderCertificates = () => {
        const lastPosition = certificates.length - visibleCertificates();
        currentCertificate = Math.min(currentCertificate, lastPosition);
        const slideWidth = certificates[0].getBoundingClientRect().width + parseFloat(getComputedStyle(certificates[0]).marginRight);
        certificateTrack.style.transform = `translate3d(-${currentCertificate * slideWidth}px, 0, 0)`;
        certificateDots.replaceChildren();

        for (let index = 0; index <= lastPosition; index += 1) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.classList.toggle('is-active', index === currentCertificate);
            dot.setAttribute('aria-label', `Tampilkan sertifikat ${index + 1}`);
            dot.setAttribute('aria-current', index === currentCertificate ? 'true' : 'false');
            dot.addEventListener('click', () => {
                currentCertificate = index;
                renderCertificates();
            });
            certificateDots.appendChild(dot);
        }
    };

    renderCertificates();
    window.addEventListener('resize', renderCertificates);

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const startAutoplay = () => {
            autoplay = window.setInterval(() => {
                currentCertificate = (currentCertificate + 1) % (certificates.length - visibleCertificates() + 1);
                renderCertificates();
            }, 3500);
        };

        startAutoplay();
        certificateTrack.closest('.certificate-carousel')?.addEventListener('mouseenter', () => window.clearInterval(autoplay));
        certificateTrack.closest('.certificate-carousel')?.addEventListener('mouseleave', startAutoplay);
        certificateTrack.closest('.certificate-carousel')?.addEventListener('focusin', () => window.clearInterval(autoplay));
        certificateTrack.closest('.certificate-carousel')?.addEventListener('focusout', (event) => {
            if (!certificateTrack.closest('.certificate-carousel').contains(event.relatedTarget)) {
                startAutoplay();
            }
        });
    }
}

const originalCopy = new Map(
    Array.from(document.querySelectorAll('[data-i18n]'), (element) => [
        element.dataset.i18n,
        element.textContent,
    ]),
);

const englishCopy = {
    home: 'Home',
    about: 'About',
    products: 'Products',
    careers: 'Careers',
    contact: 'Contact',
    articlesNews: 'Articles & News',
    coverageTitle: 'Our Reach',
    coverageSubtitle: 'Serving all of Indonesia through an extensive distribution network',
    salesNetwork: 'Sales Network',
    coverageDescription: 'PT Interskala Medika Indonesia has a distribution network covering all regions of Indonesia, from Sabang to Merauke. We are committed to providing quality medical devices to every corner of the country.',
    provinces: 'Provinces',
    cities: 'Cities/Regencies',
    partners: 'Partners',
    contactTitle: 'Ready to Help With Your Medical Equipment Needs',
    contactSubtitle: 'Please contact us for further information',
    contactButton: 'Contact Us!',
    brandTitle: 'Our Brands',
    brandSubtitle: 'Working with trusted brands',
    aboutTagline: 'IMPORTER | DISTRIBUTOR | MEDICAL DEVICE MANUFACTURER',
    aboutTitle: 'About Us',
    aboutDescriptionOne: 'PT Interskala Medika Indonesia imports, distributes, and manufactures medical devices, especially diagnostic and in vitro (IVD) products. With CE and ISO certifications, we are committed to providing safe, high-quality products that meet international standards and support better healthcare.',
    aboutDescriptionTwo: 'We keep innovating in technology, efficiency, and accessibility, backed by experience, an extensive distribution network, and a professional team. PT Interskala Medika Indonesia is ready to be a strategic partner for healthcare institutions, laboratories, and medical providers throughout Indonesia.',
    visionMissionTitle: 'Vision & Mission',
    visionTitle: 'Vision',
    visionDescription: 'To be a leading strategic partner in providing globally standardized medical device solutions for a more reliable and sustainable healthcare future in Indonesia.',
    missionTitle: 'Mission',
    missionDescriptionOne: 'Provide high-quality medical instruments that meet international standards for precise healthcare.',
    missionDescriptionTwo: 'Develop innovative and competitive diagnostic solutions that meet healthcare facility needs effectively.',
    missionDescriptionThree: 'Build long-term customer relationships through integrity, professionalism, and sustainable service.',
    yearsExperience: 'Years of Experience',
    aboutPartners: 'Partners',
    provinceReach: 'Provinces Reached',
    certificatesTitle: 'Certificates & Awards',
    certificatesSubtitle: 'Recognition of our commitment to quality and international standards',
    productTagline: 'WE BRING THE FUTURE HEALTH CARE',
    productHeading: 'Find Our Best Products',
    productDescription: 'Explore our range of high-quality products for your medical and healthcare needs',
    categoryLabel: 'Category',
    allCategories: '-- All Categories --',
    subcategoryLabel: 'Subcategory',
    allSubcategories: '-- All Subcategories --',
    resetFilter: 'Reset Filter',
    emptyCategory: 'No products are available in this category yet.',
    buyProduct: 'Buy',
    brochure: 'Brochure',
    brochurePreview: 'Brochure Preview',
    brochureUnavailable: 'Could Not Load Brochure',
    brochureUnavailableMessage: 'The PDF could not be displayed. Please download it directly.',
    brochureHint: 'Right-click the PDF to zoom or download',
    downloadPdf: 'Download PDF',
    contactPageTitle: 'Contact Us',
    contactPageIntro: 'We are ready to help with any questions and needs related to IMI Medika products and services. Our professional team is ready to provide the best solution for you.',
    operatingHours: 'Operating Hours',
    weekdays: 'Monday - Friday:',
    weekend: 'Saturday & Sunday:',
    closed: 'Closed',
    socialTitle: 'Follow Us on Social Media',
    socialDescription: 'Stay connected with us on social media for the latest information about IMI Medika products, services, and promotions.',
    documentationTitle: 'DOCUMENTATION',
    documentationIntro: 'Discover the latest articles and news about healthcare',
    exploreNow: 'EXPLORE NOW',
    mediaHeading: 'IMI MEDIKA MEDIA',
    articleCardTitle: 'Articles',
    newsCardTitle: 'News',
    readMore: 'Read More',
    articlesAvailable: 'Available Articles',
    latestNews: 'Latest News',
    articleListTitle: 'Articles',
    newsListTitle: 'Latest News',
    articleListIntro: 'Discover informative health articles to help improve your quality of life',
    newsListIntro: 'Follow the latest updates on healthcare, services, and IMI Medika activities',
    noArticles: 'No Articles Yet',
    noNews: 'No News Yet',
    noNewsDescription: 'Add your first news story to share with website visitors.',
    backToDocumentation: 'Back to Documentation',
};

document.querySelector('#language-picker')?.addEventListener('change', (event) => {
    const isEnglish = event.target.value === 'en';
    document.documentElement.lang = isEnglish ? 'en' : 'id';

    document.querySelectorAll('[data-i18n]').forEach((element) => {
        const key = element.dataset.i18n;
        element.textContent = isEnglish ? englishCopy[key] : originalCopy.get(key);
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
