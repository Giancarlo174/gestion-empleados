package com.ds6p1.ds6p1.modules.admin.sections.empleados
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowDropDown
import androidx.compose.material.icons.filled.DateRange
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.Alignment
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.api.NuevoEmpleado
import java.text.SimpleDateFormat
import java.util.*
import androidx.compose.material3.DatePicker
import androidx.compose.material3.DatePickerDialog
import androidx.compose.material3.rememberDatePickerState

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmpleadoCreateScreen(
    onVolverLista: () -> Unit,
    viewModel: EmpleadoCreateViewModel = viewModel()
) {
    val dateFormat = remember { SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()) }

    // ---------- Estados ----------
    var prefijo by remember { mutableStateOf("") }
    var tomo by remember { mutableStateOf("") }
    var asiento by remember { mutableStateOf("") }
    val cedula = listOf(prefijo, tomo, asiento).filter { it.isNotBlank() }.joinToString("-")

    var nombre1 by remember { mutableStateOf("") }
    var nombre2 by remember { mutableStateOf("") }
    var apellido1 by remember { mutableStateOf("") }
    var apellido2 by remember { mutableStateOf("") }
    var apellidoc by remember { mutableStateOf("") }
    var genero by remember { mutableStateOf(-1) }
    var estadoCivil by remember { mutableStateOf(-1) }
    var tipoSangre by remember { mutableStateOf("") }
    var usaAc by remember { mutableStateOf(0) }

    // FECHA DE NACIMIENTO usando DatePickerDialog de Material3
    var fNacimiento by remember { mutableStateOf("") }
    var showDatePickerNacimiento by remember { mutableStateOf(false) }
    val datePickerState = rememberDatePickerState()

    var nacionalidad by remember { mutableStateOf("") }
    var celular by remember { mutableStateOf("") }
    var telefono by remember { mutableStateOf("") }
    var correo by remember { mutableStateOf("") }
    var provinciaSel by remember { mutableStateOf("") }
    var distritoSel by remember { mutableStateOf("") }
    var corregimientoSel by remember { mutableStateOf("") }
    var calle by remember { mutableStateOf("") }
    var casa by remember { mutableStateOf("") }
    var comunidad by remember { mutableStateOf("") }
    var departamentoSel by remember { mutableStateOf("") }
    var cargoSel by remember { mutableStateOf("") }
    var estado by remember { mutableStateOf(1) }
    var contrasena by remember { mutableStateOf("") }
    var contrasena2 by remember { mutableStateOf("") }

    val provincias by viewModel.provincias.collectAsState()
    val distritos by viewModel.distritos.collectAsState()
    val corregimientos by viewModel.corregimientos.collectAsState()
    val departamentos by viewModel.departamentos.collectAsState()
    val cargos by viewModel.cargos.collectAsState()
    val nacionalidades by viewModel.nacionalidades.collectAsState()

    var expandedGenero by remember { mutableStateOf(false) }
    var expandedEstadoCivil by remember { mutableStateOf(false) }
    var expandedTipoSangre by remember { mutableStateOf(false) }
    var expandedProvincia by remember { mutableStateOf(false) }
    var expandedDistrito by remember { mutableStateOf(false) }
    var expandedCorregimiento by remember { mutableStateOf(false) }
    var expandedDepto by remember { mutableStateOf(false) }
    var expandedCargo by remember { mutableStateOf(false) }
    var expandedEstado by remember { mutableStateOf(false) }
    var expandedNacionalidad by remember { mutableStateOf(false) }

    val generos = listOf("Femenino", "Masculino")
    val estadosCiviles = listOf("Soltero/a", "Casado/a", "Viudo/a", "Divorciado/a")
    val sangres = listOf("A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-")
    val estadosEmpleado = listOf("Inactivo", "Activo")

    // -------- Selects anidados ----------
    LaunchedEffect(provinciaSel) {
        if (provinciaSel.isNotEmpty()) {
            viewModel.loadDistritos(provinciaSel)
            distritoSel = ""
            corregimientoSel = ""
        }
    }
    LaunchedEffect(distritoSel) {
        if (provinciaSel.isNotEmpty() && distritoSel.isNotEmpty()) {
            viewModel.loadCorregimientos(provinciaSel, distritoSel)
            corregimientoSel = ""
        }
    }
    LaunchedEffect(departamentoSel) {
        if (departamentoSel.isNotEmpty()) {
            viewModel.loadCargos(departamentoSel)
            cargoSel = ""
        }
    }
    LaunchedEffect(Unit) { viewModel.loadNacionalidades() }

    // ---------- Estado envío (modales) ----------
    val envioState by viewModel.state.collectAsState()
    when (envioState) {
        is EmpleadoCreateState.Loading -> LinearProgressIndicator(Modifier.fillMaxWidth())
        is EmpleadoCreateState.Success -> {
            AlertDialog(
                onDismissRequest = { viewModel.resetState(); onVolverLista() },
                title = { Text("¡Éxito!") },
                text = { Text((envioState as EmpleadoCreateState.Success).message) },
                confirmButton = { TextButton(onClick = { viewModel.resetState(); onVolverLista() }) { Text("OK") } }
            )
        }
        is EmpleadoCreateState.Error -> {
            AlertDialog(
                onDismissRequest = { viewModel.resetState() },
                title = { Text("Error") },
                text = { Text((envioState as EmpleadoCreateState.Error).message) },
                confirmButton = { TextButton(onClick = { viewModel.resetState() }) { Text("OK") } }
            )
        }
        else -> Unit
    }

    // ---------- UI PRINCIPAL ----------
    Column(
        Modifier
            .verticalScroll(rememberScrollState())
            .padding(16.dp)
            .fillMaxWidth()
    ) {
        Text("Agregar Nuevo Empleado", style = MaterialTheme.typography.headlineSmall)
        Spacer(Modifier.height(18.dp))
        Text("Información Personal", style = MaterialTheme.typography.titleMedium)
        Spacer(Modifier.height(8.dp))

        OutlinedTextField(
            value = prefijo,
            onValueChange = { prefijo = it },
            label = { Text("Prefijo") },
            modifier = Modifier.fillMaxWidth()
        )
        OutlinedTextField(
            value = tomo,
            onValueChange = { tomo = it },
            label = { Text("Tomo") },
            modifier = Modifier.fillMaxWidth()
        )
        OutlinedTextField(
            value = asiento,
            onValueChange = { asiento = it },
            label = { Text("Asiento") },
            modifier = Modifier.fillMaxWidth()
        )
        OutlinedTextField(value = nombre1, onValueChange = { nombre1 = it }, label = { Text("Primer Nombre *") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = nombre2, onValueChange = { nombre2 = it }, label = { Text("Segundo Nombre") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = apellido1, onValueChange = { apellido1 = it }, label = { Text("Primer Apellido *") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = apellido2, onValueChange = { apellido2 = it }, label = { Text("Segundo Apellido") }, modifier = Modifier.fillMaxWidth())

        ExposedDropdownMenuBox(expanded = expandedGenero, onExpandedChange = { expandedGenero = !expandedGenero }) {
            OutlinedTextField(
                value = if (genero in generos.indices) generos[genero] else "",
                onValueChange = {},
                label = { Text("Género *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { Icon(Icons.Default.ArrowDropDown, null) }
            )
            ExposedDropdownMenu(expanded = expandedGenero, onDismissRequest = { expandedGenero = false }) {
                generos.forEachIndexed { index, gen ->
                    DropdownMenuItem(
                        text = { Text(gen) },
                        onClick = { genero = index; expandedGenero = false }
                    )
                }
            }
        }
        if (genero == 0) {
            OutlinedTextField(
                value = apellidoc,
                onValueChange = { apellidoc = it },
                label = { Text("Apellido Casada") },
                modifier = Modifier.fillMaxWidth()
            )
        }

        ExposedDropdownMenuBox(expanded = expandedEstadoCivil, onExpandedChange = { expandedEstadoCivil = !expandedEstadoCivil }) {
            OutlinedTextField(
                value = if (estadoCivil in estadosCiviles.indices) estadosCiviles[estadoCivil] else "",
                onValueChange = {},
                label = { Text("Estado Civil *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { Icon(Icons.Default.ArrowDropDown, null) }
            )
            ExposedDropdownMenu(expanded = expandedEstadoCivil, onDismissRequest = { expandedEstadoCivil = false }) {
                estadosCiviles.forEachIndexed { index, ec ->
                    DropdownMenuItem(
                        text = { Text(ec) },
                        onClick = { estadoCivil = index; expandedEstadoCivil = false }
                    )
                }
            }
        }
        ExposedDropdownMenuBox(expanded = expandedTipoSangre, onExpandedChange = { expandedTipoSangre = !expandedTipoSangre }) {
            OutlinedTextField(
                value = tipoSangre,
                onValueChange = {},
                label = { Text("Tipo de Sangre *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { Icon(Icons.Default.ArrowDropDown, null) }
            )
            ExposedDropdownMenu(expanded = expandedTipoSangre, onDismissRequest = { expandedTipoSangre = false }) {
                sangres.forEach { sang ->
                    DropdownMenuItem(
                        text = { Text(sang) },
                        onClick = { tipoSangre = sang; expandedTipoSangre = false }
                    )
                }
            }
        }

        // ----------- FECHA DE NACIMIENTO (DatePickerDialog Material3) -----------
        OutlinedTextField(
            value = fNacimiento,
            onValueChange = {},
            label = { Text("Fecha de Nacimiento *") },
            readOnly = true,
            trailingIcon = {
                IconButton(onClick = { showDatePickerNacimiento = true }) {
                    Icon(Icons.Default.DateRange, contentDescription = "Seleccionar fecha")
                }
            },
            modifier = Modifier.fillMaxWidth()
        )
        if (showDatePickerNacimiento) {
            DatePickerDialog(
                onDismissRequest = { showDatePickerNacimiento = false },
                confirmButton = {
                    TextButton(onClick = {
                        val millis = datePickerState.selectedDateMillis
                        if (millis != null) {
                            fNacimiento = dateFormat.format(Date(millis))
                        }
                        showDatePickerNacimiento = false
                    }) { Text("OK") }
                },
                dismissButton = {
                    TextButton(onClick = { showDatePickerNacimiento = false }) { Text("Cancelar") }
                }
            ) {
                DatePicker(state = datePickerState)
            }
        }

        // Nacionalidad (desde la base de datos)
        ExposedDropdownMenuBox(expanded = expandedNacionalidad, onExpandedChange = { expandedNacionalidad = !expandedNacionalidad }) {
            OutlinedTextField(
                value = nacionalidades.find { it.codigo == nacionalidad }?.pais ?: "",
                onValueChange = {},
                label = { Text("Nacionalidad *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { Icon(Icons.Default.ArrowDropDown, null) }
            )
            ExposedDropdownMenu(expanded = expandedNacionalidad, onDismissRequest = { expandedNacionalidad = false }) {
                nacionalidades.forEach { nac ->
                    DropdownMenuItem(
                        text = { Text(nac.pais) },
                        onClick = { nacionalidad = nac.codigo; expandedNacionalidad = false }
                    )
                }
            }
        }

        Spacer(Modifier.height(16.dp))
        Text("Información de Contacto", style = MaterialTheme.typography.titleMedium)
        Spacer(Modifier.height(8.dp))
        OutlinedTextField(value = celular, onValueChange = { celular = it }, label = { Text("Celular *") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = telefono, onValueChange = { telefono = it }, label = { Text("Teléfono Fijo") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = correo, onValueChange = { correo = it }, label = { Text("Correo Electrónico *") }, modifier = Modifier.fillMaxWidth())

        Spacer(Modifier.height(16.dp))
        Text("Información de Ubicación", style = MaterialTheme.typography.titleMedium)
        Spacer(Modifier.height(8.dp))
        ExposedDropdownMenuBox(expanded = expandedProvincia, onExpandedChange = { expandedProvincia = !expandedProvincia }) {
            OutlinedTextField(
                value = provincias.find { it.codigo == provinciaSel }?.nombre ?: "",
                onValueChange = {},
                label = { Text("Provincia *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { Icon(Icons.Default.ArrowDropDown, null) }
            )
            ExposedDropdownMenu(expanded = expandedProvincia, onDismissRequest = { expandedProvincia = false }) {
                provincias.forEach { provincia ->
                    DropdownMenuItem(
                        text = { Text(provincia.nombre) },
                        onClick = {
                            provinciaSel = provincia.codigo
                            expandedProvincia = false
                            distritoSel = ""
                            corregimientoSel = ""
                        }
                    )
                }
            }
        }
        ExposedDropdownMenuBox(expanded = expandedDistrito, onExpandedChange = { expandedDistrito = !expandedDistrito }) {
            OutlinedTextField(
                value = distritos.find { it.codigo == distritoSel }?.nombre ?: "",
                onValueChange = {},
                label = { Text("Distrito *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { Icon(Icons.Default.ArrowDropDown, null) }
            )
            ExposedDropdownMenu(expanded = expandedDistrito, onDismissRequest = { expandedDistrito = false }) {
                distritos.forEach { distrito ->
                    DropdownMenuItem(
                        text = { Text(distrito.nombre) },
                        onClick = {
                            distritoSel = distrito.codigo
                            expandedDistrito = false
                            corregimientoSel = ""
                        }
                    )
                }
            }
        }
        ExposedDropdownMenuBox(expanded = expandedCorregimiento, onExpandedChange = { expandedCorregimiento = !expandedCorregimiento }) {
            OutlinedTextField(
                value = corregimientos.find { it.codigo == corregimientoSel }?.nombre ?: "",
                onValueChange = {},
                label = { Text("Corregimiento *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { Icon(Icons.Default.ArrowDropDown, null) }
            )
            ExposedDropdownMenu(expanded = expandedCorregimiento, onDismissRequest = { expandedCorregimiento = false }) {
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
        OutlinedTextField(value = calle, onValueChange = { calle = it }, label = { Text("Calle") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = casa, onValueChange = { casa = it }, label = { Text("Casa/Apto") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = comunidad, onValueChange = { comunidad = it }, label = { Text("Comunidad/Urbanización") }, modifier = Modifier.fillMaxWidth())

        Spacer(Modifier.height(16.dp))
        Text("Información Laboral", style = MaterialTheme.typography.titleMedium)
        Spacer(Modifier.height(8.dp))
        ExposedDropdownMenuBox(expanded = expandedDepto, onExpandedChange = { expandedDepto = !expandedDepto }) {
            OutlinedTextField(
                value = departamentos.find { it.codigo == departamentoSel }?.nombre ?: "",
                onValueChange = {},
                label = { Text("Departamento *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { Icon(Icons.Default.ArrowDropDown, null) }
            )
            ExposedDropdownMenu(expanded = expandedDepto, onDismissRequest = { expandedDepto = false }) {
                departamentos.forEach { dep ->
                    DropdownMenuItem(
                        text = { Text(dep.nombre) },
                        onClick = {
                            departamentoSel = dep.codigo
                            expandedDepto = false
                            cargoSel = ""
                        }
                    )
                }
            }
        }
        ExposedDropdownMenuBox(expanded = expandedCargo, onExpandedChange = { expandedCargo = !expandedCargo }) {
            OutlinedTextField(
                value = cargos.find { it.codigo == cargoSel }?.nombre ?: "",
                onValueChange = {},
                label = { Text("Cargo *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { Icon(Icons.Default.ArrowDropDown, null) }
            )
            ExposedDropdownMenu(expanded = expandedCargo, onDismissRequest = { expandedCargo = false }) {
                cargos.forEach { cargo ->
                    DropdownMenuItem(
                        text = { Text(cargo.nombre) },
                        onClick = {
                            cargoSel = cargo.codigo
                            expandedCargo = false
                        }
                    )
                }
            }
        }
        ExposedDropdownMenuBox(expanded = expandedEstado, onExpandedChange = { expandedEstado = !expandedEstado }) {
            OutlinedTextField(
                value = estadosEmpleado.getOrNull(estado) ?: "",
                onValueChange = {},
                label = { Text("Estado *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { Icon(Icons.Default.ArrowDropDown, null) }
            )
            ExposedDropdownMenu(expanded = expandedEstado, onDismissRequest = { expandedEstado = false }) {
                estadosEmpleado.forEachIndexed { idx, texto ->
                    DropdownMenuItem(
                        text = { Text(texto) },
                        onClick = { estado = idx; expandedEstado = false }
                    )
                }
            }
        }

        Spacer(Modifier.height(16.dp))
        Text("Credenciales de Acceso", style = MaterialTheme.typography.titleMedium)
        Spacer(Modifier.height(8.dp))
        OutlinedTextField(
            value = contrasena,
            onValueChange = { contrasena = it },
            label = { Text("Contraseña *") },
            modifier = Modifier.fillMaxWidth(),
            visualTransformation = PasswordVisualTransformation()
        )
        OutlinedTextField(
            value = contrasena2,
            onValueChange = { contrasena2 = it },
            label = { Text("Confirmar Contraseña *") },
            modifier = Modifier.fillMaxWidth(),
            visualTransformation = PasswordVisualTransformation()
        )

        Spacer(Modifier.height(24.dp))
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(16.dp)) {
            Button(
                onClick = {
                    val valido = prefijo.isNotBlank() && tomo.isNotBlank() && asiento.isNotBlank() &&
                            nombre1.isNotBlank() && apellido1.isNotBlank() &&
                            correo.isNotBlank() && celular.isNotBlank() && contrasena == contrasena2 &&
                            provinciaSel.isNotBlank() && distritoSel.isNotBlank() && corregimientoSel.isNotBlank() &&
                            departamentoSel.isNotBlank() && cargoSel.isNotBlank() &&
                            genero >= 0 && estadoCivil >= 0 && tipoSangre.isNotBlank() && fNacimiento.isNotBlank() &&
                            nacionalidad.isNotBlank()
                    if (valido) {
                        val nuevo = NuevoEmpleado(
                            cedula = cedula,
                            prefijo = prefijo,
                            tomo = tomo,
                            asiento = asiento,
                            nombre1 = nombre1,
                            nombre2 = nombre2,
                            apellido1 = apellido1,
                            apellido2 = apellido2,
                            apellidoc = apellidoc,
                            genero = genero,
                            estado_civil = estadoCivil,
                            tipo_sangre = tipoSangre,
                            usa_ac = usaAc,
                            f_nacimiento = fNacimiento,
                            celular = celular,
                            telefono = telefono,
                            correo = correo,
                            provincia = provinciaSel,
                            distrito = distritoSel,
                            corregimiento = corregimientoSel,
                            calle = calle,
                            casa = casa,
                            comunidad = comunidad,
                            nacionalidad = nacionalidad,
                            f_contra = "", // No se envía, la pone el backend
                            cargo = cargoSel,
                            departamento = departamentoSel,
                            estado = estado,
                            contrasena = contrasena
                        )
                        viewModel.registrarEmpleado(nuevo)
                    }
                }
            ) { Text("Agregar Empleado") }
            OutlinedButton(onClick = onVolverLista) { Text("Cancelar") }
        }
    }
}
