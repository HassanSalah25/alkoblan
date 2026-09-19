// ===================================
// AL-KOBLAN - MAIN JAVASCRIPT
// ===================================

document.addEventListener('DOMContentLoaded', function () {

    // ===================================
    // NAVBAR SCROLL EFFECT
    // ===================================
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 80) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ===================================
    // MOBILE MENU
    // ===================================
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const mobileClose = document.getElementById('mobileClose');

    function openMobileMenu() {
        hamburger && hamburger.classList.add('open');
        mobileMenu && mobileMenu.classList.add('open');
        mobileOverlay && mobileOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        hamburger && hamburger.classList.remove('open');
        mobileMenu && mobileMenu.classList.remove('open');
        mobileOverlay && mobileOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    hamburger && hamburger.addEventListener('click', openMobileMenu);
    mobileClose && mobileClose.addEventListener('click', closeMobileMenu);
    mobileOverlay && mobileOverlay.addEventListener('click', closeMobileMenu);

    // ===================================
    // MOBILE DROPDOWN
    // ===================================
    window.toggleMobileDropdown = function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.toggle('open');
        }
    };

    // ===================================
    // HERO SLIDER
    // ===================================
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    let currentSlide = 0;
    let slideInterval;

    function goToSlide(index) {
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));
        if (slides[index]) slides[index].classList.add('active');
        if (dots[index]) dots[index].classList.add('active');
        currentSlide = index;
    }

    function nextSlide() {
        goToSlide((currentSlide + 1) % slides.length);
    }

    function prevSlide() {
        goToSlide((currentSlide - 1 + slides.length) % slides.length);
    }

    function startAutoplay() {
        slideInterval = setInterval(nextSlide, 5000);
    }

    function resetAutoplay() {
        clearInterval(slideInterval);
        startAutoplay();
    }

    if (slides.length > 0) {
        startAutoplay();

        document.getElementById('heroNext') && document.getElementById('heroNext').addEventListener('click', () => {
            nextSlide(); resetAutoplay();
        });
        document.getElementById('heroPrev') && document.getElementById('heroPrev').addEventListener('click', () => {
            prevSlide(); resetAutoplay();
        });

        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                goToSlide(parseInt(dot.dataset.index));
                resetAutoplay();
            });
        });
    }

    // ===================================
    // SEARCH OVERLAY
    // ===================================
    const searchBtn = document.getElementById('searchBtn');
    const searchOverlay = document.getElementById('searchOverlay');
    const searchClose = document.getElementById('searchClose');

    searchBtn && searchBtn.addEventListener('click', () => {
        searchOverlay.classList.add('open');
        searchOverlay.querySelector('.search-input') && searchOverlay.querySelector('.search-input').focus();
    });

    searchClose && searchClose.addEventListener('click', () => {
        searchOverlay.classList.remove('open');
    });

    searchOverlay && searchOverlay.addEventListener('click', (e) => {
        if (e.target === searchOverlay) {
            searchOverlay.classList.remove('open');
        }
    });

    // Close search with ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            searchOverlay && searchOverlay.classList.remove('open');
        }
    });

    // ===================================
    // BACK TO TOP
    // ===================================
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ===================================
    // TESTIMONIALS SLIDER
    // ===================================
    const testimonialsTrack = document.getElementById('testimonialsTrack');
    const testDots = document.querySelectorAll('.testimonial-dot');
    let currentTestimonial = 0;

    function goToTestimonial(index) {
        if (!testimonialsTrack) return;
        const cards = testimonialsTrack.querySelectorAll('.testimonial-card');
        if (cards.length === 0) return;
        currentTestimonial = Math.max(0, Math.min(index, cards.length - 1));
        testimonialsTrack.style.transform = `translateX(${currentTestimonial * 100}%)`;
        testDots.forEach(d => d.classList.remove('active'));
        if (testDots[currentTestimonial]) testDots[currentTestimonial].classList.add('active');
    }

    document.getElementById('testNext') && document.getElementById('testNext').addEventListener('click', () => {
        const cards = testimonialsTrack ? testimonialsTrack.querySelectorAll('.testimonial-card') : [];
        goToTestimonial((currentTestimonial + 1) % cards.length);
    });

    document.getElementById('testPrev') && document.getElementById('testPrev').addEventListener('click', () => {
        const cards = testimonialsTrack ? testimonialsTrack.querySelectorAll('.testimonial-card') : [];
        goToTestimonial((currentTestimonial - 1 + cards.length) % cards.length);
    });

    testDots.forEach(dot => {
        dot.addEventListener('click', () => {
            goToTestimonial(parseInt(dot.dataset.index));
        });
    });

    if (testimonialsTrack) {
        setInterval(() => {
            const cards = testimonialsTrack.querySelectorAll('.testimonial-card');
            goToTestimonial((currentTestimonial + 1) % cards.length);
        }, 6000);
    }

    // ===================================
    // SCROLL ANIMATIONS (GSAP)
    // ===================================
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);

        const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
        
        revealElements.forEach(el => {
            let xPos = 0;
            let yPos = 50;

            if (el.classList.contains('reveal-left')) xPos = -50;
            if (el.classList.contains('reveal-right')) xPos = 50;

            gsap.fromTo(el, 
                { opacity: 0, x: xPos, y: yPos },
                {
                    opacity: 1, 
                    x: 0, 
                    y: 0, 
                    duration: 1,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: el,
                        start: "top 85%",
                        toggleActions: "play none none reverse"
                    }
                }
            );
        });

        // Advanced Hero Animation
        const heroTitle = document.querySelector('.hero-title');
        if (heroTitle) {
            gsap.fromTo(heroTitle, 
                { opacity: 0, y: 30, scale: 0.95 }, 
                { opacity: 1, y: 0, scale: 1, duration: 1.2, ease: "back.out(1.7)", delay: 0.2 }
            );
        }
    } else {
        // Fallback
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => revealObserver.observe(el));
    }

    // ===================================
    // COUNTER ANIMATION
    // ===================================
    function animateCounter(el, target, duration = 1800) {
        const startTime = performance.now();
        const locale = document.body.classList.contains('en') ? 'en-US' : 'ar-SA';
        const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);

        const tick = (now) => {
            const progress = Math.min((now - startTime) / duration, 1);
            const value = Math.floor(easeOutCubic(progress) * target);
            el.textContent = value.toLocaleString(locale);
            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                el.textContent = target.toLocaleString(locale);
            }
        };

        requestAnimationFrame(tick);
    }

    const counterElements = document.querySelectorAll('[data-count]');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.dataset.count, 10);
                if (!Number.isNaN(target)) {
                    animateCounter(entry.target, target);
                }
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.35 });

    counterElements.forEach(el => counterObserver.observe(el));

    // ===================================
    // PRODUCTS FILTER (homepage tab buttons only - shop page filters server-side)
    // ===================================
    const filterBtns = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card[data-category]');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.dataset.filter;
            productCards.forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.style.display = '';
                    card.style.animation = 'scaleIn 0.4s ease both';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // ===================================
    // GALLERY THUMBS (Product Detail)
    // ===================================
    const thumbs = document.querySelectorAll('.gallery-thumb');
    const mainImg = document.querySelector('.gallery-main img');

    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            thumbs.forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
            if (mainImg) {
                mainImg.src = thumb.querySelector('img').src;
            }
        });
    });

    // ===================================
    // QUANTITY INPUT
    // ===================================
    const qtyPlus = document.querySelectorAll('.qty-btn.plus');
    const qtyMinus = document.querySelectorAll('.qty-btn.minus');

    qtyPlus.forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('.qty-input');
            if (input) input.value = parseInt(input.value || 0) + 1;
        });
    });

    qtyMinus.forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('.qty-input');
            if (input && parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });

    // ===================================
    // AUTH TABS (Login/Register Page)
    // ===================================
    const authTabs = document.querySelectorAll('.auth-tab');
    const authForms = document.querySelectorAll('.auth-form');

    authTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            authTabs.forEach(t => t.classList.remove('active'));
            authForms.forEach(f => f.classList.remove('active'));
            tab.classList.add('active');
            const target = document.getElementById(tab.dataset.tab);
            if (target) target.classList.add('active');
        });
    });

    // ===================================
    // PROFILE NAV (Company Profile)
    // ===================================
    const profileNavLinks = document.querySelectorAll('.profile-nav-link');

    profileNavLinks.forEach(link => {
        link.addEventListener('click', () => {
            profileNavLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });

    // ===================================
    // RANGE SLIDER
    // ===================================
    const rangeSlider = document.querySelector('.range-slider');
    if (rangeSlider) {
        const isRtl = document.documentElement.dir === 'rtl';
        const currency = rangeSlider.dataset.currency || '';
        const paintRangeSlider = function () {
            const val = rangeSlider.value;
            const max = rangeSlider.max || 1000;
            const pct = (val / max) * 100;
            const fillDirection = isRtl ? 'to left' : 'to right';
            rangeSlider.style.background = `linear-gradient(${fillDirection}, var(--primary) 0%, var(--primary) ${pct}%, var(--border) ${pct}%)`;
            const display = document.querySelector('.price-max-display');
            if (display) display.textContent = currency ? `${val} ${currency}` : val;
        };
        rangeSlider.addEventListener('input', paintRangeSlider);
        paintRangeSlider();
    }

    // ===================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ===================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ===================================
    // FORM VALIDATION
    // ===================================
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            let valid = true;
            const inputs = form.querySelectorAll('[required]');
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.style.borderColor = 'var(--primary)';
                } else {
                    input.style.borderColor = '';
                }
            });
            if (!valid) {
                e.preventDefault();
            }
        });
    });

    // Note: cart-count updates are handled by cart.js after the server
    // confirms the item was added (see Cart.add / Cart.updateBadge) so we
    // don't double-increment the badge here.

    // Note: language switching is a real server-side locale + redirect
    // (see routes `lang.switch` and partials.header) rather than a
    // client-side Google Translate toggle.

    // ===================================
    // CMS PAGE CONTENT ENHANCER
    // ===================================
    // Imported/CMS page content (see pages/page.blade.php) is stored as a
    // flat run of <img>/<h3>/<p>/<a> tags with no wrapper markup. This groups
    // repeating "image + heading (+ text)" runs into a responsive card grid,
    // and gives a lone embedded <iframe> (e.g. a certificate PDF) a proper
    // document-viewer frame — all without touching the stored HTML.
    document.querySelectorAll('.page-content').forEach(enhancePageContent);

    function enhancePageContent(container) {
        const root = container.querySelector('section') || container;
        const isRtl = document.documentElement.dir === 'rtl';
        enhanceContentCards(root);
        enhanceEmbeddedDocuments(root, isRtl);
    }

    function isCardTrigger(node) {
        if (node.nodeType !== 1) return false;
        if (node.tagName === 'IMG') return true;
        return node.tagName === 'A' && !!node.querySelector('img');
    }

    function enhanceContentCards(root) {
        const allNodes = Array.from(root.childNodes);
        const triggerIndexes = allNodes.reduce((acc, node, idx) => {
            if (isCardTrigger(node)) acc.push(idx);
            return acc;
        }, []);
        if (triggerIndexes.length < 2) return;

        const before = allNodes.slice(0, triggerIndexes[0]);
        const groups = triggerIndexes.map((start, i) => {
            const end = triggerIndexes[i + 1] ?? allNodes.length;
            return allNodes.slice(start, end);
        });

        const frag = document.createDocumentFragment();
        before.forEach(node => {
            if (node.nodeType === 1 && node.tagName === 'P' && !node.textContent.trim()) {
                return; // drop empty <p></p> artifacts left over from import cleanup
            }
            if (node.nodeType === 1 && node.tagName === 'P' && node.textContent.trim()) {
                node.classList.add('page-lead');
            } else if (node.nodeType === 1 && /^H[2-4]$/.test(node.tagName)) {
                node.classList.add('page-section-title');
            }
            frag.appendChild(node);
        });

        const grid = document.createElement('div');
        grid.className = 'content-cards-grid';
        groups.forEach(group => {
            const trigger = group[0];
            const card = document.createElement('div');
            card.className = trigger.tagName === 'A' ? 'content-card content-card--link' : 'content-card content-card--icon';
            if (trigger.tagName === 'A' && /youtube\.com|youtu\.be/.test(trigger.href || '')) {
                card.classList.add('content-card--video');
                const play = document.createElement('i');
                play.className = 'bi bi-play-circle-fill content-card-play';
                trigger.appendChild(play);
                trigger.style.position = 'relative';
            }
            group.forEach(node => card.appendChild(node));
            grid.appendChild(card);
        });
        frag.appendChild(grid);

        root.innerHTML = '';
        root.appendChild(frag);
    }

    function enhanceEmbeddedDocuments(root, isRtl) {
        root.querySelectorAll('iframe').forEach(iframe => {
            if (iframe.closest('.document-viewer')) return;
            const wrapper = document.createElement('div');
            wrapper.className = 'document-viewer';

            const bar = document.createElement('div');
            bar.className = 'document-viewer-bar';

            const label = document.createElement('span');
            label.innerHTML = `<i class="bi bi-file-earmark-pdf-fill"></i> ${isRtl ? 'مستند PDF' : 'PDF Document'}`;

            const link = document.createElement('a');
            link.className = 'document-viewer-link';
            link.href = (iframe.getAttribute('src') || '').split('#')[0];
            link.target = '_blank';
            link.rel = 'noopener';
            link.innerHTML = `<i class="bi bi-box-arrow-up-right"></i> ${isRtl ? 'فتح في نافذة جديدة' : 'Open in new tab'}`;

            bar.appendChild(label);
            bar.appendChild(link);
            iframe.parentNode.insertBefore(wrapper, iframe);
            wrapper.appendChild(bar);
            wrapper.appendChild(iframe);
        });
    }

    console.log('✅ ALKOBLAN Thermopipe - JavaScript Initialized');
});

