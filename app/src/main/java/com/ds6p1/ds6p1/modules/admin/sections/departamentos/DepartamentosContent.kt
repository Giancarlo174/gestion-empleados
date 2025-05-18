package com.ds6p1.ds6p1.modules.admin.sections.departamentos

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.Clear
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material.icons.filled.Error
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.outlined.Clear
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.ui.theme.DataTable

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DepartamentosScreen(
    onNuevo: () -> Unit = {}
) {
    DepartmentContent(
        onCreate = onNuevo
    )
}


@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DepartmentContent(
    viewModel: DepartmentViewModel = viewModel(),
    onEdit: (String) -> Unit = {},
    onCreate: () -> Unit = {}
) {
    val uiState by viewModel.uiState.collectAsState()
    var search by remember { mutableStateOf("") }
    var codigoAEliminar by remember { mutableStateOf<String?>(null) }
    var mostrarDialogoEliminar by remember { mutableStateOf(false) }
    var mensajeEliminacion by remember { mutableStateOf<String?>(null) }
    var exitoEliminacion by remember { mutableStateOf<Boolean?>(null) }

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
                shape = RoundedCornerShape(8.dp),
                contentPadding = PaddingValues(horizontal = 12.dp, vertical = 8.dp),
                colors = ButtonDefaults.buttonColors(
                    containerColor = MaterialTheme.colorScheme.primary.copy(alpha = 0.9f),
                ),
                modifier = Modifier.height(36.dp)
            ) {
                Icon(
                    Icons.Default.Add,
                    contentDescription = null,
                    modifier = Modifier.size(16.dp)
                )
                Spacer(Modifier.width(6.dp))
                Text(
                    "Nuevo",
                    style = MaterialTheme.typography.bodyMedium.copy(
                        fontWeight = FontWeight.Medium
                    )
                )
            }
        }

        // Buscador
        OutlinedTextField(
            value = search,
            onValueChange = {
                search = it
                viewModel.loadDepartments(search)
            },
            modifier = Modifier.fillMaxWidth(),
            placeholder = { Text("Buscar por nombre o código") },
            singleLine = true,
            leadingIcon = {
                Icon(
                    Icons.Default.Search,
                    contentDescription = "Buscar",
                    tint = MaterialTheme.colorScheme.primary
                )
            },
            trailingIcon = {
                if (search.isNotEmpty()) {
                    IconButton(onClick = {
                        search = ""
                        viewModel.loadDepartments("")
                    }) {
                        Icon(
                            Icons.Default.Clear,
                            contentDescription = "Limpiar",
                            tint = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f)
                        )
                    }
                }
            },
            colors = OutlinedTextFieldDefaults.colors(
                unfocusedContainerColor = Color.Transparent,
                focusedContainerColor = Color.Transparent
            )
        )

        // Tabla y diálogos
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

                                IconButton(onClick = {
                                    codigoAEliminar = list[rowIdx].codigo
                                    mostrarDialogoEliminar = true
                                }) {
                                    Icon(
                                        Icons.Default.Delete,
                                        contentDescription = "Eliminar",
                                        tint = MaterialTheme.colorScheme.error
                                    )
                                }
                            }
                        )

                        // Diálogo de confirmación para eliminar
                        if (mostrarDialogoEliminar && codigoAEliminar != null) {
                            AlertDialog(
                                onDismissRequest = { mostrarDialogoEliminar = false },
                                title = { Text("¿Eliminar departamento?") },
                                text = { Text("¿Seguro que quieres eliminar este departamento?") },
                                confirmButton = {
                                    TextButton(onClick = {
                                        mostrarDialogoEliminar = false
                                        viewModel.deleteDepartamento(codigoAEliminar!!) { exito, mensaje ->
                                            mensajeEliminacion = mensaje
                                            exitoEliminacion = exito
                                        }
                                    }) { Text("Eliminar") }
                                },
                                dismissButton = {
                                    TextButton(onClick = { mostrarDialogoEliminar = false }) { Text("Cancelar") }
                                }
                            )
                        }

                        // Diálogo de resultado (éxito o error)
                        if (mensajeEliminacion != null && exitoEliminacion != null) {
                            val isError = exitoEliminacion == false
                            AlertDialog(
                                onDismissRequest = {
                                    mensajeEliminacion = null
                                    exitoEliminacion = null
                                },
                                title = { Text(if (isError) "No se pudo eliminar" else "Eliminado", color = if (isError) MaterialTheme.colorScheme.error else MaterialTheme.colorScheme.primary) },
                                icon = {
                                    if (isError)
                                        Icon(Icons.Default.Error, contentDescription = "Error", tint = MaterialTheme.colorScheme.error)
                                    else
                                        Icon(Icons.Default.Check, contentDescription = "Éxito", tint = MaterialTheme.colorScheme.primary)
                                },
                                text = { Text(mensajeEliminacion!!) },
                                confirmButton = {
                                    TextButton(onClick = {
                                        mensajeEliminacion = null
                                        exitoEliminacion = null
                                    }) { Text("OK") }
                                }
                            )
                        }
                    }
                }
            }
        }
    }
}