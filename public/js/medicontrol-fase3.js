// Validación de experiencia de usuario. El controlador Laravel valida de nuevo en servidor.
(() => {
  'use strict';
  for (const form of document.querySelectorAll('[data-mc-file-form]')) {
    const input = form.querySelector('input[type="file"][name="archivo"]');
    const ayuda = form.querySelector('#archivo-ayuda');
    const enviar = form.querySelector('[data-mc-submit]');
    if (!input || !ayuda || !enviar) continue;
    const textoOriginal = ayuda.textContent;
    const validar = () => {
      input.setCustomValidity('');
      const archivo = input.files?.[0];
      ayuda.textContent = textoOriginal;
      ayuda.classList.remove('text-danger');
      if (!archivo) return true;
      const extension = (archivo.name.split('.').pop() || '').toLowerCase();
      const permitido = ['pdf', 'jpg', 'jpeg', 'png'].includes(extension);
      const maximo = Number(input.dataset.maxBytes) || 5242880;
      if (!permitido) input.setCustomValidity('Seleccioná un PDF, JPG o PNG.');
      else if (archivo.size > maximo) input.setCustomValidity('El archivo supera el límite de 5 MB.');
      if (!input.validity.valid) {
        ayuda.textContent = input.validationMessage;
        ayuda.classList.add('text-danger');
        return false;
      }
      ayuda.textContent = `Seleccionado: ${archivo.name} (${(archivo.size / 1048576).toFixed(2)} MB)`;
      return true;
    };
    input.addEventListener('change', validar);
    form.addEventListener('submit', (event) => {
      if (!validar() || !form.reportValidity()) { event.preventDefault(); input.reportValidity(); return; }
      enviar.disabled = true;
      enviar.textContent = 'Subiendo archivo…';
    });
  }
  for (const form of document.querySelectorAll('[data-mc-delete-form]')) {
    form.addEventListener('submit', event => {
      if (!window.confirm('¿Eliminar este documento? Esta acción no se puede deshacer.')) event.preventDefault();
    });
  }
})();
