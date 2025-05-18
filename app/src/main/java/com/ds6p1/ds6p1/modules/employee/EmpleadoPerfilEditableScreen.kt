package com.ds6p1.ds6p1.modules.employee

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.NuevoEmpleado
import com.ds6p1.ds6p1.api.Provincia
import com.ds6p1.ds6p1.api.Distrito
import com.ds6p1.ds6p1.api.Corregimiento
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmpleadoPerfilEditableScreen(
    cedula: String,
    onBack: () -> Unit,
    viewModel: EmpleadoPerfilViewModel = viewModel()
) {
    val scrollState = rememberScrollState()
    val scope = rememberCoroutineScope()
    val uiState by viewModel.uiState.collectAsState()
    
    // Estados para manejar dialogs
    var showSuccessDialog by remember { mutableStateOf(false) }
    var showErrorDialog by remember { mutableStateOf(false) }
    var errorMessage by remember { mutableStateOf("") }
    var successMessage by remember { mutableStateOf("") }
    
    // Cargar datos del empleado
    LaunchedEffect(cedula) { viewModel.loadDetalle(cedula) }
    
    Surface(modifier = Modifier.fillMaxSize()) {
        when (uiState) {
            is EmpleadoDetalleUiState.Loading -> {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator()
                }
            }
            is EmpleadoDetalleUiState.Error -> {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Text((uiState as EmpleadoDetalleUiState.Error).message, color = MaterialTheme.colorScheme.error)
                        Spacer(modifier = Modifier.height(16.dp))
                        Button(onClick = onBack) {
                            Text("Volver")
                        }
                    }
                }
            }
            is EmpleadoDetalleUiState.Success -> {
                val empleado = (uiState as EmpleadoDetalleUiState.Success).empleado
                
                // Estados para los campos editables
                var nombre1 by remember { mutableStateOf(empleado.nombre1) }
                var nombre2 by remember { mutableStateOf(empleado.nombre2 ?: "") }
                var apellido1 by remember { mutableStateOf(empleado.apellido1) }
                var apellido2 by remember { mutableStateOf(empleado.apellido2 ?: "") }
                var apellidoCasada by remember { mutableStateOf(empleado.apellidoc ?: "") }
                var celular by remember { mutableStateOf(empleado.celular ?: "") }
                var telefono by remember { mutableStateOf(empleado.telefono ?: "") }
                var calle by remember { mutableStateOf(empleado.calle ?: "") }
                var casa by remember { mutableStateOf(empleado.casa ?: "") }
                var comunidad by remember { mutableStateOf(empleado.comunidad ?: "") }
                
                // Estados para las listas desplegables
                val provincias = remember { mutableStateListOf<Provincia>() }
                val distritos = remember { mutableStateListOf<Distrito>() }
                val corregimientos = remember { mutableStateListOf<Corregimiento>() }
                
                var provinciaSel by remember { mutableStateOf(empleado.provincia ?: "") }
                var distritoSel by remember { mutableStateOf(empleado.distrito ?: "") }
                var corregimientoSel by remember { mutableStateOf(empleado.corregimiento ?: "") }
                
                // Estados para control de los dropdowns
                var expandedProvincia by remember { mutableStateOf(false) }
                var expandedDistrito by remember { mutableStateOf(false) }
                var expandedCorregimiento by remember { mutableStateOf(false) }
                
                // Cargar datos de ubicación
                LaunchedEffect(Unit) {
                    try {
                        val response = ApiClient.optionsApi.getProvincias()
                        provincias.clear()
                        provincias.addAll(response)
                    } catch (e: Exception) {
                        errorMessage = "Error cargando provincias: ${e.message}"
                        showErrorDialog = true
                    }
                }
                
                LaunchedEffect(provinciaSel) {
                    if (provinciaSel.isNotEmpty()) {
                        try {
                            val response = ApiClient.optionsApi.getDistritos(provinciaSel)
                            distritos.clear()
                            distritos.addAll(response)
                        } catch (e: Exception) {
                            errorMessage = "Error cargando distritos: ${e.message}"
                            showErrorDialog = true
                        }
                    }
                }
                
                LaunchedEffect(distritoSel) {
                    if (provinciaSel.isNotEmpty() && distritoSel.isNotEmpty()) {
                        try {
                            val response = ApiClient.optionsApi.getCorregimientos(provinciaSel, distritoSel)
                            corregimientos.clear()
                            corregimientos.addAll(response)
                        } catch (e: Exception) {
                            errorMessage = "Error cargando corregimientos: ${e.message}"
                            showErrorDialog = true
                        }
                    }
                }
                
                // Dialogs
                if (showSuccessDialog) {
                    AlertDialog(
                        onDismissRequest = { 
                            showSuccessDialog = false
                            onBack()
                        },
                        title = { Text("¡Datos Actualizados!") },
                        text = { Text(successMessage) },
                        confirmButton = {
                            TextButton(onClick = { 
                                showSuccessDialog = false
                                onBack()
                            }) {
                                Text("Aceptar")
                            }
                        }
                    )
                }
                
                if (showErrorDialog) {
                    AlertDialog(
                        onDismissRequest = { showErrorDialog = false },
                        title = { Text("Error") },
                        text = { Text(errorMessage) },
                        confirmButton = {
                            TextButton(onClick = { showErrorDialog = false }) {
                                Text("Aceptar")
                            }
                        }
                    )
                }
                
                // UI principal
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .verticalScroll(scrollState)
                        .padding(16.dp)
                ) {
                    // Barra superior
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(bottom = 16.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        IconButton(onClick = onBack) {
                            Icon(Icons.Default.ArrowBack, contentDescription = "Regresar")
                        }
                        Text(
                            "Editar Perfil",
                            style = MaterialTheme.typography.titleLarge
                        )
                    }
                    
                    // Información Personal
                    Text("Información Personal", style = MaterialTheme.typography.titleMedium)
                    Spacer(modifier = Modifier.height(8.dp))
                    
                    // Cédula (solo lectura)
                    OutlinedTextField(
                        value = empleado.cedula,
                        onValueChange = { },
                        label = { Text("Cédula") },
                        modifier = Modifier.fillMaxWidth(),
                        enabled = false,
                        readOnly = true
                    )
                    
                    // Campos editables
                    OutlinedTextField(
                        value = nombre1,
                        onValueChange = { nombre1 = it },
                        label = { Text("Primer Nombre *") },
                        modifier = Modifier.fillMaxWidth()
                    )
                    
                    OutlinedTextField(
                        value = nombre2,
                        onValueChange = { nombre2 = it },
                        label = { Text("Segundo Nombre") },
                        modifier = Modifier.fillMaxWidth()
                    )
                    
                    OutlinedTextField(
                        value = apellido1,
                        onValueChange = { apellido1 = it },
                        label = { Text("Primer Apellido *") },
                        modifier = Modifier.fillMaxWidth()
                    )
                    
                    OutlinedTextField(
                        value = apellido2,
                        onValueChange = { apellido2 = it },
                        label = { Text("Segundo Apellido") },
                        modifier = Modifier.fillMaxWidth()
                    )
                    
                    // Apellido de casada (solo si es mujer)
                    if (empleado.genero == 0) {
                        OutlinedTextField(
                            value = apellidoCasada,
                            onValueChange = { apellidoCasada = it },
                            label = { Text("Apellido de Casada") },
                            modifier = Modifier.fillMaxWidth()
                        )
                    }
                    
                    Spacer(modifier = Modifier.height(16.dp))
                    Text("Información de Contacto", style = MaterialTheme.typography.titleMedium)
                    Spacer(modifier = Modifier.height(8.dp))
                    
                    OutlinedTextField(
                        value = celular,
                        onValueChange = { celular = it },
                        label = { Text("Celular *") },
                        modifier = Modifier.fillMaxWidth()
                    )
                    
                    OutlinedTextField(
                        value = telefono,
                        onValueChange = { telefono = it },
                        label = { Text("Teléfono Fijo") },
                        modifier = Modifier.fillMaxWidth()
                    )
                    
                    // Correo (solo lectura)
                    OutlinedTextField(
                        value = empleado.correo ?: "",
                        onValueChange = { },
                        label = { Text("Correo Electrónico") },
                        modifier = Modifier.fillMaxWidth(),
                        enabled = false,
                        readOnly = true
                    )
                    
                    Spacer(modifier = Modifier.height(16.dp))
                    Text("Dirección", style = MaterialTheme.typography.titleMedium)
                    Spacer(modifier = Modifier.height(8.dp))
                    
                    // Provincia (dropdown)
                    ExposedDropdownMenuBox(
                        expanded = expandedProvincia,
                        onExpandedChange = { expandedProvincia = !expandedProvincia }
                    ) {
                        OutlinedTextField(
                            value = provincias.find { it.codigo == provinciaSel }?.nombre ?: "",
                            onValueChange = { },
                            label = { Text("Provincia *") },
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expandedProvincia) },
                            modifier = Modifier
                                .fillMaxWidth()
                                .menuAnchor()
                        )
                        
                        ExposedDropdownMenu(
                            expanded = expandedProvincia,
                            onDismissRequest = { expandedProvincia = false }
                        ) {
                            provincias.forEach { provincia ->
                                DropdownMenuItem(
                                    text = { Text(provincia.nombre) },
                                    onClick = {
                                        provinciaSel = provincia.codigo
                                        expandedProvincia = false
                                        // Resetear selecciones dependientes
                                        distritoSel = ""
                                        corregimientoSel = ""
                                    }
                                )
                            }
                        }
                    }
                    
                    // Distrito (dropdown)
                    ExposedDropdownMenuBox(
                        expanded = expandedDistrito,
                        onExpandedChange = { expandedDistrito = !expandedDistrito }
                    ) {
                        OutlinedTextField(
                            value = distritos.find { it.codigo == distritoSel }?.nombre ?: "",
                            onValueChange = { },
                            label = { Text("Distrito *") },
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expandedDistrito) },
                            modifier = Modifier
                                .fillMaxWidth()
                                .menuAnchor()
                        )
                        
                        ExposedDropdownMenu(
                            expanded = expandedDistrito,
                            onDismissRequest = { expandedDistrito = false }
                        ) {
                            distritos.forEach { distrito ->
                                DropdownMenuItem(
                                    text = { Text(distrito.nombre) },
                                    onClick = {
                                        distritoSel = distrito.codigo
                                        expandedDistrito = false
                                        // Resetear selección dependiente
                                        corregimientoSel = ""
                                    }
                                )
                            }
                        }
                    }
                    
                    // Corregimiento (dropdown)
                    ExposedDropdownMenuBox(
                        expanded = expandedCorregimiento,
                        onExpandedChange = { expandedCorregimiento = !expandedCorregimiento }
                    ) {
                        OutlinedTextField(
                            value = corregimientos.find { it.codigo == corregimientoSel }?.nombre ?: "",
                            onValueChange = { },
                            label = { Text("Corregimiento *") },
                            readOnly = true,
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expandedCorregimiento) },
                            modifier = Modifier
                                .fillMaxWidth()
                                .menuAnchor()
                        )
                        
                        ExposedDropdownMenu(
                            expanded = expandedCorregimiento,
                            onDismissRequest = { expandedCorregimiento = false }
                        ) {
                            corregimientos.forEach { corregimiento ->
                                DropdownMenuItem(
                                    text = { Text(corregimiento.nombre) },
                                    onClick = {
                                        corregimientoSel = corregimiento.codigo
                                        expandedCorregimiento = false
                                    }
                                )
                            }
                        }
                    }
                    
                    OutlinedTextField(
                        value = calle,
                        onValueChange = { calle = it },
                        label = { Text("Calle") },
                        modifier = Modifier.fillMaxWidth()
                    )
                    
                    OutlinedTextField(
                        value = casa,
                        onValueChange = { casa = it },
                        label = { Text("Casa/Apto") },
                        modifier = Modifier.fillMaxWidth()
                    )
                    
                    OutlinedTextField(
                        value = comunidad,
                        onValueChange = { comunidad = it },
                        label = { Text("Comunidad/Urbanización") },
                        modifier = Modifier.fillMaxWidth()
                    )
                    
                    Spacer(modifier = Modifier.height(16.dp))
                    Text("Información Laboral", style = MaterialTheme.typography.titleMedium)
                    Spacer(modifier = Modifier.height(8.dp))
                    
                    // Campos laborales (solo lectura)
                    OutlinedTextField(
                        value = empleado.nombre_departamento ?: "",
                        onValueChange = { },
                        label = { Text("Departamento") },
                        modifier = Modifier.fillMaxWidth(),
                        enabled = false,
                        readOnly = true
                    )
                    
                    OutlinedTextField(
                        value = empleado.nombre_cargo ?: "",
                        onValueChange = { },
                        label = { Text("Cargo") },
                        modifier = Modifier.fillMaxWidth(),
                        enabled = false,
                        readOnly = true
                    )
                    
                    OutlinedTextField(
                        value = if (empleado.estado == 1) "Activo" else "Inactivo",
                        onValueChange = { },
                        label = { Text("Estado") },
                        modifier = Modifier.fillMaxWidth(),
                        enabled = false,
                        readOnly = true
                    )
                    
                    OutlinedTextField(
                        value = empleado.f_contra ?: "",
                        onValueChange = { },
                        label = { Text("Fecha de Contratación") },
                        modifier = Modifier.fillMaxWidth(),
                        enabled = false,
                        readOnly = true
                    )
                    
                    Spacer(modifier = Modifier.height(24.dp))
                    
                    // Botones de acción
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.spacedBy(16.dp)
                    ) {
                        Button(
                            onClick = {
                                // Validar campos obligatorios
                                if (nombre1.isBlank() || apellido1.isBlank() || celular.isBlank() ||
                                    provinciaSel.isBlank() || distritoSel.isBlank() || corregimientoSel.isBlank()) {
                                    errorMessage = "Por favor completa todos los campos obligatorios marcados con *"
                                    showErrorDialog = true
                                } else {
                                    // Preparar objeto para actualizar
                                    val empleadoActualizado = NuevoEmpleado(
                                        cedula = empleado.cedula,
                                        prefijo = empleado.prefijo ?: "",
                                        tomo = empleado.tomo ?: "",
                                        asiento = empleado.asiento ?: "",
                                        nombre1 = nombre1,
                                        nombre2 = nombre2,
                                        apellido1 = apellido1,
                                        apellido2 = apellido2,
                                        apellidoc = if (empleado.genero == 0) apellidoCasada else "",
                                        genero = empleado.genero,
                                        estado_civil = empleado.estado_civil,
                                        tipo_sangre = empleado.tipo_sangre ?: "",
                                        usa_ac = empleado.usa_ac ?: 0,
                                        f_nacimiento = empleado.f_nacimiento ?: "",
                                        celular = celular,
                                        telefono = telefono,
                                        correo = empleado.correo ?: "",
                                        provincia = provinciaSel,
                                        distrito = distritoSel,
                                        corregimiento = corregimientoSel,
                                        calle = calle,
                                        casa = casa,
                                        comunidad = comunidad,
                                        nacionalidad = empleado.nacionalidad ?: "",
                                        f_contra = empleado.f_contra ?: "",
                                        cargo = empleado.cargo ?: "",
                                        departamento = empleado.departamento ?: "",
                                        estado = empleado.estado
                                    )
                                    
                                    // Enviar actualización
                                    scope.launch {
                                        try {
                                            val response = ApiClient.employeesApi.updateEmployee(empleadoActualizado)
                                            if (response.success) {
                                                successMessage = response.message ?: "Datos actualizados correctamente"
                                                showSuccessDialog = true
                                            } else {
                                                errorMessage = response.message ?: "Error al actualizar datos"
                                                showErrorDialog = true
                                            }
                                        } catch (e: Exception) {
                                            errorMessage = "Error de conexión: ${e.message}"
                                            showErrorDialog = true
                                        }
                                    }
                                }
                            },
                            modifier = Modifier.weight(1f)
                        ) {
                            Text("Guardar Cambios")
                        }
                        
                        OutlinedButton(
                            onClick = onBack,
                            modifier = Modifier.weight(1f)
                        ) {
                            Text("Cancelar")
                        }
                    }
                }
            }
        }
    }
}

