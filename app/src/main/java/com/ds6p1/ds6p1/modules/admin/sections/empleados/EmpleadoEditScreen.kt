package com.ds6p1.ds6p1.modules.admin.sections.empleados

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.Alignment
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.api.EmpleadoDetalle
import com.ds6p1.ds6p1.api.NuevoEmpleado
import java.text.SimpleDateFormat
import java.util.*
import androidx.compose.material3.DatePicker
import androidx.compose.material3.DatePickerDialog
import androidx.compose.material3.rememberDatePickerState

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmpleadoEditScreen(
    empleado: EmpleadoDetalle,
    onBack: () -> Unit,
    onSuccess: () -> Unit,
    viewModel: EmpleadoEditScreenModel = viewModel()
) {
    val dateFormat = remember { SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()) }
    val scrollState = rememberScrollState()

    var prefijo by remember { mutableStateOf(empleado.prefijo ?: "") }
    var tomo by remember { mutableStateOf(empleado.tomo ?: "") }
    var asiento by remember { mutableStateOf(empleado.asiento ?: "") }
    val cedula = listOf(prefijo, tomo, asiento).filter { it.isNotBlank() }.joinToString("-")

    var nombre1 by remember { mutableStateOf(empleado.nombre1) }
    var nombre2 by remember { mutableStateOf(empleado.nombre2 ?: "") }
    var apellido1 by remember { mutableStateOf(empleado.apellido1) }
    var apellido2 by remember { mutableStateOf(empleado.apellido2 ?: "") }
    var apellidoc by remember { mutableStateOf(empleado.apellidoc ?: "") }
    var genero by remember { mutableStateOf(empleado.genero) }
    var estadoCivil by remember { mutableStateOf(empleado.estado_civil) }
    var tipoSangre by remember { mutableStateOf(empleado.tipo_sangre ?: "") }
    var usaAc by remember { mutableStateOf(empleado.usa_ac) }

    var fNacimiento by remember { mutableStateOf(empleado.f_nacimiento ?: "") }
    var showDatePickerNacimiento by remember { mutableStateOf(false) }
    val datePickerState = rememberDatePickerState(
        initialSelectedDateMillis = try {
            dateFormat.parse(empleado.f_nacimiento ?: "")?.time
        } catch (e: Exception) {
            null
        }
    )

    var nacionalidad by remember { mutableStateOf(empleado.nacionalidad ?: "") }
    var celular by remember { mutableStateOf(empleado.celular ?: "") }
    var telefono by remember { mutableStateOf(empleado.telefono ?: "") }
    var correo by remember { mutableStateOf(empleado.correo ?: "") }
    var provinciaSel by remember { mutableStateOf(empleado.provincia ?: "") }
    var distritoSel by remember { mutableStateOf(empleado.distrito ?: "") }
    var corregimientoSel by remember { mutableStateOf(empleado.corregimiento ?: "") }
    var calle by remember { mutableStateOf(empleado.calle ?: "") }
    var casa by remember { mutableStateOf(empleado.casa ?: "") }
    var comunidad by remember { mutableStateOf(empleado.comunidad ?: "") }
    var departamentoSel by remember { mutableStateOf(empleado.departamento ?: "") }
    var cargoSel by remember { mutableStateOf(empleado.cargo ?: "") }
    var estado by remember { mutableStateOf(empleado.estado) }

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

    LaunchedEffect(provinciaSel) {
        if (provinciaSel.isNotEmpty()) {
            viewModel.loadDistritos(provinciaSel)
            distritoSel = distritoSel
            corregimientoSel = corregimientoSel
        }
    }
    LaunchedEffect(distritoSel) {
        if (provinciaSel.isNotEmpty() && distritoSel.isNotEmpty()) {
            viewModel.loadCorregimientos(provinciaSel, distritoSel)
            corregimientoSel = corregimientoSel
        }
    }
    LaunchedEffect(departamentoSel) {
        if (departamentoSel.isNotEmpty()) {
            viewModel.loadCargos(departamentoSel)
            cargoSel = cargoSel
        }
    }

    val uiState by viewModel.state.collectAsState()

    when (uiState) {
        is EmpleadoEditState.Loading -> LinearProgressIndicator(Modifier.fillMaxWidth())
        is EmpleadoEditState.Success -> {
            AlertDialog(
                onDismissRequest = { viewModel.resetState(); onSuccess() },
                title = { Text("¡Actualización Exitosa!") },
                text = { Text((uiState as EmpleadoEditState.Success).message) },
                confirmButton = {
                    TextButton(onClick = { viewModel.resetState(); onSuccess() }) { Text("Aceptar") }
                }
            )
        }
        is EmpleadoEditState.Error -> {
            AlertDialog(
                onDismissRequest = { viewModel.resetState() },
                title = { Text("Error") },
                text = { Text((uiState as EmpleadoEditState.Error).message) },
                confirmButton = { TextButton(onClick = { viewModel.resetState() }) { Text("OK") } }
            )
        }
        else -> Unit
    }

    Column(
        Modifier
            .verticalScroll(scrollState)
            .padding(16.dp)
            .fillMaxWidth()
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically
        ) {
            IconButton(onClick = onBack, modifier = Modifier.padding(end = 8.dp)) {
                Icon(Icons.Default.ArrowBack, contentDescription = "Regresar", tint = MaterialTheme.colorScheme.primary)
            }
            Text("Editar Empleado", style = MaterialTheme.typography.headlineSmall)
        }
        Spacer(Modifier.height(18.dp))
        Text("Información Personal", style = MaterialTheme.typography.titleMedium)
        Spacer(Modifier.height(8.dp))

        OutlinedTextField(value = prefijo, onValueChange = { prefijo = it }, label = { Text("Prefijo") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = tomo, onValueChange = { tomo = it }, label = { Text("Tomo") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = asiento, onValueChange = { asiento = it }, label = { Text("Asiento") }, modifier = Modifier.fillMaxWidth())
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
                    DropdownMenuItem(text = { Text(gen) }, onClick = { genero = index; expandedGenero = false })
                }
            }
        }
        if (genero == 0) {
            OutlinedTextField(value = apellidoc, onValueChange = { apellidoc = it }, label = { Text("Apellido Casada") }, modifier = Modifier.fillMaxWidth())
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
                    DropdownMenuItem(text = { Text(ec) }, onClick = { estadoCivil = index; expandedEstadoCivil = false })
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
                    DropdownMenuItem(text = { Text(sang) }, onClick = { tipoSangre = sang; expandedTipoSangre = false })
                }
            }
        }

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
                    }) {
                        Text("OK")
                    }
                },
                dismissButton = {
                    TextButton(onClick = { showDatePickerNacimiento = false }) {
                        Text("Cancelar")
                    }
                }
            ) {
                DatePicker(state = datePickerState)
            }
        }

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
                    DropdownMenuItem(text = { Text(nac.pais) }, onClick = { nacionalidad = nac.codigo; expandedNacionalidad = false })
                }
            }
        }

        Spacer(Modifier.height(16.dp))
        Text("Información de Contacto", style = MaterialTheme.typography.titleMedium)
        Spacer(Modifier.height(8.dp))
        OutlinedTextField(value = celular, onValueChange = { celular = it }, label = { Text("Celular *") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = telefono, onValueChange = { telefono = it }, label = { Text("Teléfono Fijo") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = correo, onValueChange = {}, label = { Text("Correo Electrónico *") }, modifier = Modifier.fillMaxWidth(), readOnly = true, enabled = false)

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
                    DropdownMenuItem(text = { Text(provincia.nombre) }, onClick = {
                        provinciaSel = provincia.codigo
                        expandedProvincia = false
                    })
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
                    DropdownMenuItem(text = { Text(distrito.nombre) }, onClick = {
                        distritoSel = distrito.codigo
                        expandedDistrito = false
                    })
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
                    DropdownMenuItem(text = { Text(corregimiento.nombre) }, onClick = {
                        corregimientoSel = corregimiento.codigo
                        expandedCorregimiento = false
                    })
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
                    DropdownMenuItem(text = { Text(dep.nombre) }, onClick = {
                        departamentoSel = dep.codigo
                        expandedDepto = false
                    })
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
                    DropdownMenuItem(text = { Text(cargo.nombre) }, onClick = {
                        cargoSel = cargo.codigo
                        expandedCargo = false
                    })
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
                    DropdownMenuItem(text = { Text(texto) }, onClick = { estado = idx; expandedEstado = false })
                }
            }
        }

        Spacer(Modifier.height(24.dp))
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(16.dp)) {
            Button(
                onClick = {
                    val valido = nombre1.isNotBlank() && apellido1.isNotBlank() &&
                            celular.isNotBlank() && correo.isNotBlank() &&
                            provinciaSel.isNotBlank() && distritoSel.isNotBlank() && corregimientoSel.isNotBlank() &&
                            departamentoSel.isNotBlank() && cargoSel.isNotBlank() &&
                            tipoSangre.isNotBlank() && fNacimiento.isNotBlank() &&
                            nacionalidad.isNotBlank() && genero >= 0 && estadoCivil >= 0

                    if (valido) {
                        val actualizado = NuevoEmpleado(
                            cedula = cedula,
                            prefijo = prefijo,
                            tomo = tomo,
                            asiento = asiento,
                            nombre1 = nombre1,
                            nombre2 = nombre2.ifBlank { null },
                            apellido1 = apellido1,
                            apellido2 = apellido2.ifBlank { null },
                            apellidoc = apellidoc.ifBlank { null },
                            genero = genero,
                            estado_civil = estadoCivil,
                            tipo_sangre = tipoSangre,
                            usa_ac = usaAc,
                            f_nacimiento = fNacimiento,
                            celular = celular,
                            telefono = telefono.ifBlank { null },
                            correo = correo,
                            provincia = provinciaSel,
                            distrito = distritoSel,
                            corregimiento = corregimientoSel,
                            calle = calle.ifBlank { null },
                            casa = casa.ifBlank { null },
                            comunidad = comunidad.ifBlank { null },
                            nacionalidad = nacionalidad,
                            f_contra = empleado.f_contra ?: "",
                            cargo = cargoSel,
                            departamento = departamentoSel,
                            estado = estado
                        )
                        viewModel.actualizarEmpleado(actualizado)
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
