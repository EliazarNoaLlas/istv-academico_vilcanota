function limpiarTablero() {
    document.querySelectorAll('#dir-horarios-tbody .academic-slot').forEach((slot) => { slot.innerHTML = ''; });
}

function pintarBloque(bloque) {
    const slot = document.querySelector(`#dir-horarios-tbody .academic-slot[data-day="${bloque.dia}"][data-start="${bloque.hora_inicio.slice(0, 5)}"]`);
    if (!slot) return;

    const docenteNombre = bloque.docente ? `${bloque.docente.usuario?.nombres ?? ''} ${bloque.docente.usuario?.apellidos ?? ''}` : '';

    const article = document.createElement('article');
    article.className = 'schedule-block academic-block course-color';
    article.innerHTML = `
        <strong>${(bloque.curso?.nombre_curso ?? '').toUpperCase()}</strong>
        <span>${docenteNombre.toUpperCase()}</span>
    `;

    slot.appendChild(article);
}

function cargarHorario() {
    const params = new URLSearchParams();
    const idPrograma = document.getElementById('dir-horarios-filtro-programa')?.value;
    const idDocente = document.getElementById('dir-horarios-filtro-docente')?.value;
    const semestre = document.getElementById('dir-horarios-filtro-semestre')?.value;
    if (idPrograma) params.set('id_programa', idPrograma);
    if (idDocente) params.set('id_docente', idDocente);
    if (semestre) params.set('semestre', semestre);

    fetch(`/api/director/horarios?${params.toString()}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            limpiarTablero();
            (data.horarios ?? []).forEach(pintarBloque);
        })
        .catch((error) => console.error(error));
}

export function initDirectorHorarios() {
    const tbody = document.getElementById('dir-horarios-tbody');
    if (!tbody) return;

    cargarHorario();

    document.getElementById('dir-horarios-filtro-programa')?.addEventListener('change', cargarHorario);
    document.getElementById('dir-horarios-filtro-docente')?.addEventListener('change', cargarHorario);
    document.getElementById('dir-horarios-filtro-semestre')?.addEventListener('change', cargarHorario);
}

document.addEventListener('DOMContentLoaded', initDirectorHorarios);
