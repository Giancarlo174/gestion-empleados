package com.ds6p1.ds6p1.modules.admin.sections.cargos

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.ui.theme.DataTable

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CargosScreen() {
    var mostrarCrearCargo by remember { mutableStateOf(false) }

    if (mostrarCrearCargo) {
        CargoCreateScreen(
            onVolverLista = { mostrarCrearCargo = false }
        )
    } else {
        CargosContent(
            onCreate = { mostrarCrearCargo = true }
        )
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CargosContent(
    viewModel: CargosViewModel = viewModel(),
    onEdit: (String) -> Unit = {},
    onDelete: (String) -> Unit = {},
    onCreate: () -> Unit = {}
) {
    val uiState by viewModel.uiState.collectAsState()
    var search by remember { mutableStateOf("") }

    Column(
        Modifier
            .fillMaxSize()
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        // Header
        Row(
            Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                "Lista de Cargos",
                fontSize = 24.sp,
                color = MaterialTheme.colorScheme.primary
            )
            Button(
                onClick = onCreate,
                shape = MaterialTheme.shapes.medium
            ) {
                Text("+ Nuevo Cargo")
            }
        }

        // Buscador
        Row(
            Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            OutlinedTextField(
                value = search,
                onValueChange = {
                    search = it
                    viewModel.loadCargos(search)
                },
                modifier = Modifier.weight(1f),
                placeholder = { Text("Buscar por código o nombre") },
                singleLine = true
            )
            Button(onClick = { viewModel.loadCargos(search) }) {
                Text("Buscar")
            }
        }

        // Tabla
        Box(
            Modifier
                .fillMaxWidth()
                .weight(1f)
        ) {
            when (uiState) {
                is CargosUiState.Loading -> Box(Modifier.fillMaxSize(), Alignment.Center) {
                    CircularProgressIndicator()
                }
                is CargosUiState.Error -> Box(Modifier.fillMaxSize(), Alignment.Center) {
                    Text(
                        (uiState as CargosUiState.Error).message,
                        color = MaterialTheme.colorScheme.error
                    )
                }
                is CargosUiState.Success -> {
                    val cargos = (uiState as CargosUiState.Success).cargos
                    if (cargos.isEmpty()) {
                        Box(Modifier.fillMaxSize(), Alignment.Center) {
                            Text("No hay cargos registrados")
                        }
                    } else {
                        DataTable(
                            columns = listOf("Código", "Nombre del Cargo", "Departamento"),
                            rows = cargos.map { listOf(it.codigo, it.nombre, it.departamento) },
                            actions = { rowIdx ->
                                IconButton(onClick = { onEdit(cargos[rowIdx].codigo) }) {
                                    Icon(Icons.Default.Edit, contentDescription = "Editar")
                                }
                                IconButton(onClick = { onDelete(cargos[rowIdx].codigo) }) {
                                    Icon(
                                        Icons.Default.Delete,
                                        contentDescription = "Eliminar",
                                        tint = MaterialTheme.colorScheme.error
                                    )
                                }
                            }
                        )
                    }
                }
            }
        }
    }
}
