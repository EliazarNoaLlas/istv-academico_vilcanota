const BADGE_ESTADO = { PENDIENTE: 'c-badge-navy', SUBIDO: 'c-badge-gold', APROBADO: 'c-badge-green', OBSERVADO: 'c-badge-red' };

function renderRow(doc) {
    const docente = doc.portafolio?.docente
        ? `${doc.portafolio.docente.usuario?.nombres ?? ''} ${doc.portafolio.docente.usuario?.apellidos ?? ''}`
        : '—';
    const curso = doc.portafolio?.curso?.nombre_curso ?? '—';

    return `
        <tr>
            <td>${doc.titulo}</td>
            <td>${docente}</td>
            <td>${curso}</td>
            <td>${doc.tipo}</td>
            <td><span class="c-badge ${BADGE_ESTADO[doc.estado] ?? 'c-badge-navy'}">${doc.estado}</span></td>
        </tr>
    `;
}

function filtrosActuales() {
    const params = {};
    const docente = document.getElementById('dir-portafolio-filtro-docente')?.value;
    const curso = document.getElementById('dir-portafolio-filtro-curso')?.value;
    const estado = document.getElementById('dir-portafolio-filtro-estado')?.value;
    if (docente) params.id_docente = docente;
    if (curso) params.id_curso = curso;
    if (estado) params.estado = estado;

    return params;
}

function cargar() {
    const tbody = document.getElementById('dir-portafolio-tbody');
    const query = new URLSearchParams(filtrosActuales()).toString();

    fetch(`/api/director/portafolio${query ? `?${query}` : ''}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const documentos = data.documentos ?? [];

            tbody.innerHTML = documentos.length
                ? documentos.map(renderRow).join('')
                : '<tr><td colspan="5" class="c-table-empty">No hay documentos para este filtro.</td></tr>';
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="5" class="c-table-empty">No se pudo cargar el portafolio.</td></tr>';
            console.error(error);
        });
}

export function initDirectorPortafolio() {
    const tbody = document.getElementById('dir-portafolio-tbody');
    if (!tbody) return;

    cargar();
    ['dir-portafolio-filtro-docente', 'dir-portafolio-filtro-curso', 'dir-portafolio-filtro-estado'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', cargar);
    });
}

document.addEventListener('DOMContentLoaded', initDirectorPortafolio);
