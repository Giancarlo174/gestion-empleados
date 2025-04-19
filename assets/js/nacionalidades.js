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
        
        // Eliminar cualquier contenedor existente
        var existingContainer = select.parentNode.querySelector('.flag-container');
        if (existingContainer) {
            existingContainer.remove();
        }
        
        if (countryCode) {
            // Crear el nuevo elemento de bandera con fade-in
            var flagContainer = document.createElement('div');
            flagContainer.classList.add('flag-container');
            flagContainer.style.opacity = '0';
            
            // Usar banderas locales
            flagContainer.innerHTML = '<img src="/ds6/assets/img/flags/' + countryCode.toLowerCase() + '.png" ' +
                                     'alt="' + selectedOption.text + '" class="flag-icon" /> ' +
                                     selectedOption.text;
            
            // Insertar después del select
            select.parentNode.insertBefore(flagContainer, select.nextElementSibling);
            
            // Aplicar efecto fade-in
            setTimeout(() => {
                flagContainer.style.transition = 'opacity 0.3s ease-in-out, transform 0.3s ease-in-out';
                flagContainer.style.opacity = '1';
                flagContainer.style.transform = 'translateY(0)';
            }, 10);
        }
    }
    
    // Función para precargar banderas (mejora el rendimiento)
    function precargarBanderas() {
        const nacionalidadSelect = document.querySelector('.nacionalidad-select');
        if (!nacionalidadSelect) return;
        
        Array.from(nacionalidadSelect.options).forEach(option => {
            const countryCode = option.getAttribute('data-country');
            if (countryCode) {
                const img = new Image();
                img.src = '/ds6/assets/img/flags/' + countryCode.toLowerCase() + '.png';
            }
        });
    }
    
    // Precargar banderas en segundo plano
    setTimeout(precargarBanderas, 1000);
});
