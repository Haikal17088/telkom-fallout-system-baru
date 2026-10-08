/*
 * Telkom Fallout System - soft navigation helper
 *
 * Rules:
 * - GET links/forms can navigate without a full document reload.
 * - POST/PUT/DELETE, uploads and downloads remain normal browser requests.
 * - Dashboard replaces only #contentShell .content.
 * - Public pages replace head/body while keeping this helper alive.
 */
(function () {
    'use strict';

    if (window.__TFNavigationInstalled) {
        return;
    }

    window.__TFNavigationInstalled = true;

    var activeRequest = null;
    var loadedExternalScripts = Object.create(null);

    function currentMode() {
        return document.body
            ? (document.body.getAttribute('data-tf-nav') || 'public')
            : 'public';
    }

    function isSameOrigin(url) {
        return url.origin === window.location.origin;
    }

    function shouldIgnoreAnchor(anchor, url) {
        if (!anchor || !url || !isSameOrigin(url)) {
            return true;
        }

        if (
            anchor.target === '_blank' ||
            anchor.hasAttribute('download') ||
            anchor.dataset.noSpa !== undefined ||
            anchor.rel === 'external'
        ) {
            return true;
        }

        if (url.protocol !== 'http:' && url.protocol !== 'https:') {
            return true;
        }

        if (url.hash && url.pathname === window.location.pathname && url.search === window.location.search) {
            return true;
        }

        /* File downloads must remain native browser navigation. */
        if (url.pathname.indexOf('/download') !== -1 || url.pathname.indexOf('-download') !== -1) {
            return true;
        }

        return false;
    }

    function shouldIgnoreForm(form) {
        if (!form) {
            return true;
        }

        var method = (form.getAttribute('method') || 'GET').toUpperCase();

        if (method !== 'GET') {
            return true;
        }

        if (form.target === '_blank' || form.dataset.noSpa !== undefined) {
            return true;
        }

        var action = form.getAttribute('action') || window.location.href;
        var url = new URL(action, window.location.href);

        if (!isSameOrigin(url)) {
            return true;
        }

        if (url.pathname.indexOf('/download') !== -1 || url.pathname.indexOf('-download') !== -1) {
            return true;
        }

        return false;
    }

    function ensureLoader() {
        var loader = document.getElementById('tfNavigationLoader');
        var style = document.getElementById('tfNavigationLoaderStyle');

        if (!style) {
            style = document.createElement('style');
            style.id = 'tfNavigationLoaderStyle';
            style.textContent =
                '#tfNavigationLoader{' +
                    'position:fixed;inset:0;z-index:2147483646;' +
                    'display:flex;align-items:center;justify-content:center;' +
                    'padding:16px;background:rgba(247,243,241,.94);' +
                    'backdrop-filter:blur(7px);-webkit-backdrop-filter:blur(7px);' +
                    'opacity:0;visibility:hidden;pointer-events:none;' +
                    'transition:opacity .18s ease,visibility .18s ease;' +
                '}' +
                '#tfNavigationLoader.is-visible{' +
                    'opacity:1;visibility:visible;pointer-events:auto;' +
                '}' +
                '.tf-navigation-loader-card{' +
                    'width:min(320px,calc(100vw - 32px));' +
                    'padding:26px 24px 23px;background:#fff;border-radius:20px;' +
                    'box-shadow:0 30px 80px -28px rgba(58,4,16,.30);' +
                    'text-align:center;border:1px solid rgba(200,16,46,.08);' +
                '}' +
                '.tf-navigation-loader-logo{' +
                    'width:50px;height:50px;margin:0 auto 13px;' +
                    'display:grid;place-items:center;border-radius:14px;' +
                    'background:linear-gradient(135deg,#C8102E,#8A0F26);' +
                    'color:#fff;font:800 16px/1 "Space Grotesk",sans-serif;' +
                    'box-shadow:0 12px 25px rgba(200,16,46,.20);' +
                '}' +
                '.tf-navigation-loader-spinner{' +
                    'width:30px;height:30px;margin:0 auto 14px;border-radius:50%;' +
                    'border:3px solid rgba(200,16,46,.12);border-top-color:#C8102E;' +
                    'animation:tfNavigationLoaderSpin .72s linear infinite;' +
                '}' +
                '.tf-navigation-loader-title{' +
                    'font:700 15px/1.25 "Space Grotesk",sans-serif;color:#20161A;' +
                '}' +
                '.tf-navigation-loader-subtitle{' +
                    'margin-top:5px;font:11.5px/1.5 "Inter",sans-serif;color:#7A6B6F;' +
                '}' +
                '@keyframes tfNavigationLoaderSpin{to{transform:rotate(360deg)}}';
            document.head.appendChild(style);
        }

        if (loader) {
            return loader;
        }

        loader = document.createElement('div');
        loader.id = 'tfNavigationLoader';
        loader.setAttribute('aria-live', 'polite');
        loader.innerHTML =
            '<div class="tf-navigation-loader-card">' +
                '<div class="tf-navigation-loader-logo">TF</div>' +
                '<div class="tf-navigation-loader-spinner" aria-hidden="true"></div>' +
                '<div class="tf-navigation-loader-title">Telkom Fallout System</div>' +
                '<div class="tf-navigation-loader-subtitle">Memuat halaman...</div>' +
            '</div>';

        document.body.appendChild(loader);
        return loader;
    }

    function showLoader(textValue) {
        var loader = ensureLoader();
        var subtitle = loader.querySelector('.tf-navigation-loader-subtitle');

        if (subtitle) {
            subtitle.textContent = textValue || 'Memuat halaman...';
        }

        loader.classList.add('is-visible');
    }

    function hideLoader() {
        var loader = document.getElementById('tfNavigationLoader');
        if (loader) {
            loader.classList.remove('is-visible');
        }
    }

    function copyBodyAttributes(fromBody, toBody) {
        if (!fromBody || !toBody) {
            return;
        }

        Array.from(toBody.attributes).forEach(function (attr) {
            toBody.removeAttribute(attr.name);
        });

        Array.from(fromBody.attributes).forEach(function (attr) {
            toBody.setAttribute(attr.name, attr.value);
        });
    }

    function cleanupDashboardTransientNodes() {
        var keep = [
            '#sidebarBackdrop',
            '#sidebar',
            '.sidebar-close',
            '.main',
            '#logoutLoading',
            '#tfNavigationLoader'
        ];

        Array.from(document.body.children).forEach(function (child) {
            var shouldKeep = keep.some(function (selector) {
                return child.matches(selector);
            });

            if (!shouldKeep) {
                child.remove();
            }
        });
    }

    function closeDashboardMobileSidebar() {
        if (typeof window.closeMobileSidebar === 'function') {
            try {
                window.closeMobileSidebar();
            } catch (e) {}
        }
    }

    function updateDashboardActiveState(url) {
        var pathname = url.pathname.replace(/\/+$/, '') || '/';

        document.querySelectorAll('.nav-item[href]').forEach(function (item) {
            var itemUrl;
            try {
                itemUrl = new URL(item.href, window.location.href);
            } catch (e) {
                return;
            }

            var itemPath = itemUrl.pathname.replace(/\/+$/, '') || '/';
            item.classList.toggle('active', itemPath === pathname);
        });

        document.querySelectorAll('.witel-branch').forEach(function (branch) {
            branch.classList.remove('open');
        });

        document.querySelectorAll('.witel-branch').forEach(function (branch) {
            var hasCurrent = false;

            branch.querySelectorAll('.nav-item[href]').forEach(function (item) {
                try {
                    var itemUrl = new URL(item.href, window.location.href);
                    var itemPath = itemUrl.pathname.replace(/\/+$/, '') || '/';
                    if (itemPath === pathname) {
                        hasCurrent = true;
                    }
                } catch (e) {}
            });

            if (hasCurrent) {
                branch.classList.add('open');
            }
        });

        var adminLink = document.querySelector('.admin-nav-group .nav-item[href]');
        if (adminLink) {
            adminLink.classList.toggle('active', pathname.indexOf('/admin/users') === 0);
        }
    }

    function runInlineScript(source) {
        var originalDocumentAdd = document.addEventListener;
        var originalWindowAdd = window.addEventListener;

        document.addEventListener = function (type, listener, options) {
            if (type === 'DOMContentLoaded' && typeof listener === 'function') {
                try {
                    listener(new Event('DOMContentLoaded'));
                } catch (e) {}
                return;
            }

            return originalDocumentAdd.call(document, type, listener, options);
        };

        window.addEventListener = function (type, listener, options) {
            if (type === 'load' && typeof listener === 'function') {
                try {
                    listener(new Event('load'));
                } catch (e) {}
                return;
            }

            return originalWindowAdd.call(window, type, listener, options);
        };

        try {
            new Function(source)();
        } finally {
            document.addEventListener = originalDocumentAdd;
            window.addEventListener = originalWindowAdd;
        }
    }

    function loadExternalScript(src) {
        var absolute = new URL(src, window.location.href).href;

        if (loadedExternalScripts[absolute]) {
            return loadedExternalScripts[absolute];
        }

        loadedExternalScripts[absolute] = new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            script.src = absolute;
            script.async = false;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });

        return loadedExternalScripts[absolute];
    }

    async function runScripts(root) {
        if (!root) {
            return;
        }

        var scripts = Array.from(root.querySelectorAll('script'));

        for (var i = 0; i < scripts.length; i++) {
            var oldScript = scripts[i];

            if (oldScript.src) {
                try {
                    await loadExternalScript(oldScript.src);
                } catch (e) {
                    // Keep navigation usable even when an optional CDN script fails.
                }
                continue;
            }

            var source = oldScript.textContent || '';
            if (source.trim() !== '') {
                try {
                    runInlineScript(source);
                } catch (e) {
                    // One optional page script should not break navigation.
                }
            }
        }
    }

    function removeInitialPageLoaders(root) {
        if (!root) {
            return;
        }

        var selectors = [
            '#pageLoading',
            '#trPageLoading',
            '#dfPageLoading',
            '#udPageLoading',
            '#edPageLoading',
            '#ahPageLoading',
            '#exPageLoading'
        ];

        selectors.forEach(function (selector) {
            root.querySelectorAll(selector).forEach(function (el) {
                el.remove();
            });
        });
    }

    async function fetchHtml(url, mode) {
        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();

        try {
            var response = await fetch(url.href, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'X-TF-SPA': '1',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml'
                },
                signal: activeRequest.signal
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            var contentType = response.headers.get('content-type') || '';
            if (contentType.indexOf('text/html') === -1) {
                throw new Error('Bukan respons HTML');
            }

            var html = await response.text();
            var parser = new DOMParser();
            return parser.parseFromString(html, 'text/html');
        } finally {
            activeRequest = null;
        }
    }

    async function navigateDashboard(url, options) {
        options = options || {};
        try {
            var nextDoc = await fetchHtml(url, 'dashboard');
            var nextContent = nextDoc.querySelector('#contentShell .content');

            /* Standalone admin create/edit pages fall back to full navigation. */
            if (!nextContent) {
                window.location.assign(url.href);
                return;
            }

            var currentContent = document.querySelector('#contentShell .content');

            if (!currentContent) {
                window.location.assign(url.href);
                return;
            }

            cleanupDashboardTransientNodes();
            currentContent.innerHTML = nextContent.innerHTML;
            currentContent.className = nextContent.className;

            document.title = nextDoc.title || document.title;
            document.body.setAttribute('data-tf-nav', 'dashboard');

            await runScripts(currentContent);

            if (options.push !== false) {
                window.history.pushState({}, '', url.href);
            }

            updateDashboardActiveState(url);
            closeDashboardMobileSidebar();
            window.scrollTo(0, 0);
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }
            window.location.assign(url.href);
        } finally {
            hideLoader();
        }
    }

    async function navigatePublic(url, options) {
        options = options || {};
        showLoader('Memuat halaman...');

        try {
            var nextDoc = await fetchHtml(url, 'public');

            copyBodyAttributes(nextDoc.body, document.body);
            document.title = nextDoc.title || document.title;
            document.head.innerHTML = nextDoc.head.innerHTML;
            document.body.innerHTML = nextDoc.body.innerHTML;

            var root = document.body;
            await runScripts(root);

            /* The rekap page has its own loader; don't stack loaders. */
            var rekapLoader = document.getElementById('rekapPageLoader');
            if (rekapLoader) {
                rekapLoader.remove();
            }

            if (options.push !== false) {
                window.history.pushState({}, '', url.href);
            }

            window.scrollTo(0, 0);
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }
            window.location.assign(url.href);
        } finally {
            hideLoader();
        }
    }

    async function navigate(url, options) {
        if (!url || !isSameOrigin(url)) {
            return;
        }

        if (currentMode() === 'dashboard') {
            await navigateDashboard(url, options);
        } else {
            await navigatePublic(url, options);
        }
    }

    document.addEventListener('click', function (event) {
        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }

        var anchor = event.target.closest('a[href]');
        if (!anchor) {
            return;
        }

        var url;
        try {
            url = new URL(anchor.href, window.location.href);
        } catch (e) {
            return;
        }

        if (shouldIgnoreAnchor(anchor, url)) {
            return;
        }

        /* Dashboard nav and public GET links are the only links handled here. */
        if (currentMode() === 'dashboard') {
            var path = url.pathname;
            var isDashboardNav =
                path === '/dashboard' ||
                path.indexOf('/dashboard/') === 0 ||
                path.indexOf('/admin/users') === 0;

            if (!isDashboardNav) {
                return;
            }
        }

        event.preventDefault();
        navigate(url, {push: true});
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (currentMode() !== 'public' || shouldIgnoreForm(form)) {
            return;
        }

        var method = (form.getAttribute('method') || 'GET').toUpperCase();
        if (method !== 'GET') {
            return;
        }

        var action = form.getAttribute('action') || window.location.href;
        var url = new URL(action, window.location.href);
        var formData;
        try {
            formData = new FormData(form, event.submitter);
        } catch (e) {
            formData = new FormData(form);
        }

        Array.from(url.searchParams.keys()).forEach(function (key) {
            url.searchParams.delete(key);
        });

        formData.forEach(function (value, key) {
            if (typeof value === 'string') {
                url.searchParams.append(key, value);
            }
        });

        event.preventDefault();
        navigate(url, {push: true});
    });

    window.addEventListener('popstate', function () {
        var url = new URL(window.location.href);
        navigate(url, {push: false});
    });
})();
