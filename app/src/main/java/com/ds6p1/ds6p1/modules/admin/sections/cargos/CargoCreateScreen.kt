package com.ds6p1.ds6p1.modules.admin.sections.cargos

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CargoCreateScreen(
    onVolverLista: () -> Unit,
    viewModel: CargoCreateViewModel = viewModel()
) {
    var codigo by remember { mutableStateOf("") }
    var nombre by remember { mutableStateOf("") }
    var departamentoSel by remember { mutableStateOf("") }
    var expandedDepartamento by remember { mutableStateOf(false) }

    val departamentos by viewModel.departamentos.collectAsState()
    val envioState by viewModel.state.collectAsState()

    when (envioState) {
        is CargoCreateState.Loading -> LinearProgressIndicator(Modifier.fillMaxWidth())
        is CargoCreateState.Success -> {
            AlertDialog(
                onDismissRequest = { viewModel.resetState(); onVolverLista() },
                title = { Text("¡Éxito!") },
                text = { Text((envioState as CargoCreateState.Success).message) },
                confirmButton = { TextButton(onClick = { viewModel.resetState(); onVolverLista() }) { Text("OK") } }
            )
        }
        is CargoCreateState.Error -> {
            AlertDialog(
                onDismissRequest = { viewModel.resetState() },
                title = { Text("Error") },
                text = { Text((envioState as CargoCreateState.Error).message) },
                confirmButton = { TextButton(onClick = { viewModel.resetState() }) { Text("OK") } }
            )
        }
        else -> Unit
    }

    Column(
        Modifier
            .verticalScroll(rememberScrollState())
            .padding(16.dp)
            .fillMaxWidth()
    ) {
        Text("Agregar Cargo", style = MaterialTheme.typography.headlineSmall)
        Spacer(Modifier.height(18.dp))

        OutlinedTextField(
            value = codigo,
            onValueChange = { if (it.length <= 3) codigo = it },
            label = { Text("Código de Cargo *") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true,
            supportingText = { Text("Ingrese un código único de hasta 3 caracteres.") }
        )
        OutlinedTextField(
            value = nombre,
            onValueChange = { nombre = it },
            label = { Text("Nombre del Cargo *") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true
        )

        ExposedDropdownMenuBox(expanded = expandedDepartamento, onExpandedChange = { expandedDepartamento = !expandedDepartamento }) {
            OutlinedTextField(
                value = departamentos.find { it.codigo == departamentoSel }?.nombre ?: "",
                onValueChange = {},
                label = { Text("Departamento *") },
                readOnly = true,
                modifier = Modifier.menuAnchor().fillMaxWidth(),
                trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expandedDepartamento) }
            )
            ExposedDropdownMenu(
                expanded = expandedDepartamento,
                onDismissRequest = { expandedDepartamento = false }
            ) {
                departamentos.forEach { departamento ->
                    DropdownMenuItem(
                        text = { Text(departamento.nombre) },
                        onClick = {
                            departamentoSel = departamento.codigo
                            expandedDepartamento = false
                        }
                    )
                }
            }
        }

        Spacer(Modifier.height(24.dp))
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(16.dp)) {
            Button(
                onClick = {
                    if (codigo.isNotBlank() && nombre.isNotBlank() && departamentoSel.isNotBlank()) {
                        viewModel.crearCargo(departamentoSel, codigo, nombre)
                    }
                }
            ) { Text("Agregar Cargo") }
            OutlinedButton(onClick = onVolverLista) { Text("Cancelar") }
        }
    }
}
