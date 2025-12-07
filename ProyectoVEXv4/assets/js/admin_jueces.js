document.addEventListener('DOMContentLoaded', function() {
    
    const selectCat = document.getElementById('selectCategoriaJuez');
    const containerDisponibles = document.getElementById('listaDisponibles');
    const containerAsignados = document.getElementById('listaAsignados');
    const labelCategoria = document.getElementById('labelCategoriaSeleccionada');

    // 1. Evento al cambiar el select de categoría
    if(selectCat) {
        selectCat.addEventListener('change', function() {
            const idCat = this.value;
            const nombreCat = this.options[this.selectedIndex].text;
            
            if(idCat) {
                labelCategoria.textContent = nombreCat;
                cargarListas(idCat);
                document.getElementById('panelGestionJueces').style.display = 'flex';
            } else {
                document.getElementById('panelGestionJueces').style.display = 'none';
            }
        });
    }

    // 2. Función para cargar datos de la API
    function cargarListas(idCat) {
        containerDisponibles.innerHTML = '<div class="loading">Cargando...</div>';
        containerAsignados.innerHTML = '<div class="loading">Cargando...</div>';

        fetch(`../../controllers/api_jueces_categoria.php?cat=${idCat}`)
            .then(response => response.json())
            .then(data => {
                renderizarLista(data.disponibles, containerDisponibles, 'asignar', idCat);
                renderizarLista(data.asignados, containerAsignados, 'liberar', idCat);
            })
            .catch(error => console.error('Error:', error));
    }

    // 3. Función para dibujar las tarjetas de jueces
    function renderizarLista(lista, contenedor, accion, idCatActual) {
        contenedor.innerHTML = ''; // Limpiar

        if (lista.length === 0) {
            contenedor.innerHTML = '<div class="empty-msg">No hay jueces en esta lista.</div>';
            return;
        }

        lista.forEach(juez => {
            const card = document.createElement('div');
            card.className = `juez-card ${accion === 'asignar' ? 'card-disp' : 'card-asig'}`;
            
            // Icono según acción
            const icon = accion === 'asignar' 
                ? '<i class="fas fa-arrow-right"></i>' 
                : '<i class="fas fa-arrow-left"></i>';
            
            const btnHtml = accion === 'asignar'
                ? `<button class="btn-move btn-add" title="Asignar a categoría">${icon}</button>`
                : `<button class="btn-move btn-remove" title="Quitar de categoría">${icon}</button>`;

            card.innerHTML = `
                <div class="juez-info">
                    <strong>${juez.nombre} ${juez.apellidoPat}</strong>
                    <small>${juez.nombreEscuela || 'Sin escuela'}</small>
                </div>
                ${btnHtml}
            `;

            // Click en la tarjeta o botón para mover
            card.querySelector('.btn-move').addEventListener('click', () => {
                moverJuez(juez.idJuez, accion, idCatActual);
            });

            contenedor.appendChild(card);
        });
    }

    // 4. Función para mover jueces (AJAX)
    function moverJuez(idJuez, accion, idCategoria) {
        fetch('../../controllers/control_cambiar_cat_juez.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                accion: accion,
                idJuez: idJuez,
                idCategoria: idCategoria
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Recargar listas para ver cambios
                cargarListas(idCategoria);
            } else {
                alert('Error al mover el juez');
            }
        });
    }
});