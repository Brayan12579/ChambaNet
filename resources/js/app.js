import './locations';

document.documentElement.classList.add('js-enhanced');

const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
if ('IntersectionObserver' in window && !motionPreference.matches) {
    const cards = document.querySelectorAll('.categories-grid .category-card, .recent-section .job-card, .companies-grid .company-card, .resource-card, .how-section .step');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(({ target, isIntersecting }) => {
            if (!isIntersecting) return;
            target.classList.add('is-revealed');
            observer.unobserve(target);
            window.setTimeout(() => target.classList.remove('reveal-pending', 'is-revealed'), 500);
        });
    }, { threshold: 0.08 });
    cards.forEach((card) => {
        card.classList.add('reveal-pending');
        observer.observe(card);
    });
    motionPreference.addEventListener('change', ({ matches }) => {
        if (!matches) return;
        observer.disconnect();
        cards.forEach((card) => card.classList.remove('reveal-pending', 'is-revealed'));
    });
}

const mobileLayout = window.matchMedia('(max-width: 899px)');
const menuToggle = document.querySelector('[data-menu-toggle]');
const mainNav = document.querySelector('#main-nav');

function closeMenu(restoreFocus = false) {
    if (!menuToggle || !mainNav) return;
    mainNav.classList.remove('is-open');
    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.setAttribute('aria-label', 'Abrir menú');
    if (restoreFocus && mobileLayout.matches) menuToggle.focus({ preventScroll: true });
}
menuToggle?.addEventListener('click', () => {
    const open = menuToggle.getAttribute('aria-expanded') !== 'true';
    if (!open) return closeMenu(true);
    mainNav?.classList.add('is-open');
    menuToggle.setAttribute('aria-expanded', 'true');
    menuToggle.setAttribute('aria-label', 'Cerrar menú');
    mainNav?.querySelector('a, button')?.focus({ preventScroll: true });
});
document.addEventListener('pointerdown', (event) => {
    if (mobileLayout.matches && mainNav?.classList.contains('is-open') && !mainNav.contains(event.target) && !menuToggle?.contains(event.target)) closeMenu();
});
document.addEventListener('focusin', (event) => {
    if (mobileLayout.matches && mainNav?.classList.contains('is-open') && !mainNav.contains(event.target) && !menuToggle?.contains(event.target)) closeMenu();
});
mainNav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => closeMenu()));

const filterToggle = document.querySelector('[data-filters-toggle]');
const filterPanel = document.querySelector('[data-filters-panel]');
const filterClose = document.querySelector('[data-filters-close]');
function setFiltersOpen(open, restoreFocus = false) {
    if (!filterPanel || !filterToggle) return;
    filterPanel.classList.toggle('is-open', open);
    filterToggle.setAttribute('aria-expanded', String(open));
    if (open && mobileLayout.matches) {
        filterPanel.querySelector('input:not([type="hidden"]), select, textarea')?.focus({ preventScroll: true });
        filterPanel.scrollIntoView({ block: 'nearest', behavior: 'instant' });
    }
    if (!open && restoreFocus) filterToggle.focus({ preventScroll: true });
}
function updateResponsiveState() {
    closeMenu();
    if (!filterPanel || !filterToggle) return;
    filterPanel.classList.toggle('is-collapsible', mobileLayout.matches);
    const hasErrors = Boolean(filterPanel.querySelector('[aria-invalid="true"], .field-error:not(:empty)'));
    setFiltersOpen(!mobileLayout.matches || hasErrors);
}
filterToggle?.addEventListener('click', () => setFiltersOpen(!filterPanel?.classList.contains('is-open'), true));
filterClose?.addEventListener('click', () => setFiltersOpen(false, true));
mobileLayout.addEventListener('change', updateResponsiveState);
updateResponsiveState();
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (mainNav?.classList.contains('is-open') && mobileLayout.matches) closeMenu(true);
    if (filterPanel?.classList.contains('is-open') && mobileLayout.matches && filterPanel.contains(event.target)) setFiltersOpen(false, true);
});

const registrationForm = document.querySelector('[data-register-form]');
if (registrationForm) {
    const companyField = registrationForm.querySelector('[data-company-field]');
    const updateRole = () => {
        const isEmployer = registrationForm.querySelector('[name="role"]:checked')?.value === 'employer';
        if (!companyField) return;
        companyField.hidden = !isEmployer;
        const input = companyField.querySelector('input');
        if (input) input.required = isEmployer;
        const title = document.querySelector('[data-register-title]');
        const description = document.querySelector('[data-register-description]');
        if (title) title.textContent = isEmployer ? 'Crea tu cuenta de empresa' : 'Crea tu perfil de candidato';
        if (description) description.textContent = isEmployer ? 'Presenta tu negocio y encuentra a tu próximo equipo.' : 'Guarda oportunidades y sigue tus postulaciones desde tu cuenta.';
    };
    registrationForm.querySelectorAll('[name="role"]').forEach((input) => input.addEventListener('change', updateRole));
    updateRole();
}
document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    const input = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!input || input.tagName !== 'INPUT') return;
    toggle.addEventListener('click', () => {
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(reveal));
        toggle.setAttribute('aria-label', reveal ? 'Ocultar contraseña' : 'Mostrar contraseña');
        toggle.textContent = reveal ? (toggle.dataset.labelHide || 'Ocultar') : (toggle.dataset.labelShow || 'Mostrar');
    });
});

let confirmationDialog;
let confirmationPending = false;
const approvedSubmissions = new WeakSet();
const loadingForms = new Map();

function makeConfirmationDialog() {
    const dialog = document.createElement('dialog');
    dialog.className = 'confirm-dialog';
    dialog.setAttribute('role', 'alertdialog');
    dialog.setAttribute('aria-labelledby', 'confirmation-title');
    dialog.setAttribute('aria-describedby', 'confirmation-description');
    const icon = document.createElement('span');
    icon.className = 'confirm-dialog-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = '!';
    const title = document.createElement('h2');
    title.id = 'confirmation-title';
    const description = document.createElement('p');
    description.id = 'confirmation-description';
    const actions = document.createElement('div');
    actions.className = 'actions';
    const cancel = document.createElement('button');
    cancel.type = 'button';
    cancel.className = 'button button-secondary';
    cancel.textContent = 'Cancelar';
    cancel.setAttribute('autofocus', '');
    cancel.dataset.dialogCancel = '';
    const proceed = document.createElement('button');
    proceed.type = 'button';
    proceed.className = 'button button-danger';
    proceed.textContent = 'Confirmar';
    proceed.dataset.dialogConfirm = '';
    actions.append(cancel, proceed);
    dialog.append(icon, title, description, actions);
    cancel.addEventListener('click', () => dialog.close('cancel'));
    proceed.addEventListener('click', () => dialog.close('confirm'));
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        dialog.close('cancel');
    });
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const bounds = dialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) dialog.close('cancel');
    });
    document.body.append(dialog);
    return dialog;
}
function confirmSubmission(form) {
    if (!('HTMLDialogElement' in window) || typeof HTMLDialogElement.prototype.showModal !== 'function') return Promise.resolve(window.confirm(form.dataset.confirm));
    if (confirmationPending) return Promise.resolve(false);
    confirmationPending = true;
    confirmationDialog ||= makeConfirmationDialog();
    confirmationDialog.querySelector('#confirmation-title').textContent = form.dataset.confirmTitle || 'Confirma esta acción';
    confirmationDialog.querySelector('#confirmation-description').textContent = form.dataset.confirm;
    confirmationDialog.querySelector('[data-dialog-confirm]').textContent = form.dataset.confirmButton || 'Sí, continuar';
    const previousFocus = document.activeElement;
    return new Promise((resolve) => {
        confirmationDialog.addEventListener('close', () => {
            confirmationPending = false;
            previousFocus?.focus?.({ preventScroll: true });
            resolve(confirmationDialog.returnValue === 'confirm');
        }, { once: true });
        confirmationDialog.returnValue = '';
        confirmationDialog.showModal();
        confirmationDialog.querySelector('[data-dialog-cancel]').focus({ preventScroll: true });
    });
}
function resetLoading(form) {
    const state = loadingForms.get(form);
    if (!state) return;
    window.clearTimeout(state.timer);
    delete form.dataset.submitting;
    form.removeAttribute('aria-busy');
    if (state.button) {
        state.button.removeAttribute('aria-disabled');
        state.button.removeAttribute('aria-busy');
        state.button.replaceChildren(...state.nodes);
    }
    loadingForms.delete(form);
}
function markLoading(form, button) {
    const nodes = button ? Array.from(button.childNodes) : [];
    form.dataset.submitting = 'true';
    form.setAttribute('aria-busy', 'true');
    if (button && !button.hasAttribute('data-keep-label')) {
        button.setAttribute('aria-disabled', 'true');
        button.setAttribute('aria-busy', 'true');
        button.textContent = form.method.toLowerCase() === 'get' ? 'Buscando…' : 'Un momento…';
    }
    const timer = window.setTimeout(() => resetLoading(form), 20000);
    loadingForms.set(form, { button, nodes, timer });
}
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() === 'dialog') return;
    if (form.dataset.submitting === 'true') {
        event.preventDefault();
        return;
    }
    const button = event.submitter;
    if (form.dataset.confirm && !approvedSubmissions.delete(form)) {
        event.preventDefault();
        confirmSubmission(form).then((approved) => {
            if (!approved || !form.isConnected) return;
            approvedSubmissions.add(form);
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit(button || undefined);
            } else {
                if (button?.name) {
                    const preservedValue = document.createElement('input');
                    preservedValue.type = 'hidden';
                    preservedValue.name = button.name;
                    preservedValue.value = button.value;
                    form.append(preservedValue);
                }
                markLoading(form, button);
                HTMLFormElement.prototype.submit.call(form);
            }
        });
        return;
    }
    markLoading(form, button);
});
window.addEventListener('pageshow', () => loadingForms.forEach((_, form) => resetLoading(form)));


document.querySelectorAll('[data-guidance]').forEach((guide) => {
    const items = Array.from(guide.querySelectorAll('[data-guidance-item]'));
    const progress = guide.querySelector('[data-guidance-progress]');
    const count = guide.querySelector('[data-guidance-count]');
    const status = guide.querySelector('[data-guidance-status]');
    const storageKey = 'trabajogo.guidance.' + (guide.dataset.guidance || 'preparacion');
    const itemKeys = items.map((item, index) => item.dataset.guidanceItem || String(index));
    let canStore = true;
    try {
        const saved = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
        if (Array.isArray(saved)) items.forEach((item, index) => { item.checked = saved.includes(itemKeys[index]); });
    } catch {
        canStore = false;
    }
    const update = (persist = true) => {
        const completed = items.filter((item) => item.checked).length;
        if (progress) { progress.max = items.length || 1; progress.value = completed; }
        if (count) count.textContent = completed + ' de ' + items.length;
        if (persist && canStore) {
            try {
                window.localStorage.setItem(storageKey, JSON.stringify(itemKeys.filter((_, index) => items[index].checked)));
            } catch {
                canStore = false;
            }
        }
        if (status) {
            status.textContent = !canStore
                ? 'Puedes completar esta lista. Tu navegador no permite guardar el avance.'
                : (completed === items.length && items.length > 0
                    ? '¡Lista completa! Tu avance queda guardado en este navegador.'
                    : 'Marca cada paso a medida que lo completes. El avance se guarda solo en este navegador.');
        }
    };
    items.forEach((item) => item.addEventListener('change', () => update()));
    guide.querySelector('[data-guidance-reset]')?.addEventListener('click', () => {
        items.forEach((item) => { item.checked = false; });
        update();
        items[0]?.focus({ preventScroll: true });
    });
    update(false);
});
