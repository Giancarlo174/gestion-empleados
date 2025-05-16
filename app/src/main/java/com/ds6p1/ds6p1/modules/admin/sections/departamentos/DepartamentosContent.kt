package com.ds6p1.ds6p1.modules.admin.sections.departamentos

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.ui.theme.DataTable

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DepartamentosScreen() {
    var mostrarCrearDepartamento by remember { mutableStateOf(false) }

    if (mostrarCrearDepartamento) {
        DepartamentoCreateScreen(
            onVolverLista = { mostrarCrearDepartamento = false }
        )
    } else {
        DepartmentContent(
            onCreate = { mostrarCrearDepartamento = true }
        )
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DepartmentContent(
    viewModel: DepartmentViewModel = viewModel(),
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
        // Header principal
        Row(
            Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                "Lista de Departamentos",
                style = MaterialTheme.typography.titleLarge,
                color = MaterialTheme.colorScheme.primary
            )
            Button(
                onClick = onCreate,
                shape = MaterialTheme.shapes.medium
            ) {
                Text("+ Nuevo Departamento")
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
                    viewModel.loadDepartments(search)
                },
                modifier = Modifier.weight(1f),
                placeholder = { Text("Buscar por nombre o código") },
                singleLine = true
            )
            Button(onClick = { viewModel.loadDepartments(search) }) {
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
                is DepartmentUiState.Loading -> Box(Modifier.fillMaxSize(), Alignment.Center) {
                    CircularProgressIndicator()
                }
                is DepartmentUiState.Error -> Box(Modifier.fillMaxSize(), Alignment.Center) {
                    Text(
                        (uiState as DepartmentUiState.Error).message,
                        color = MaterialTheme.colorScheme.error
                    )
                }
                is DepartmentUiState.Success -> {
                    val list = (uiState as DepartmentUiState.Success).departments
                    if (list.isEmpty()) {
                        Box(Modifier.fillMaxSize(), Alignment.Center) {
                            Text("No hay departamentos")
                        }
                    } else {
                        DataTable(
                            columns = listOf("Código", "Nombre de Departamento"),
                            rows = list.map { listOf(it.codigo, it.nombre) },
                            actions = { rowIdx ->
                                IconButton(onClick = { onEdit(list[rowIdx].codigo) }) {
                                    Icon(Icons.Default.Edit, contentDescription = "Editar")
                                }
                                IconButton(onClick = { onDelete(list[rowIdx].codigo) }) {
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
