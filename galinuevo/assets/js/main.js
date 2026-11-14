function syncFieldState(field) {
    const input = field.querySelector('input');
    if (!input) return;
    field.classList.toggle('is-filled', input.value.trim().length > 0);
}

document.querySelectorAll('.lg-field').forEach((field) => {
    const input = field.querySelector('input');
    if (!input) return;
    syncFieldState(field);
    input.addEventListener('input', () => syncFieldState(field));
    input.addEventListener('blur', () => syncFieldState(field));
});

document.querySelectorAll('[data-toggle-visibility]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const selector = btn.getAttribute('data-toggle-visibility');
        const input = document.querySelector(selector);
        if (!input) return;

        const icon = btn.querySelector('i');
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';

        if (icon) {
            icon.classList.toggle('bi-eye', !isHidden);
            icon.classList.toggle('bi-eye-slash', isHidden);
        }

        input.focus();
    });
});

const dniInput = document.getElementById('doc');
const passInput = document.getElementById('pass');
const btnLogin = document.getElementById('btnLogin');

function digitsOnly(el, maxLen) {
    el.value = el.value.replace(/\D/g, '').slice(0, maxLen);
}

function updateButtonState() {
    const dniOk = dniInput.value.length >= 6 && dniInput.value.length <= 8;
    const passOk = passInput.value.length === 4;
    btnLogin.disabled = !(dniOk && passOk);
}

dniInput.addEventListener('input', () => {
    digitsOnly(dniInput, 8);
    updateButtonState();
});

passInput.addEventListener('input', () => {
    digitsOnly(passInput, 4);
    updateButtonState();
});

window.addEventListener('load', updateButtonState);
