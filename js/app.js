/**
 * Alamrani Real Estate - Main JavaScript Application
 * Handles all frontend interactions, API calls, and UI enhancements
 */

// Global App Object
const AlamraniApp = {
    // Configuration
    config: {
        apiUrl: '/alamrani/api.php',
        mapTileUrl: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        mapAttribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        defaultMapCenter: [15.3694, 44.1910], // Sana'a coordinates
        defaultMapZoom: 12,
        animationDuration: 300,
        debounceDelay: 500
    },

    // State management
    state: {
        currentLanguage: document.documentElement.lang || 'ar',
        isRTL: document.documentElement.dir === 'rtl',
        currentPage: 1,
        searchFilters: {},
        favoriteProperties: new Set(),
        mapInstance: null,
        propertyMarkers: []
    },

    // Initialize the application
    init() {
        this.bindEvents();
        this.initializeComponents();
        this.loadUserPreferences();
        console.log('Alamrani Real Estate App initialized');
    },

    // Bind all event listeners
    bindEvents() {
        // Mobile menu toggle
        this.bindMobileMenu();
        
        // Language switcher
        this.bindLanguageSwitcher();
        
        // Header scroll effects
        this.bindHeaderScroll();
        
        // Search functionality
        this.bindSearchEvents();
        
        // Property interactions
        this.bindPropertyEvents();
        
        // Form handling
        this.bindFormEvents();
        
        // Lazy loading
        this.bindLazyLoading();
        
        // Keyboard navigation
        this.bindKeyboardEvents();
    },

    // Initialize components
    initializeComponents() {
        this.initializeCarousels();
        this.initializeModals();
        this.initializeTooltips();
        this.initializeCounters();
        this.initializeScrollAnimations();
    },

    // Mobile menu functionality
    bindMobileMenu() {
        const menuToggle = document.getElementById('menuToggle');
        const mobileNav = document.getElementById('mobileNav');
        
        if (menuToggle && mobileNav) {
            menuToggle.addEventListener('click', (e) => {
                e.preventDefault();
                menuToggle.classList.toggle('active');
                mobileNav.classList.toggle('active');
                
                // Prevent body scroll when menu is open
                document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!menuToggle.contains(e.target) && !mobileNav.contains(e.target)) {
                    menuToggle.classList.remove('active');
                    mobileNav.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Close menu when clicking on a link
            mobileNav.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    menuToggle.classList.remove('active');
                    mobileNav.classList.remove('active');
                    document.body.style.overflow = '';
                });
            });
        }
    },

    // Language switcher
    bindLanguageSwitcher() {
        const languageSelect = document.getElementById('languageSelect');
        
        if (languageSelect) {
            languageSelect.addEventListener('change', (e) => {
                this.changeLanguage(e.target.value);
            });
        }

        // Global language change function
        window.changeLanguage = (lang) => {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('lang', lang);
            window.location.href = currentUrl.toString();
        };
    },

    // Header scroll effects
    bindHeaderScroll() {
        const header = document.querySelector('.header');
        let lastScrollTop = 0;
        
        if (header) {
            window.addEventListener('scroll', this.throttle(() => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Add scrolled class for styling
                if (scrollTop > 100) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                
                // Hide/show header on scroll (optional)
                if (scrollTop > lastScrollTop && scrollTop > 200) {
                    header.style.transform = 'translateY(-100%)';
                } else {
                    header.style.transform = 'translateY(0)';
                }
                
                lastScrollTop = scrollTop;
            }, 100));
        }
    },

    // Search functionality
    bindSearchEvents() {
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchInput');
        const searchFilters = document.querySelectorAll('.search-filter');
        
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.performSearch();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.showSearchSuggestions(e.target.value);
            }, this.config.debounceDelay));
        }

        // Filter change handlers
        searchFilters.forEach(filter => {
            filter.addEventListener('change', () => {
                this.updateSearchFilters();
            });
        });

        // Advanced search toggle
        const advancedToggle = document.getElementById('advancedSearchToggle');
        const advancedPanel = document.getElementById('advancedSearchPanel');
        
        if (advancedToggle && advancedPanel) {
            advancedToggle.addEventListener('click', () => {
                advancedPanel.classList.toggle('active');
                advancedToggle.classList.toggle('active');
            });
        }
    },

    // Property interactions
    bindPropertyEvents() {
        // Favorite buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.favorite-btn') || e.target.closest('.favorite-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.favorite-btn');
                const propertyId = btn.dataset.propertyId;
                this.toggleFavorite(propertyId, btn);
            }
        });

        // Property card hover effects
        document.querySelectorAll('.property-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                this.animateCard(card, 'enter');
            });
            
            card.addEventListener('mouseleave', () => {
                this.animateCard(card, 'leave');
            });
        });

        // Property quick view
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quick-view-btn') || e.target.closest('.quick-view-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.quick-view-btn');
                const propertyId = btn.dataset.propertyId;
                this.showQuickView(propertyId);
            }
        });
    },

    // Form handling
    bindFormEvents() {
        // Contact forms
        document.querySelectorAll('.contact-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleContactForm(form);
            });
        });

        // Inquiry forms
        document.querySelectorAll('.inquiry-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleInquiryForm(form);
            });
        });

        // File upload handling
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFileUpload(e.target);
            });
        });

        // Real-time validation
        document.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field);
            });
        });
    },

    // Lazy loading for images
    bindLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    },

    // Keyboard navigation
    bindKeyboardEvents() {
        document.addEventListener('keydown', (e) => {
            // Escape key closes modals and menus
            if (e.key === 'Escape') {
                this.closeModals();
                this.closeMobileMenu();
            }
            
            // Enter key activates buttons
            if (e.key === 'Enter' && e.target.matches('.btn, .property-card')) {
                e.target.click();
            }
        });
    },

    // Search functionality
    async performSearch() {
        const searchData = this.collectSearchData();
        this.showLoader();
        
        try {
            const response = await this.apiCall('GET', '/properties', searchData);
            this.displaySearchResults(response.data);
            this.updateUrl(searchData);
        } catch (error) {
            this.showError('Search failed. Please try again.');
        } finally {
            this.hideLoader();
        }
    },

    // Collect search form data
    collectSearchData() {
        const form = document.getElementById('searchForm');
        if (!form) return {};

        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (value.trim()) {
                data[key] = value;
            }
        }
        
        return data;
    },

    // Show search suggestions
    async showSearchSuggestions(query) {
        if (query.length < 2) {
            this.hideSuggestions();
            return;
        }

        try {
            const response = await this.apiCall('GET', '/search/suggestions', { q: query });
            this.displaySuggestions(response.data);
        } catch (error) {
            console.error('Failed to load suggestions:', error);
        }
    },

    // Display search suggestions
    displaySuggestions(suggestions) {
        const container = document.getElementById('searchSuggestions');
        if (!container) return;

        container.innerHTML = '';
        
        if (suggestions.length === 0) {
            container.style.display = 'none';
            return;
        }

        suggestions.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.textContent = suggestion.title;
            item.addEventListener('click', () => {
                document.getElementById('searchInput').value = suggestion.title;
                this.hideSuggestions();
                this.performSearch();
            });
            container.appendChild(item);
        });

        container.style.display = 'block';
    },

    // Hide search suggestions
    hideSuggestions() {
        const container = document.getElementById('searchSuggestions');
        if (container) {
            container.style.display = 'none';
        }
    },

    // Toggle property favorite
    async toggleFavorite(propertyId, btn) {
        if (!this.isUserLoggedIn()) {
            this.showLoginPrompt();
            return;
        }

        const isFavorite = this.state.favoriteProperties.has(propertyId);
        
        try {
            if (isFavorite) {
                await this.apiCall('DELETE', `/favorites/${propertyId}`);
                this.state.favoriteProperties.delete(propertyId);
                btn.classList.remove('active');
            } else {
                await this.apiCall('POST', '/favorites', { property_id: propertyId });
                this.state.favoriteProperties.add(propertyId);
                btn.classList.add('active');
            }
            
            this.showToast(isFavorite ? 'Removed from favorites' : 'Added to favorites');
        } catch (error) {
            this.showError('Failed to update favorites');
        }
    },

    // Property card animations
    animateCard(card, action) {
        const image = card.querySelector('.property-image img');
        const actions = card.querySelector('.property-actions');
        
        if (action === 'enter') {
            if (image) image.style.transform = 'scale(1.05)';
            if (actions) actions.style.opacity = '1';
        } else {
            if (image) image.style.transform = 'scale(1)';
            if (actions) actions.style.opacity = '0';
        }
    },

    // Show property quick view modal
    async showQuickView(propertyId) {
        this.showLoader();
        
        try {
            const response = await this.apiCall('GET', `/properties/${propertyId}`);
            this.displayQuickViewModal(response.data);
        } catch (error) {
            this.showError('Failed to load property details');
        } finally {
            this.hideLoader();
        }
    },

    // Handle contact form submission
    async handleContactForm(form) {
        if (!this.validateForm(form)) return;

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        this.setButtonLoading(submitBtn, true);
        
        try {
            const response = await this.apiCall('POST', '/contact', formData);
            this.showSuccess('Message sent successfully!');
            form.reset();
        } catch (error) {
            this.showError('Failed to send message. Please try again.');
        } finally {
            this.setButtonLoading(submitBtn, false);
        }
    },

    // Handle inquiry form submission
    async handleInquiryForm(form) {
        if (!this.validateForm(form)) return;

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        this.setButtonLoading(submitBtn, true);
        
        try {
            const response = await this.apiCall('POST', '/inquiries', formData);
            this.showSuccess('Inquiry sent successfully!');
            form.reset();
        } catch (error) {
            this.showError('Failed to send inquiry. Please try again.');
        } finally {
            this.setButtonLoading(submitBtn, false);
        }
    },

    // Form validation
    validateForm(form) {
        let isValid = true;
        const fields = form.querySelectorAll('input, textarea, select');
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    },

    // Validate individual field
    validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        const required = field.hasAttribute('required');
        let isValid = true;
        let message = '';

        // Clear previous validation
        field.classList.remove('is-valid', 'is-invalid');
        
        // Required field validation
        if (required && !value) {
            isValid = false;
            message = this.getValidationMessage('required');
        }
        // Email validation
        else if (type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            message = this.getValidationMessage('email');
        }
        // Phone validation
        else if (type === 'tel' && value && !this.isValidPhone(value)) {
            isValid = false;
            message = this.getValidationMessage('phone');
        }
        // Minimum length validation
        else if (field.hasAttribute('minlength') && value.length < field.getAttribute('minlength')) {
            isValid = false;
            message = this.getValidationMessage('minlength', field.getAttribute('minlength'));
        }

        // Apply validation classes and messages
        field.classList.add(isValid ? 'is-valid' : 'is-invalid');
        this.showFieldValidation(field, isValid, message);
        
        return isValid;
    },

    // Show field validation message
    showFieldValidation(field, isValid, message) {
        let feedback = field.parentNode.querySelector('.invalid-feedback, .valid-feedback');
        
        if (feedback) {
            feedback.remove();
        }
        
        if (!isValid && message) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = message;
            field.parentNode.appendChild(feedback);
        }
    },

    // Initialize image carousels
    initializeCarousels() {
        document.querySelectorAll('.property-carousel').forEach(carousel => {
            this.createCarousel(carousel);
        });
    },

    // Create carousel instance
    createCarousel(element) {
        const slides = element.querySelectorAll('.carousel-slide');
        const prevBtn = element.querySelector('.carousel-prev');
        const nextBtn = element.querySelector('.carousel-next');
        const indicators = element.querySelector('.carousel-indicators');
        
        let currentSlide = 0;
        
        // Create indicators
        if (indicators && slides.length > 1) {
            slides.forEach((_, index) => {
                const indicator = document.createElement('button');
                indicator.className = `carousel-indicator ${index === 0 ? 'active' : ''}`;
                indicator.addEventListener('click', () => goToSlide(index));
                indicators.appendChild(indicator);
            });
        }
        
        const goToSlide = (index) => {
            slides[currentSlide].classList.remove('active');
            indicators?.children[currentSlide]?.classList.remove('active');
            
            currentSlide = index;
            
            slides[currentSlide].classList.add('active');
            indicators?.children[currentSlide]?.classList.add('active');
        };
        
        const nextSlide = () => {
            goToSlide((currentSlide + 1) % slides.length);
        };
        
        const prevSlide = () => {
            goToSlide((currentSlide - 1 + slides.length) % slides.length);
        };
        
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        
        // Auto-play (optional)
        if (element.dataset.autoplay) {
            setInterval(nextSlide, parseInt(element.dataset.autoplay) || 5000);
        }
        
        // Touch/swipe support
        this.addSwipeSupport(element, { onSwipeLeft: nextSlide, onSwipeRight: prevSlide });
    },

    // Add swipe support for touch devices
    addSwipeSupport(element, callbacks) {
        let startX = 0;
        let startY = 0;
        
        element.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        });
        
        element.addEventListener('touchend', (e) => {
            if (!startX || !startY) return;
            
            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;
            
            const diffX = startX - endX;
            const diffY = startY - endY;
            
            // Determine swipe direction
            if (Math.abs(diffX) > Math.abs(diffY)) {
                if (Math.abs(diffX) > 50) { // Minimum swipe distance
                    if (diffX > 0 && callbacks.onSwipeLeft) {
                        callbacks.onSwipeLeft();
                    } else if (diffX < 0 && callbacks.onSwipeRight) {
                        callbacks.onSwipeRight();
                    }
                }
            }
            
            startX = 0;
            startY = 0;
        });
    },

    // Initialize modals
    initializeModals() {
        document.querySelectorAll('[data-modal-target]').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = trigger.dataset.modalTarget;
                this.openModal(modalId);
            });
        });

        document.querySelectorAll('.modal-close, .modal-backdrop').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                this.closeModals();
            });
        });
    },

    // Open modal
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Focus management
            const firstFocusable = modal.querySelector('button, input, select, textarea, a[href]');
            if (firstFocusable) firstFocusable.focus();
        }
    },

    // Close all modals
    closeModals() {
        document.querySelectorAll('.modal.active').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    },

    // Initialize tooltips
    initializeTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            this.createTooltip(element);
        });
    },

    // Create tooltip
    createTooltip(element) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = element.dataset.tooltip;
        
        element.addEventListener('mouseenter', () => {
            document.body.appendChild(tooltip);
            this.positionTooltip(element, tooltip);
            tooltip.classList.add('visible');
        });
        
        element.addEventListener('mouseleave', () => {
            tooltip.remove();
        });
    },

    // Position tooltip
    positionTooltip(element, tooltip) {
        const rect = element.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        let left = rect.left + (rect.width - tooltipRect.width) / 2;
        let top = rect.top - tooltipRect.height - 10;
        
        // Adjust for screen boundaries
        if (left < 10) left = 10;
        if (left + tooltipRect.width > window.innerWidth - 10) {
            left = window.innerWidth - tooltipRect.width - 10;
        }
        if (top < 10) {
            top = rect.bottom + 10;
        }
        
        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${top}px`;
    },

    // Initialize counters
    initializeCounters() {
        const counters = document.querySelectorAll('.counter');
        
        if ('IntersectionObserver' in window) {
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.animateCounter(entry.target);
                        counterObserver.unobserve(entry.target);
                    }
                });
            });
            
            counters.forEach(counter => counterObserver.observe(counter));
        }
    },

    // Animate counter
    animateCounter(element) {
        const target = parseInt(element.dataset.count);
        const duration = 2000;
        const start = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            const current = Math.floor(progress * target);
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    },

    // Initialize scroll animations
    initializeScrollAnimations() {
        const animatedElements = document.querySelectorAll('.fade-in, .slide-up');
        
        if ('IntersectionObserver' in window) {
            const animationObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        animationObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            animatedElements.forEach(element => animationObserver.observe(element));
        }
    },

    // API call wrapper
    async apiCall(method, endpoint, data = null) {
        const url = this.config.apiUrl + endpoint;
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data && method !== 'GET') {
            if (data instanceof FormData) {
                delete options.headers['Content-Type'];
                options.body = data;
            } else {
                options.body = JSON.stringify(data);
            }
        }

        if (data && method === 'GET') {
            const params = new URLSearchParams(data);
            const separator = url.includes('?') ? '&' : '?';
            url += separator + params.toString();
        }

        const response = await fetch(url, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error?.message || 'Request failed');
        }

        return result;
    },

    // Utility functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    throttle(func, wait) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, wait);
            }
        };
    },

    // Validation helpers
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    isValidPhone(phone) {
        const re = /^[\+]?[0-9\-\s\(\)]{7,15}$/;
        return re.test(phone);
    },

    // Get validation message based on current language
    getValidationMessage(type, param = null) {
        const messages = {
            ar: {
                required: 'هذا الحقل مطلوب',
                email: 'يجب أن يكون بريد إلكتروني صحيح',
                phone: 'رقم هاتف غير صحيح',
                minlength: `يجب أن يكون على الأقل ${param} أحرف`
            },
            en: {
                required: 'This field is required',
                email: 'Must be a valid email address',
                phone: 'Invalid phone number',
                minlength: `Must be at least ${param} characters`
            }
        };
        
        return messages[this.state.currentLanguage]?.[type] || messages.en[type];
    },

    // UI feedback methods
    showLoader() {
        const loader = document.getElementById('loader') || this.createLoader();
        loader.style.display = 'flex';
    },

    hideLoader() {
        const loader = document.getElementById('loader');
        if (loader) loader.style.display = 'none';
    },

    createLoader() {
        const loader = document.createElement('div');
        loader.id = 'loader';
        loader.className = 'loader';
        loader.innerHTML = '<div class="loader-spinner"></div>';
        document.body.appendChild(loader);
        return loader;
    },

    setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.classList.add('loading');
            button.dataset.originalText = button.textContent;
            button.textContent = this.state.currentLanguage === 'ar' ? 'جاري الإرسال...' : 'Sending...';
        } else {
            button.disabled = false;
            button.classList.remove('loading');
            button.textContent = button.dataset.originalText || button.textContent;
        }
    },

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Remove after delay
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    showSuccess(message) {
        this.showToast(message, 'success');
    },

    showError(message) {
        this.showToast(message, 'error');
    },

    // User authentication helpers
    isUserLoggedIn() {
        return document.body.dataset.userLoggedIn === 'true';
    },

    showLoginPrompt() {
        const message = this.state.currentLanguage === 'ar' 
            ? 'يجب تسجيل الدخول أولاً' 
            : 'Please login first';
        this.showError(message);
        
        setTimeout(() => {
            window.location.href = '/alamrani/login.php';
        }, 2000);
    },

    // Load user preferences
    loadUserPreferences() {
        // Load favorites
        const favorites = localStorage.getItem('alamrani_favorites');
        if (favorites) {
            this.state.favoriteProperties = new Set(JSON.parse(favorites));
        }
        
        // Update favorite buttons
        this.updateFavoriteButtons();
    },

    // Update favorite button states
    updateFavoriteButtons() {
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            const propertyId = btn.dataset.propertyId;
            if (this.state.favoriteProperties.has(propertyId)) {
                btn.classList.add('active');
            }
        });
    },

    // Save user preferences
    saveUserPreferences() {
        localStorage.setItem('alamrani_favorites', JSON.stringify([...this.state.favoriteProperties]));
    },

    // Update URL with search parameters
    updateUrl(params) {
        const url = new URL(window.location);
        Object.keys(params).forEach(key => {
            if (params[key]) {
                url.searchParams.set(key, params[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        
        window.history.pushState({}, '', url);
    },

    // Close mobile menu
    closeMobileMenu() {
        const menuToggle = document.getElementById('menuToggle');
        const mobileNav = document.getElementById('mobileNav');
        
        if (menuToggle && mobileNav) {
            menuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
};

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    AlamraniApp.init();
});

// Save preferences before page unload
window.addEventListener('beforeunload', () => {
    AlamraniApp.saveUserPreferences();
});

// Export for global access
window.AlamraniApp = AlamraniApp;