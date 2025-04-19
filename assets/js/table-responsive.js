/**
 * Script para mejorar las tablas responsivas
 * Añade indicadores visuales y mejora la experiencia de usuario en tablas con scroll horizontal
 */

document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todos los contenedores de tablas responsivas
    const tableContainers = document.querySelectorAll('.table-responsive');
    
    tableContainers.forEach(container => {
        // Crear el indicador de scroll si la tabla es más ancha que el contenedor
        if (container.scrollWidth > container.clientWidth) {
            addScrollIndicator(container);
            setupScrollEvents(container);
        }
        
        // Comprobamos si la tabla tiene celdas de acción y ajustamos
        optimizeActionButtons(container);
    });
    
    // Manejar cambios de tamaño de ventana
    window.addEventListener('resize', function() {
        tableContainers.forEach(container => {
            // Verificar si la tabla necesita scroll horizontal
            const needsScroll = container.scrollWidth > container.clientWidth;
            const hasIndicator = container.querySelector('.table-scroll-indicator');
            
            if (needsScroll && !hasIndicator) {
                addScrollIndicator(container);
                setupScrollEvents(container);
            } else if (!needsScroll && hasIndicator) {
                container.querySelector('.table-scroll-indicator').remove();
            }
        });
    });
});

/**
 * Añade un indicador visual de scroll horizontal a la tabla
 */
function addScrollIndicator(tableContainer) {
    // Comprobar si ya existe el indicador
    if (tableContainer.querySelector('.table-scroll-indicator')) return;
    
    // Crear el indicador solo para dispositivos móviles
    if (window.innerWidth < 768) {
        const indicator = document.createElement('div');
        indicator.className = 'table-scroll-indicator';
        indicator.innerHTML = '<i class="fas fa-arrows-alt-h"></i>';
        indicator.style.cssText = `
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            right: 0;
            top: 0;
            background: rgba(0,0,0,0.5);
            color: white;
            padding: 5px 8px;
            border-radius: 3px;
            font-size: 12px;
            opacity: 0.8;
            z-index: 2;
            animation: pulse 1.5s infinite;
        `;
        
        // Crear una hoja de estilo para la animación
        if (!document.querySelector('#table-scroll-style')) {
            const style = document.createElement('style');
            style.id = 'table-scroll-style';
            style.textContent = `
                @keyframes pulse {
                    0% { opacity: 0.8; }
                    50% { opacity: 0.5; }
                    100% { opacity: 0.8; }
                }
                
                .table-responsive {
                    position: relative;
                }
                
                .table-scroll-indicator.end {
                    transform: scaleX(-1);
                }
                
                .table-scroll-indicator.hidden {
                    display: none !important;
                }
            `;
            document.head.appendChild(style);
        }
        
        tableContainer.appendChild(indicator);
        
        // Ocultar el indicador después de un tiempo
        setTimeout(() => {
            indicator.classList.add('hidden');
        }, 3000);
    }
}

/**
 * Configura los eventos de scroll para la tabla
 */
function setupScrollEvents(tableContainer) {
    const indicator = tableContainer.querySelector('.table-scroll-indicator');
    if (!indicator) return;
    
    // Mostrar indicador cuando el usuario toca la tabla
    tableContainer.addEventListener('touchstart', function() {
        indicator.classList.remove('hidden');
    }, { passive: true });
    
    // Ocultar indicador después de un tiempo
    tableContainer.addEventListener('touchend', function() {
        setTimeout(() => {
            indicator.classList.add('hidden');
        }, 2000);
    }, { passive: true });
    
    // Actualizar posición del indicador según la dirección del scroll
    tableContainer.addEventListener('scroll', function() {
        // Si está cerca del extremo derecho
        if (tableContainer.scrollLeft + tableContainer.clientWidth >= tableContainer.scrollWidth - 10) {
            indicator.classList.add('end');
        } else {
            indicator.classList.remove('end');
        }
    }, { passive: true });
}

/**
 * Optimiza los botones de acción en tablas responsivas
 */
function optimizeActionButtons(tableContainer) {
    // Buscar celdas con múltiples botones de acción
    const actionCells = tableContainer.querySelectorAll('td:last-child');
    
    actionCells.forEach(cell => {
        const buttons = cell.querySelectorAll('.btn');
        
        // Si hay varios botones, podemos optimizarlos para móvil
        if (buttons.length > 2 && window.innerWidth < 768) {
            // Añadir un dropdown de acciones si hay muchos botones
            createActionDropdown(cell, buttons);
        }
    });
}

/**
 * Crea un dropdown para los botones de acción en pantallas pequeñas
 */
function createActionDropdown(cell, buttons) {
    // Solo aplicar en móvil
    if (window.innerWidth >= 768 || cell.querySelector('.dropdown-menu')) return;
    
    // Crear estructura del dropdown
    const dropdownContainer = document.createElement('div');
    dropdownContainer.className = 'dropdown';
    
    const dropdownButton = document.createElement('button');
    dropdownButton.className = 'btn btn-secondary btn-sm dropdown-toggle';
    dropdownButton.setAttribute('data-bs-toggle', 'dropdown');
    dropdownButton.setAttribute('aria-expanded', 'false');
    dropdownButton.innerHTML = '<i class="fas fa-ellipsis-v"></i> Acciones';
    
    const dropdownMenu = document.createElement('div');
    dropdownMenu.className = 'dropdown-menu dropdown-menu-end';
    
    // Mover los botones originales al menú dropdown
    buttons.forEach(button => {
        const action = document.createElement('a');
        action.className = 'dropdown-item';
        action.href = button.href || '#';
        
        if (button.getAttribute('data-bs-toggle')) {
            action.setAttribute('data-bs-toggle', button.getAttribute('data-bs-toggle'));
        }
        
        // Conservar los eventos onClick originales
        const originalOnClick = button.getAttribute('onclick');
        if (originalOnClick) {
            action.setAttribute('onclick', originalOnClick);
        }
        
        // Copiar el contenido y estilo del botón
        action.innerHTML = button.innerHTML;
        
        // Añadir clases de color según el botón original
        if (button.classList.contains('btn-danger')) {
            action.classList.add('text-danger');
        } else if (button.classList.contains('btn-warning')) {
            action.classList.add('text-warning');
        } else if (button.classList.contains('btn-info')) {
            action.classList.add('text-info');
        } else if (button.classList.contains('btn-success')) {
            action.classList.add('text-success');
        }
        
        dropdownMenu.appendChild(action);
    });
    
    // Añadir elementos al contenedor
    dropdownContainer.appendChild(dropdownButton);
    dropdownContainer.appendChild(dropdownMenu);
    
    // Reemplazar los botones originales con el dropdown
    cell.innerHTML = '';
    cell.appendChild(dropdownContainer);
}