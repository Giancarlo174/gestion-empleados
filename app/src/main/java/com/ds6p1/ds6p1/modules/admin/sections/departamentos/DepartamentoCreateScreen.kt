package com.ds6p1.ds6p1.modules.admin.sections.departamentos

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DepartamentoCreateScreen(
    onVolverLista: () -> Unit,
    viewModel: DepartamentoCreateViewModel = viewModel()
) {

    var nombre by remember { mutableStateOf("") }

    val estado by viewModel.state.collectAsState()

    when (estado) {
        is DepartamentoCreateState.Loading -> LinearProgressIndicator(Modifier.fillMaxWidth())
        is DepartamentoCreateState.Success -> {
            val codigo = (estado as DepartamentoCreateState.Success).codigo
            AlertDialog(
                onDismissRequest = {
                    viewModel.resetState()
                    onVolverLista()
                },
                title = { Text("¡Departamento agregado!") },
                text = { Text("El código asignado es: $codigo\n\n${(estado as DepartamentoCreateState.Success).message}") },
                confirmButton = {
                    TextButton(onClick = {
                        viewModel.resetState()
                        onVolverLista()
                    }) { Text("OK") }
                }
            )
        }
        is DepartamentoCreateState.Error -> {
            AlertDialog(
                onDismissRequest = { viewModel.resetState() },
                title = { Text("Error") },
                text = { Text((estado as DepartamentoCreateState.Error).message) },
                confirmButton = { TextButton(onClick = { viewModel.resetState() }) { Text("OK") } }
            )
        }
        else -> {}
    }

    Column(
        Modifier
            .padding(20.dp)
            .fillMaxWidth()
    ) {
        Text("Agregar Departamento", style = MaterialTheme.typography.headlineSmall)
        Spacer(Modifier.height(18.dp))
        OutlinedTextField(
            value = nombre,
            onValueChange = { nombre = it },
            label = { Text("Nombre de Departamento *") },
            modifier = Modifier.fillMaxWidth(),
            keyboardOptions = KeyboardOptions(imeAction = ImeAction.Done)
        )

        Spacer(Modifier.height(24.dp))
        Row(horizontalArrangement = Arrangement.spacedBy(16.dp)) {
            Button(
                onClick = {
                    if (nombre.isNotBlank()) {
                        viewModel.crearDepartamento(nombre)
                    }
                }
            ) { Text("Agregar Departamento") }
            OutlinedButton(onClick = onVolverLista) { Text("Cancelar") }
        }
    }
}
