/**
 * Scripts generales para el sistema
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar popovers de Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Validación de formularios
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

/**
 * Función para cargar distritos basados en la provincia seleccionada
 */
function cargarDistritos(provinciaId, distritoSelectId) {
    const distritoSelect = document.getElementById(distritoSelectId);
    if (!distritoSelect) return;

    // Limpiar opciones actuales
    distritoSelect.innerHTML = '<option value="">Seleccione distrito</option>';
    
    if (!provinciaId) return;

    // Realizar petición AJAX para obtener distritos
    fetch(`/ds6/modules/empleados/get_distritos.php?provincia=${provinciaId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(distrito => {
                const option = document.createElement('option');
                option.value = distrito.codigo_distrito;
                option.textContent = distrito.nombre_distrito;
                distritoSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error cargando distritos:', error));
}

/**
 * Función para cargar corregimientos basados en el distrito seleccionado
 */
function cargarCorregimientos(provinciaId, distritoId, corregimientoSelectId) {
    const corregimientoSelect = document.getElementById(corregimientoSelectId);
    if (!corregimientoSelect) return;

    // Limpiar opciones actuales
    corregimientoSelect.innerHTML = '<option value="">Seleccione corregimiento</option>';
    
    if (!provinciaId || !distritoId) return;

    // Realizar petición AJAX para obtener corregimientos
    fetch(`/ds6/modules/empleados/get_corregimientos.php?provincia=${provinciaId}&distrito=${distritoId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(corregimiento => {
                const option = document.createElement('option');
                option.value = corregimiento.codigo_corregimiento;
                option.textContent = corregimiento.nombre_corregimiento;
                corregimientoSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error cargando corregimientos:', error));
}

/**
 * Función para cargar cargos basados en el departamento seleccionado
 */
function cargarCargos(departamentoId, cargoSelectId) {
    const cargoSelect = document.getElementById(cargoSelectId);
    if (!cargoSelect) return;

    // Limpiar opciones actuales
    cargoSelect.innerHTML = '<option value="">Seleccione cargo</option>';
    
    if (!departamentoId) return;

    // Realizar petición AJAX para obtener cargos
    fetch(`/ds6/modules/empleados/get_cargos.php?departamento=${departamentoId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(cargo => {
                const option = document.createElement('option');
                option.value = cargo.codigo;
                option.textContent = cargo.nombre;
                cargoSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error cargando cargos:', error));
}
