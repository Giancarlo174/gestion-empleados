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
    
    // Manejar la visibilidad del sidebar en dispositivos móviles
    const handleSidebarOnMobile = () => {
        if (window.innerWidth < 768) {
            const sidebarMenu = document.getElementById('sidebarMenu');
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            const navbarToggler = document.querySelector('.navbar-toggler');
            
            if (sidebarMenu && navLinks.length > 0) {
                // Cerrar el menú al hacer clic en un enlace
                navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (sidebarMenu.classList.contains('show')) {
                            const bsCollapse = new bootstrap.Collapse(sidebarMenu);
                            bsCollapse.hide();
                        }
                    });
                });

                // Mejorar la experiencia táctil en el menú
                navLinks.forEach(link => {
                    link.addEventListener('touchstart', function() {
                        this.classList.add('touch-active');
                    }, {passive: true});
                    
                    link.addEventListener('touchend', function() {
                        this.classList.remove('touch-active');
                    }, {passive: true});
                });
                
                // Cerrar el menú al hacer clic fuera de él
                document.addEventListener('click', (e) => {
                    if (sidebarMenu.classList.contains('show') && 
                        !sidebarMenu.contains(e.target) && 
                        !navbarToggler.contains(e.target)) {
                        const bsCollapse = new bootstrap.Collapse(sidebarMenu);
                        bsCollapse.hide();
                    }
                });
            }
            
            // Mejorar interacción en formularios móviles
            const enhanceMobileInputs = () => {
                // Prevenir zoom en iOS
                const viewportMeta = document.querySelector('meta[name="viewport"]');
                if (viewportMeta) {
                    viewportMeta.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0');
                }
                
                // Mejorar accesibilidad de inputs en móviles
                document.querySelectorAll('input, select, textarea').forEach(input => {
                    input.addEventListener('focus', function() {
                        // Añadir padding para evitar que el teclado virtual oculte el input
                        const scrollY = window.scrollY;
                        setTimeout(() => {
                            window.scrollTo(0, scrollY);
                        }, 50);
                    });
                });
            };
            
            enhanceMobileInputs();
        }
    };
    
    // Ejecutar una vez al cargar
    handleSidebarOnMobile();
    
    // También ejecutar cuando cambia el tamaño de la ventana
    window.addEventListener('resize', handleSidebarOnMobile);
    
    // Agregar animaciones suaves a las tarjetas
    const animateCards = () => {
        const isMobile = window.innerWidth < 768;
        const cards = document.querySelectorAll('.card');
        
        cards.forEach((card, index) => {
            // En móvil, usar animaciones más sencillas
            if (isMobile) {
                card.classList.add('card-visible');
            } else {
                setTimeout(() => {
                    card.classList.add('card-visible');
                }, 100 * index);
            }
        });
    };
    
    animateCards();
    
    // Agregar efecto scroll suave a los enlaces
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== "#" && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Mejorar la experiencia con los modales en dispositivos móviles
    const enhanceModalsForMobile = () => {
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                // Asegurar que el modal sea visible y accesible en móvil
                if (window.innerWidth < 768) {
                    const modalDialog = this.querySelector('.modal-dialog');
                    if (modalDialog) {
                        modalDialog.style.margin = '10px';
                        
                        // Permitir scroll dentro del modal pero no en el body
                        document.body.style.overflow = 'hidden';
                        modalDialog.style.overflowY = 'auto';
                    }
                }
            });
            
            modal.addEventListener('hidden.bs.modal', function() {
                // Restaurar scroll del body
                document.body.style.overflow = '';
            });
        });
    };
    
    enhanceModalsForMobile();
});

/**
 * Función para cargar distritos basados en la provincia seleccionada
 */
function cargarDistritos(provinciaId, distritoSelectId) {
    const distritoSelect = document.getElementById(distritoSelectId);
    if (!distritoSelect) return;
    
    // Mostrar indicador de carga
    distritoSelect.innerHTML = '<option value="">Cargando distritos...</option>';
    distritoSelect.disabled = true;
    
    // Realizar petición AJAX
    fetch(`/ds6/modules/empleados/get_distritos.php?provincia=${provinciaId}`)
        .then(response => response.json())
        .then(data => {
            // Limpiar select
            distritoSelect.innerHTML = '<option value="">Seleccione un distrito...</option>';
            
            // Agregar opciones
            if (data && data.length) {
                data.forEach(distrito => {
                    const option = document.createElement('option');
                    option.value = distrito.codigo_distrito;
                    option.textContent = distrito.nombre_distrito;
                    distritoSelect.appendChild(option);
                });
            }
            
            distritoSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error al cargar los distritos:', error);
            distritoSelect.innerHTML = '<option value="">Error al cargar</option>';
            distritoSelect.disabled = true;
        });
}

/**
 * Función para cargar corregimientos basados en el distrito seleccionado
 */
function cargarCorregimientos(provinciaId, distritoId, corregimientoSelectId) {
    const corregimientoSelect = document.getElementById(corregimientoSelectId);
    if (!corregimientoSelect) return;
    
    // Mostrar indicador de carga
    corregimientoSelect.innerHTML = '<option value="">Cargando corregimientos...</option>';
    corregimientoSelect.disabled = true;
    
    // Realizar petición AJAX
    fetch(`/ds6/modules/empleados/get_corregimientos.php?provincia=${provinciaId}&distrito=${distritoId}`)
        .then(response => response.json())
        .then(data => {
            // Limpiar select
            corregimientoSelect.innerHTML = '<option value="">Seleccione un corregimiento...</option>';
            
            // Agregar opciones
            if (data && data.length) {
                data.forEach(corregimiento => {
                    const option = document.createElement('option');
                    option.value = corregimiento.codigo_corregimiento;
                    option.textContent = corregimiento.nombre_corregimiento;
                    corregimientoSelect.appendChild(option);
                });
            }
            
            corregimientoSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error al cargar los corregimientos:', error);
            corregimientoSelect.innerHTML = '<option value="">Error al cargar</option>';
            corregimientoSelect.disabled = true;
        });
}

/**
 * Función para cargar cargos basados en el departamento seleccionado
 */
function cargarCargos(departamentoId, cargoSelectId) {
    const cargoSelect = document.getElementById(cargoSelectId);
    if (!cargoSelect) return;
    
    // Mostrar indicador de carga
    cargoSelect.innerHTML = '<option value="">Cargando cargos...</option>';
    cargoSelect.disabled = true;
    
    // Realizar petición AJAX
    fetch(`/ds6/modules/empleados/get_cargos.php?departamento=${departamentoId}`)
        .then(response => response.json())
        .then(data => {
            // Limpiar select
            cargoSelect.innerHTML = '<option value="">Seleccione un cargo...</option>';
            
            // Agregar opciones
            if (data && data.length) {
                data.forEach(cargo => {
                    const option = document.createElement('option');
                    option.value = cargo.codigo;
                    option.textContent = cargo.nombre;
                    cargoSelect.appendChild(option);
                });
            }
            
            cargoSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error al cargar los cargos:', error);
            cargoSelect.innerHTML = '<option value="">Error al cargar</option>';
            cargoSelect.disabled = true;
        });
}

// Función para activar/desactivar campos según un checkbox
function toggleFieldsVisibility(checkboxId, fieldsContainerId, inverse = false) {
    const checkbox = document.getElementById(checkboxId);
    const container = document.getElementById(fieldsContainerId);
    
    if (!checkbox || !container) return;
    
    function updateVisibility() {
        if ((checkbox.checked && !inverse) || (!checkbox.checked && inverse)) {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }
    
    checkbox.addEventListener('change', updateVisibility);
    updateVisibility(); // Set initial state
}
