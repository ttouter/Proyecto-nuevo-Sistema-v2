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
                // Actualizar título visual
                labelCategoria.textContent = nombreCat;
                
                // Limpiar listas INMEDIATAMENTE para dar feedback visual y evitar clicks erróneos
                containerDisponibles.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
                containerAsignados.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

                // Cargar datos frescos
                cargarListas(idCat);
                
                document.getElementById('panelGestionJueces').style.display = 'flex';
            } else {
                document.getElementById('panelGestionJueces').style.display = 'none';
            }
        });
    }

    // 2. Función para cargar datos de la API (CON ANTI-CACHE AGRESIVO)
    function cargarListas(idCat) {
        // Usamos timestamp para forzar al navegador a pedir datos nuevos siempre
        const timestamp = new Date().getTime();

        fetch(`../../controllers/api_jueces_categoria.php?cat=${idCat}&_t=${timestamp}`)
            .then(response => response.json())
            .then(data => {
                // Renderizar ambas columnas
                renderizarLista(data.disponibles, containerDisponibles, 'asignar');
                renderizarLista(data.asignados, containerAsignados, 'liberar');
            })
            .catch(error => {
                console.error('Error:', error);
                containerDisponibles.innerHTML = '<div class="empty-msg text-danger">Error de conexión</div>';
                containerAsignados.innerHTML = '<div class="empty-msg text-danger">Error de conexión</div>';
            });
    }

    // 3. Función para dibujar las tarjetas de jueces
    function renderizarLista(lista, contenedor, accion) {
        contenedor.innerHTML = ''; // Limpiar loader

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
            
            // Textos y clases para el botón
            const btnClass = accion === 'asignar' ? 'btn-add' : 'btn-remove';
            const btnTitle = accion === 'asignar' ? 'Asignar a esta categoría' : 'Quitar de esta categoría';

            card.innerHTML = `
                <div class="juez-info">
                    <strong>${juez.nombre} ${juez.apellidoPat}</strong>
                    <small>${juez.nombreEscuela || 'Sin escuela'}</small>
                </div>
                <button class="btn-move ${btnClass}" title="${btnTitle}">
                    ${icon}
                </button>
            `;

            // Asignar evento click
            const btn = card.querySelector('.btn-move');
            btn.onclick = function() {
                
                // --- VALIDACIÓN DE LÍMITE (FRONTEND) ---
                if (accion === 'asignar') {
                    // Contamos cuántas tarjetas existen en el contenedor de asignados
                    const numAsignados = containerAsignados.querySelectorAll('.juez-card').length;
                    
                    if (numAsignados >= 3) {
                        const alertBox = document.getElementById('alertBoxError');
                        const alertText = document.getElementById('alertTextError');
                        const msg = 'Error: Solo se permiten 3 jueces por categoría.';

                        if (alertBox && alertText) {
                            alertText.innerText = msg;
                            alertBox.style.display = 'block';
                            setTimeout(() => { alertBox.style.display = 'none'; }, 5000);
                        } else {
                            alert(msg);
                        }
                        return; // DETIENE LA EJECUCIÓN
                    }
                }

                // Deshabilitar botón visualmente para evitar doble click
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                // NOTA: Leemos el ID de la categoría directamente del SELECT para asegurar
                // que estamos asignando a la categoría que el usuario está viendo actualmente.
                const currentCatId = selectCat.value;
                
                moverJuez(juez.idJuez, accion, currentCatId, this);
            };

            contenedor.appendChild(card);
        });
    }

    // 4. Función para mover jueces (AJAX)
    function moverJuez(idJuez, accion, idCategoria, btnElement) {
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
                // ÉXITO: Recargar listas inmediatamente para ver el cambio reflejado
                cargarListas(idCategoria);
            } else {
                // ERROR: Restaurar el botón a su estado original
                if(btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = accion === 'asignar' ? '<i class="fas fa-arrow-right"></i>' : '<i class="fas fa-arrow-left"></i>';
                }

                // MOSTRAR ALERTA FLOTANTE (Estilo Dashboard)
                const alertBox = document.getElementById('alertBoxError');
                const alertText = document.getElementById('alertTextError');
                
                const msgError = data.msg || 'Error desconocido al mover el juez';

                if (alertBox && alertText) {
                    alertText.innerText = msgError;
                    alertBox.style.display = 'block';
                    // Ocultar alerta automáticamente después de 6 seg
                    setTimeout(() => { alertBox.style.display = 'none'; }, 6000);
                } else {
                    alert(msgError); // Fallback si no existe la alerta flotante
                }
            }
        })
        .catch(err => {
            console.error(err);
            if(btnElement) btnElement.disabled = false;
            alert('Error de conexión al servidor.');
        });
    }
});