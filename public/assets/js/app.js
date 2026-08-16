/* ============================================================
   Nubia Inventory - Core front-end interactions
   ============================================================ */
(function () {
    'use strict';

    const Nubia = window.Nubia || {};

    /* ---------------- Theme (dark / light) ---------------- */
    Nubia.theme = {
        get() { return localStorage.getItem('nubia-theme') || document.documentElement.getAttribute('data-theme') || 'light'; },
        set(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('nubia-theme', theme);
            const icon = document.querySelector('#themeToggle .material-symbols-rounded');
            if (icon) icon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
            // Persist server-side (best effort).
            if (window.NUBIA_BASE) {
                fetch(window.NUBIA_BASE + '/settings/theme', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                    body: 'theme=' + theme + '&_token=' + (window.CSRF_TOKEN || '')
                }).catch(() => {});
            }
        },
        toggle() { this.set(this.get() === 'dark' ? 'light' : 'dark'); }
    };

    /* ---------------- Sidebar ---------------- */
    Nubia.sidebar = {
        isDesktop() {
            return window.matchMedia('(min-width: 992px)').matches;
        },
        applyCollapsed(collapsed) {
            const sidebar = document.querySelector('.sidebar');
            const shell = document.querySelector('.app-shell');
            const btn = document.getElementById('sidebarToggle');
            const icon = btn?.querySelector('.material-symbols-rounded');
            if (!sidebar || !shell) return;

            sidebar.classList.toggle('collapsed', collapsed);
            shell.classList.toggle('sidebar-collapsed', collapsed);
            document.documentElement.classList.toggle('sidebar-collapsed-pref', collapsed);
            localStorage.setItem('nubia-sidebar-collapsed', collapsed ? '1' : '0');

            if (icon) {
                icon.textContent = collapsed ? 'menu' : 'menu_open';
            }
            if (btn) {
                btn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            }
        },
        toggle() {
            if (this.isDesktop()) {
                const isCollapsed = document.querySelector('.sidebar')?.classList.contains('collapsed')
                    || document.documentElement.classList.contains('sidebar-collapsed-pref');
                this.applyCollapsed(!isCollapsed);
            } else {
                document.querySelector('.sidebar')?.classList.toggle('open');
                document.querySelector('.sidebar-overlay')?.classList.toggle('show');
            }
        },
        close() {
            document.querySelector('.sidebar')?.classList.remove('open');
            document.querySelector('.sidebar-overlay')?.classList.remove('show');
        },
        init() {
            const preferCollapsed = localStorage.getItem('nubia-sidebar-collapsed') === '1';
            if (this.isDesktop() && preferCollapsed) {
                this.applyCollapsed(true);
            } else if (!this.isDesktop()) {
                document.documentElement.classList.remove('sidebar-collapsed-pref');
            }
            this.persistScroll();
            // Re-enable transitions after first paint.
            requestAnimationFrame(() => {
                document.documentElement.classList.remove('sidebar-boot');
            });
        },
        /** Keep sidebar scrolled to the last position / active item after navigation. */
        persistScroll() {
            const nav = document.querySelector('.sidebar-nav');
            if (!nav) return;

            const key = 'nubia-sidebar-scroll';
            const saved = sessionStorage.getItem(key);
            if (saved !== null) {
                nav.scrollTop = parseInt(saved, 10) || 0;
            }

            const active = nav.querySelector('.nav-link.active');
            if (active) {
                const navRect = nav.getBoundingClientRect();
                const linkRect = active.getBoundingClientRect();
                const outOfView = linkRect.top < navRect.top || linkRect.bottom > navRect.bottom;
                if (outOfView) {
                    active.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                }
            }

            const save = () => sessionStorage.setItem(key, String(nav.scrollTop));
            nav.addEventListener('scroll', save, { passive: true });
            nav.querySelectorAll('.nav-link').forEach((link) => {
                link.addEventListener('click', save);
            });
        }
    };

    /* ---------------- Toast helper ---------------- */
    Nubia.toast = function (message, type = 'success') {
        if (window.toastr) {
            toastr.options = { positionClass: 'toast-top-right', progressBar: true, timeOut: 3500, closeButton: true };
            toastr[type](message);
        } else {
            alert(message);
        }
    };

    /* ---------------- Confirm dialog (SweetAlert2) ---------------- */
    Nubia.confirm = function (opts) {
        const options = Object.assign({
            title: 'Are you sure?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            confirmText: 'Yes, proceed',
            onConfirm: () => {}
        }, opts);

        if (window.Swal) {
            Swal.fire({
                title: options.title,
                text: options.text,
                icon: options.icon,
                showCancelButton: true,
                confirmButtonText: options.confirmText,
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#94a3b8',
                reverseButtons: true
            }).then((r) => { if (r.isConfirmed) options.onConfirm(); });
        } else if (confirm(options.text)) {
            options.onConfirm();
        }
    };

    /* ---------------- AJAX helper ---------------- */
    Nubia.ajax = async function (url, options = {}) {
        const opts = Object.assign({
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }, options);

        if (opts.body && !(opts.body instanceof FormData) && typeof opts.body === 'object') {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(opts.body);
        }

        const resolved = (url.startsWith('http') || url.startsWith('/')) ? url : (window.NUBIA_BASE + url);
        const res = await fetch(resolved, opts);
        const ct = res.headers.get('content-type') || '';
        const data = ct.includes('application/json') ? await res.json() : await res.text();
        return { ok: res.ok, status: res.status, data };
    };

    Nubia.fmtMoney = function (n) {
        const sym = window.CURRENCY_SYMBOL || 'Tk';
        return sym + ' ' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    /* ---------------- Global search ---------------- */
    function initGlobalSearch() {
        const input = document.getElementById('globalSearch');
        const box = document.getElementById('searchResults');
        if (!input || !box) return;
        let timer = null;

        input.addEventListener('input', function () {
            clearTimeout(timer);
            const q = this.value.trim();
            if (q.length < 2) { box.style.display = 'none'; return; }
            timer = setTimeout(async () => {
                try {
                    const { data } = await Nubia.ajax('/api/search?q=' + encodeURIComponent(q));
                    renderSearch(box, data);
                } catch (e) { /* ignore */ }
            }, 250);
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.global-search')) box.style.display = 'none';
        });
    }

    function renderSearch(box, data) {
        let html = '';
        const groups = data.results || {};
        let count = 0;
        for (const group in groups) {
            if (!groups[group].length) continue;
            html += '<div class="cat">' + group + '</div>';
            groups[group].forEach((item) => {
                count++;
                html += '<a href="' + window.NUBIA_BASE + item.url + '"><strong>' + item.title + '</strong>' +
                    (item.subtitle ? ' <span class="text-muted-2" style="font-size:.8rem">— ' + item.subtitle + '</span>' : '') + '</a>';
            });
        }
        box.innerHTML = count ? html : '<div class="p-3 text-muted-2">No results found.</div>';
        box.style.display = 'block';
    }

    /* ---------------- Number inputs ---------------- */
    /**
     * Browsers step a focused <input type="number"> when the wheel rolls over it,
     * so scrolling the page silently rewrites quantities and prices. Dropping focus
     * before the default action runs keeps the value intact and lets the page scroll.
     */
    function initNumberInputGuard() {
        const isNumberField = (el) => el instanceof HTMLInputElement && el.type === 'number';

        document.addEventListener('wheel', function (e) {
            const el = document.activeElement;
            if (isNumberField(el) && el === e.target && !el.readOnly && !el.disabled) {
                el.blur();
            }
        }, { passive: true, capture: true });
    }

    /* ---------------- Notifications ---------------- */
    function initNotifications() {
        document.getElementById('notifMarkAll')?.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopPropagation();
            try {
                const { ok } = await Nubia.ajax('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: '_token=' + encodeURIComponent(window.CSRF_TOKEN || '')
                });
                if (ok) {
                    const menu = this.closest('.dropdown-menu');
                    const dot = this.closest('.dropdown')?.querySelector('.dot');
                    if (dot) dot.remove();
                    if (menu) {
                        menu.querySelectorAll('.notif-item, #notifMarkAll').forEach((el) => el.remove());
                        const title = menu.querySelector('.topbar-menu-title');
                        if (title) title.innerHTML = '<span>Notifications</span>';
                        if (!menu.querySelector('.notif-empty')) {
                            const empty = document.createElement('div');
                            empty.className = 'px-3 py-3 text-muted-2 notif-empty';
                            empty.style.fontSize = '.85rem';
                            empty.textContent = "You're all caught up.";
                            menu.appendChild(empty);
                        }
                    }
                }
            } catch (err) { /* ignore */ }
        });
    }

    /* ---------------- PWA ---------------- */
    Nubia.pwa = {
        deferredPrompt: null,
        isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches
                || window.navigator.standalone === true
                || document.documentElement.classList.contains('is-pwa');
        },
        register() {
            if (!('serviceWorker' in navigator)) return;
            const swUrl = (window.NUBIA_BASE || '') + '/sw.js';
            navigator.serviceWorker.register(swUrl, { scope: '/' }).then((reg) => {
                reg.addEventListener('updatefound', () => {
                    const worker = reg.installing;
                    if (!worker) return;
                    worker.addEventListener('statechange', () => {
                        if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                            Nubia.pwa.promptUpdate(reg);
                        }
                    });
                });
            }).catch(() => {});

            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (refreshing) return;
                refreshing = true;
                window.location.reload();
            });
        },
        promptUpdate(reg) {
            if (window.Swal) {
                Swal.fire({
                    title: 'Update available',
                    text: 'A newer version of Nubia is ready.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Refresh',
                    cancelButtonText: 'Later',
                    confirmButtonColor: '#dc2626'
                }).then((r) => {
                    if (r.isConfirmed) {
                        reg.waiting?.postMessage({ type: 'SKIP_WAITING' });
                    }
                });
            }
        },
        bindInstall() {
            const btn = document.getElementById('pwaInstallBtn');
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                Nubia.pwa.deferredPrompt = e;
                if (btn && !Nubia.pwa.isStandalone()) {
                    btn.classList.remove('d-none');
                    btn.classList.add('d-grid');
                }
                Nubia.pwa.showBanner();
            });

            window.addEventListener('appinstalled', () => {
                Nubia.pwa.deferredPrompt = null;
                btn?.classList.add('d-none');
                btn?.classList.remove('d-grid');
                document.getElementById('pwaInstallBanner')?.remove();
                Nubia.toast('Nubia installed. Open it from your home screen.', 'success');
            });

            btn?.addEventListener('click', () => Nubia.pwa.install());
        },
        async install() {
            const prompt = Nubia.pwa.deferredPrompt;
            if (!prompt) {
                Nubia.pwa.showIosHint();
                return;
            }
            prompt.prompt();
            await prompt.userChoice;
            Nubia.pwa.deferredPrompt = null;
            document.getElementById('pwaInstallBtn')?.classList.add('d-none');
            document.getElementById('pwaInstallBanner')?.remove();
        },
        showBanner() {
            if (Nubia.pwa.isStandalone()) return;
            if (localStorage.getItem('nubia-pwa-banner-dismissed') === '1') return;
            if (document.getElementById('pwaInstallBanner')) return;
            if (!Nubia.pwa.deferredPrompt && !Nubia.pwa.isIos()) return;

            const bar = document.createElement('div');
            bar.id = 'pwaInstallBanner';
            bar.className = 'pwa-install-banner';
            bar.innerHTML = '<div class="pwa-install-copy"><strong>Install Nubia</strong><span>Use it like an app — faster access on this device.</span></div><div class="pwa-install-actions"><button type="button" class="btn btn-brand btn-sm" id="pwaBannerInstall">Install</button><button type="button" class="btn btn-soft btn-sm" id="pwaBannerDismiss" aria-label="Dismiss">Not now</button></div>';
            document.body.appendChild(bar);
            document.getElementById('pwaBannerInstall')?.addEventListener('click', () => {
                if (Nubia.pwa.deferredPrompt) Nubia.pwa.install();
                else Nubia.pwa.showIosHint();
            });
            document.getElementById('pwaBannerDismiss')?.addEventListener('click', () => {
                localStorage.setItem('nubia-pwa-banner-dismissed', '1');
                bar.remove();
            });
        },
        isIos() {
            return /iphone|ipad|ipod/i.test(navigator.userAgent)
                && !window.MSStream
                && !Nubia.pwa.isStandalone();
        },
        showIosHint() {
            if (!Nubia.pwa.isIos()) return;
            Nubia.toast('On iPhone: tap Share, then “Add to Home Screen”.', 'info');
        }
    };

    /* ---------------- IMEI / serial chip inputs ---------------- */
    /**
     * Turns any <textarea data-imei-chips> into a chip editor. The textarea stays
     * in the DOM (hidden) and keeps holding the newline-separated value, so server
     * side parsing and any existing oninput handlers keep working untouched.
     */
    const imeiSplit = /[\s,;]+/;

    function imeiParse(value) {
        return String(value || '').split(imeiSplit).map((s) => s.trim()).filter(Boolean);
    }

    function imeiEscape(value) {
        return String(value).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[c]);
    }

    function buildImeiChips(source) {
        if (source.dataset.imeiChipsReady === '1') return;
        source.dataset.imeiChipsReady = '1';

        const required = source.hasAttribute('required');
        if (required) source.removeAttribute('required');

        const wrap = document.createElement('div');
        wrap.className = 'imei-chips';

        const field = document.createElement('div');
        field.className = 'imei-chips-field';
        if (source.classList.contains('form-control-sm')) field.classList.add('is-sm');

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'imei-chips-input';
        input.autocomplete = 'off';
        input.spellcheck = false;
        input.placeholder = source.dataset.imeiPlaceholder || 'Scan or type, then press Enter';

        const meta = document.createElement('div');
        meta.className = 'imei-chips-meta';

        source.parentNode.insertBefore(wrap, source);
        wrap.appendChild(field);
        wrap.appendChild(meta);
        wrap.appendChild(source);
        source.classList.add('imei-chips-source');
        field.appendChild(input);

        let codes = imeiParse(source.value);

        const expected = () => {
            const raw = source.dataset.imeiExpected;
            const n = raw === undefined ? NaN : Math.round(parseFloat(raw));
            return Number.isFinite(n) && n > 0 ? n : 0;
        };

        function render() {
            field.querySelectorAll('.imei-chip').forEach((el) => el.remove());
            codes.forEach((code, i) => {
                const chip = document.createElement('span');
                chip.className = 'imei-chip';
                chip.innerHTML = '<span class="material-symbols-rounded">smartphone</span>' +
                    '<span class="imei-chip-code">' + imeiEscape(code) + '</span>' +
                    '<button type="button" class="imei-chip-x" data-index="' + i + '" aria-label="Remove ' + imeiEscape(code) + '">' +
                    '<span class="material-symbols-rounded">close</span></button>';
                field.insertBefore(chip, input);
            });

            const want = expected();
            const unit = codes.length === 1 ? 'unit' : 'units';
            meta.classList.remove('is-match', 'is-over');
            if (want) {
                meta.textContent = codes.length + ' of ' + want + ' ' + (want === 1 ? 'unit' : 'units') + ' entered';
                if (codes.length === want) meta.classList.add('is-match');
                else if (codes.length > want) meta.classList.add('is-over');
            } else {
                meta.textContent = codes.length ? codes.length + ' ' + unit + ' entered' : '';
            }
            input.placeholder = codes.length ? 'Add another…' : (source.dataset.imeiPlaceholder || 'Scan or type, then press Enter');
        }

        function sync() {
            source.value = codes.join('\n');
            source.dispatchEvent(new Event('input', { bubbles: true }));
            render();
        }

        function add(raw) {
            const found = imeiParse(raw);
            let duplicates = 0;
            found.forEach((code) => {
                if (codes.some((c) => c.toLowerCase() === code.toLowerCase())) { duplicates++; return; }
                codes.push(code);
            });
            if (duplicates) {
                Nubia.toast(duplicates === 1 ? 'That IMEI / serial is already added.' : duplicates + ' duplicate entries were skipped.', 'warning');
            }
            sync();
        }

        function commit() {
            if (!input.value.trim()) return;
            add(input.value);
            input.value = '';
        }

        field.addEventListener('mousedown', (e) => {
            if (e.target.closest('.imei-chip')) return;
            e.preventDefault();
            input.focus();
        });
        field.addEventListener('click', (e) => {
            const btn = e.target.closest('.imei-chip-x');
            if (!btn) return;
            codes.splice(parseInt(btn.dataset.index, 10), 1);
            sync();
            input.focus();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === 'Tab' || e.key === ',' || e.key === ';' || e.key === ' ') {
                if (!input.value.trim()) {
                    if (e.key === 'Enter') e.preventDefault();
                    return;
                }
                e.preventDefault();
                commit();
            } else if (e.key === 'Backspace' && input.value === '' && codes.length) {
                e.preventDefault();
                codes.pop();
                sync();
            }
        });
        input.addEventListener('paste', (e) => {
            const text = (e.clipboardData || window.clipboardData)?.getData('text');
            if (!text) return;
            e.preventDefault();
            add(input.value + ' ' + text);
            input.value = '';
        });
        input.addEventListener('blur', () => { field.classList.remove('is-focused'); commit(); });
        input.addEventListener('focus', () => field.classList.add('is-focused'));

        // The expected count follows a quantity field that can change at any time.
        new MutationObserver(() => render()).observe(source, {
            attributes: true,
            attributeFilter: ['data-imei-expected']
        });

        const form = source.form;
        if (form && required) {
            source.dataset.imeiRequired = '1';
            if (form.dataset.imeiChipsGuard !== '1') {
                form.dataset.imeiChipsGuard = '1';
                form.addEventListener('submit', (e) => {
                    const blank = Array.from(form.querySelectorAll('.imei-chips-source[data-imei-required="1"]'))
                        .find((el) => !el.value.trim() && el.closest('.imei-chips')?.offsetParent !== null);
                    if (blank) {
                        e.preventDefault();
                        Nubia.toast('Enter at least one IMEI / serial.', 'error');
                        blank.closest('.imei-chips')?.querySelector('.imei-chips-input')?.focus();
                    }
                });
            }
        }

        render();
    }

    Nubia.imeiChips = {
        init(root) {
            (root || document).querySelectorAll('textarea[data-imei-chips]').forEach(buildImeiChips);
        }
    };

    function initImeiChips() {
        Nubia.imeiChips.init(document);
        // Item rows in the purchase / sale builders are re-rendered on every change.
        let queued = false;
        const observer = new MutationObserver(() => {
            if (queued) return;
            queued = true;
            requestAnimationFrame(() => { queued = false; Nubia.imeiChips.init(document); });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    /* ---------------- Init ---------------- */
    document.addEventListener('DOMContentLoaded', function () {
        // Apply saved theme early (also done inline in <head>).
        document.getElementById('themeToggle')?.addEventListener('click', () => Nubia.theme.toggle());
        document.getElementById('sidebarToggle')?.addEventListener('click', () => Nubia.sidebar.toggle());
        document.getElementById('mobileMenuBtn')?.addEventListener('click', () => Nubia.sidebar.toggle());
        document.querySelector('.sidebar-overlay')?.addEventListener('click', () => Nubia.sidebar.close());
        Nubia.sidebar.init();
        initGlobalSearch();
        initNotifications();
        initNumberInputGuard();
        initImeiChips();
        Nubia.pwa.register();
        Nubia.pwa.bindInstall();
        if (Nubia.pwa.isIos() && !localStorage.getItem('nubia-pwa-banner-dismissed')) {
            setTimeout(() => Nubia.pwa.showBanner(), 1800);
        }

        // Auto-submit delete forms via confirm.
        document.querySelectorAll('[data-confirm-delete]').forEach((el) => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const form = this.closest('form') || document.getElementById(this.dataset.form);
                Nubia.confirm({
                    title: 'Delete record?',
                    text: this.dataset.confirmDelete || 'This will permanently remove the record.',
                    icon: 'warning',
                    confirmText: 'Delete',
                    onConfirm: () => form && form.submit()
                });
            });
        });
    });

    window.Nubia = Nubia;
})();
