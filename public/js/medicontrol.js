/* Validaciones sencillas del lado del navegador. Laravel vuelve a validar en el servidor. */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-validar]').forEach((formulario) => {
        formulario.addEventListener('submit', (evento) => {
            const clave = formulario.querySelector('[name="password"]');
            const confirmacion = formulario.querySelector('[name="password_confirmation"]');

            // Revisamos que la confirmación coincida cuando el formulario la tenga.
            if (clave && confirmacion) {
                confirmacion.setCustomValidity(
                    clave.value !== confirmacion.value ? 'Las contraseñas no coinciden.' : ''
                );
            }
            if (!formulario.checkValidity()) {
                evento.preventDefault();
                evento.stopPropagation();
            }
            formulario.classList.add('was-validated');
        });
        const confirmacion = formulario.querySelector('[name="password_confirmation"]');
        if (confirmacion) confirmacion.addEventListener('input', () => confirmacion.setCustomValidity(''));
    });

    // Al seleccionar una doctora y fecha, consultamos los espacios libres del back-end.
    const selectorDoctor = document.getElementById('doctor_id');
    const selectorFecha = document.getElementById('fecha');
    const selectorHora = document.getElementById('hora');
    const sugerencias = document.getElementById('horarios-sugeridos');
    if (selectorDoctor && selectorFecha && selectorHora && sugerencias) {
        const revisarDisponibilidad = async () => {
            sugerencias.replaceChildren();
            if (!selectorDoctor.value || !selectorFecha.value) return;
            sugerencias.textContent = 'Consultando horarios disponibles...';
            try {
                const url = new URL(sugerencias.dataset.url, window.location.href);
                url.searchParams.set('doctor_id', selectorDoctor.value);
                url.searchParams.set('fecha', selectorFecha.value);
                const respuesta = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!respuesta.ok) throw new Error('No se pudo consultar la disponibilidad');
                const horarios = await respuesta.json();
                sugerencias.replaceChildren();
                if (!horarios.length) {
                    sugerencias.textContent = 'No hay horarios disponibles en esa fecha.';
                    return;
                }
                const etiqueta = document.createElement('span');
                etiqueta.className = 'd-block small text-muted mb-2';
                etiqueta.textContent = 'Selecciona un horario disponible:';
                sugerencias.appendChild(etiqueta);
                horarios.forEach((hora) => {
                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'btn btn-outline-primary btn-sm me-2 mb-2';
                    boton.textContent = hora;
                    boton.addEventListener('click', () => {
                        selectorHora.value = hora;
                        selectorHora.dispatchEvent(new Event('change'));
                        sugerencias.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                        boton.classList.add('active');
                    });
                    sugerencias.appendChild(boton);
                });
            } catch (_error) {
                sugerencias.textContent = 'No se pudieron cargar los horarios. Puedes escribir la hora manualmente.';
            }
        };
        selectorDoctor.addEventListener('change', revisarDisponibilidad);
        selectorFecha.addEventListener('change', revisarDisponibilidad);
        if (selectorDoctor.value && selectorFecha.value) revisarDisponibilidad();
    }
});

// Permite revisar la contraseña escrita sin enviarla a ningún servicio externo.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-toggle]').forEach((boton) => {
        boton.addEventListener('click', () => {
            const campo = document.getElementById(boton.dataset.passwordToggle);
            if (!campo) return;
            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            boton.textContent = mostrar ? 'Ocultar' : 'Ver';
            boton.setAttribute('aria-pressed', String(mostrar));
            boton.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

});

// El paciente consulta solamente los estados de sus propias citas.
document.addEventListener('DOMContentLoaded', () => {
    const feed = document.getElementById('mc-patient-status-feed');
    if (!feed) return;
    const clases = { 'Pendiente': 'mc-status-pendiente', 'Confirmado': 'mc-status-confirmado',
        'En proceso': 'mc-status-proceso', 'Completada': 'mc-status-completada',
        'Cancelada': 'mc-status-cancelada', 'No-show': 'mc-status-no-show' };
    async function actualizar() {
        if (document.hidden) return;
        try {
            const respuesta = await fetch(feed.dataset.url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!respuesta.ok) return;
            const estados = await respuesta.json();
            estados.forEach((item) => {
                const celda = document.querySelector(`[data-status-id="${Number(item.id)}"]`);
                if (!celda || !clases[item.status]) return;
                const etiqueta = celda.querySelector('.mc-pill');
                if (!etiqueta) return;
                Object.values(clases).forEach((clase) => etiqueta.classList.remove(clase));
                etiqueta.classList.add(clases[item.status]);
                etiqueta.textContent = ({ 'Pendiente': 'Solicitada', 'Confirmado': 'Confirmada', 'No-show': 'No asistió' })[item.status] || item.status;
            });
            const aviso = document.getElementById('mc-estado-actualizado');
            if (aviso) aviso.textContent = 'Actualizado a las ' + new Date().toLocaleTimeString('es-SV', { hour: '2-digit', minute: '2-digit' });
        } catch (_error) { /* Si falla internet, conservamos el último estado visible. */ }
    }
    actualizar();
    window.setInterval(actualizar, 25000);
});
