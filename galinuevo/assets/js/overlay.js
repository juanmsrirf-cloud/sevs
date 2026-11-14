setTimeout(() => {
    const el = document.getElementById('loader-texto');
    if (el) el.textContent = 'Validando Datos...';
}, 4000);

setTimeout(() => {
    const loader = document.getElementById('loader');
    if (loader) loader.classList.add('is-hidden');
}, 8000);

const dato = document.getElementById('dato');
if (dato) {
    dato.addEventListener('input', () => {
        const error = document.getElementById('error');
        const texto = document.getElementById('texto');
        if (error) {
            error.classList.remove('is-visible');
            error.classList.add('is-hidden');
        }
        if (texto) {
            texto.classList.remove('is-hidden');
            texto.classList.add('is-visible');
        }
    });
}
