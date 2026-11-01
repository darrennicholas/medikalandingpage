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
    let currentIndex = 0;
    let resetTimer;

    originalSlides.slice(0, 4).forEach((slide) => {
        const clone = slide.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        brandTrack.appendChild(clone);
    });

    const renderCarousel = (animate = true) => {
        const slideWidth = brandTrack.children[0].getBoundingClientRect().width;
        brandTrack.style.transition = animate ? 'transform 500ms ease' : 'none';
        brandTrack.style.transform = 'translate3d(-' + currentIndex * slideWidth + 'px, 0, 0)';

        brandDots.querySelectorAll('button').forEach((dot, index) => {
            dot.classList.toggle('is-active', index === currentIndex % originalSlides.length);
            dot.setAttribute('aria-current', index === currentIndex % originalSlides.length ? 'true' : 'false');
        });
    };

    originalSlides.forEach((slide, index) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.setAttribute('aria-label', 'Tampilkan brand ' + (index + 1));
        dot.addEventListener('click', () => {
            clearTimeout(resetTimer);
            currentIndex = index;
            renderCarousel();
        });
        brandDots.appendChild(dot);
    });

    const nextBrand = () => {
        currentIndex += 1;
        renderCarousel();

        if (currentIndex === originalSlides.length) {
            resetTimer = window.setTimeout(() => {
                currentIndex = 0;
                renderCarousel(false);
            }, 510);
        }
    };

    renderCarousel(false);
    window.addEventListener('resize', () => renderCarousel(false));

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        let autoplay = window.setInterval(nextBrand, 3000);
        const carousel = brandTrack.closest('.brand-carousel');

        carousel?.addEventListener('mouseenter', () => window.clearInterval(autoplay));
        carousel?.addEventListener('mouseleave', () => {
            autoplay = window.setInterval(nextBrand, 3000);
        });
        carousel?.addEventListener('focusin', () => window.clearInterval(autoplay));
        carousel?.addEventListener('focusout', (event) => {
            if (!carousel.contains(event.relatedTarget)) {
                autoplay = window.setInterval(nextBrand, 3000);
            }
        });
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
    news: 'Articles & News',
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
};

document.querySelector('#language-picker')?.addEventListener('change', (event) => {
    const isEnglish = event.target.value === 'en';
    document.documentElement.lang = isEnglish ? 'en' : 'id';

    document.querySelectorAll('[data-i18n]').forEach((element) => {
        const key = element.dataset.i18n;
        element.textContent = isEnglish ? englishCopy[key] : originalCopy.get(key);
    });
});
