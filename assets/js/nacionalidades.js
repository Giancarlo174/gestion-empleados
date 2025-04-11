/**
 * Script para manejar la visualización de banderas en los selectores de nacionalidad
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar los selectores de nacionalidad
    var nacionalidadSelects = document.querySelectorAll('.nacionalidad-select');
    
    nacionalidadSelects.forEach(function(select) {
        // Agregar evento para mostrar la bandera cuando cambia la selección
        select.addEventListener('change', function() {
            mostrarBanderaNacionalidad(this);
        });
        
        // Mostrar la bandera inicial si hay un valor seleccionado
        if (select.value) {
            mostrarBanderaNacionalidad(select);
        }
    });

    function mostrarBanderaNacionalidad(select) {
        // Obtener la opción seleccionada
        var selectedOption = select.options[select.selectedIndex];
        var countryCode = selectedOption.getAttribute('data-country');
        
        if (countryCode) {
            // Crear o actualizar el elemento de bandera
            var flagContainer = select.nextElementSibling;
            if (!flagContainer || !flagContainer.classList.contains('flag-container')) {
                flagContainer = document.createElement('div');
                flagContainer.classList.add('flag-container');
                select.parentNode.insertBefore(flagContainer, select.nextElementSibling);
            }
            
            // Usar banderas locales
            flagContainer.innerHTML = '<img src="/ds6/assets/img/flags/' + countryCode + '.png" ' +
                                     'alt="' + selectedOption.text + '" class="flag-icon" /> ' +
                                     selectedOption.text;
        }
    }
});
