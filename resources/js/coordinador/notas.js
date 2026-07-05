function circulo(valor, notaMinima) {
    if (valor === null || valor === undefined) return '—';
    const clase = valor >= notaMinima ? 'c-nota-ap' : valor >= notaMinima - 2 ? 'c-nota-med' : 'c-nota-des';

    return `<div class="c-nota-circle ${clase}" style="width:32px;height:32px;font-size:12px">${valor}</div>`;
}

function renderRow(nota, notaMinima) {
    const estudiante = nota.matricula_curso?.matricula?.estudiante;
    const curso = nota.matricula_curso?.curso;
    const nombreEstudiante = estudiante ? `${estudiante.nombres} ${estudiante.apellido_paterno ?? ''}` : '—';

    return `
        <tr>
            <td>${nombreEstudiante}</td>
            <td>${curso?.nombre_curso ?? '—'}</td>
            <td>${nota.unidad}</td>
            <td>${circulo(nota.practica, notaMinima)}</td>
            <td>${circulo(nota.teoria, notaMinima)}</td>
            <td>${circulo(nota.examen, notaMinima)}</td>
            <td>${circulo(nota.promedio, notaMinima)}</td>
            <td><span class="c-badge ${nota.promedio >= notaMinima ? 'c-badge-green' : 'c-badge-red'}">${nota.promedio >= notaMinima ? 'Aprobado' : 'Desaprobado'}</span></td>
        </tr>
    `;
}

function cargar() {
    const tbody = document.getElementById('coord-notas-tbody');
    const params = new URLSearchParams();
    const curso = document.getElementById('coord-notas-filtro-curso')?.value;
    const unidad = document.getElementById('coord-notas-filtro-unidad')?.value;
    if (curso) params.set('id_curso', curso);
    if (unidad) params.set('unidad', unidad);

    fetch(`/api/coordinador/notas${params.toString() ? `?${params}` : ''}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const notas = data.notas ?? [];
            const resumen = data.resumen ?? {};
            const notaMinima = resumen.nota_minima ?? 10.5;

            tbody.innerHTML = notas.length
                ? notas.map((n) => renderRow(n, notaMinima)).join('')
                : '<tr><td colspan="8" class="coord-portafolio-empty">No hay notas para este filtro.</td></tr>';

            const root = document.getElementById('coord-notas-kpis');
            root.querySelector('[data-kpi="total"]').textContent = resumen.total ?? 0;
            root.querySelector('[data-kpi="aprobados"]').textContent = resumen.aprobados ?? 0;
            root.querySelector('[data-kpi="desaprobados"]').textContent = resumen.desaprobados ?? 0;
            root.querySelector('[data-kpi="promedio"]').textContent = resumen.promedio_general ?? '—';
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="8" class="coord-portafolio-empty">No se pudo cargar el registro de notas.</td></tr>';
            console.error(error);
        });
}

export function initCoordinadorNotas() {
    const tbody = document.getElementById('coord-notas-tbody');
    if (!tbody) return;

    cargar();
    document.getElementById('coord-notas-filtro-curso')?.addEventListener('change', cargar);
    document.getElementById('coord-notas-filtro-unidad')?.addEventListener('change', cargar);
}

document.addEventListener('DOMContentLoaded', initCoordinadorNotas);
