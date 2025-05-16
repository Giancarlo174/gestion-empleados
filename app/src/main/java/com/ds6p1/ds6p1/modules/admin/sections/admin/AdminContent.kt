package com.ds6p1.ds6p1.modules.admin.sections.admins

import androidx.compose.foundation.background
import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.modules.admin.sections.admin.AdminCreateScreen
import com.ds6p1.ds6p1.ui.theme.DataTable

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AdminsScreen() {
    var mostrarCrearAdmins by remember { mutableStateOf(false) }

    if (mostrarCrearAdmins) {
        AdminCreateScreen(
            onVolverLista = { mostrarCrearAdmins = false }
        )
    } else {
        AdminContent(
            onCreate = { mostrarCrearAdmins = true }
        )
    }
}
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AdminContent(
    onCreate: () -> Unit = {},
    viewModel: AdminViewModel = viewModel(),
    onEdit: (String) -> Unit = {},
    onDelete: (String) -> Unit = {},
) {
    val uiState by viewModel.uiState.collectAsState()
    var search by remember { mutableStateOf("") }

    Column(
        Modifier
            .fillMaxSize()
            .padding(24.dp),
        verticalArrangement = Arrangement.spacedBy(20.dp)
    ) {
        // Header con botón flotante a la derecha
        Row(
            Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = "Administradores",
                fontSize = 26.sp,
                color = MaterialTheme.colorScheme.primary
            )
            Button(
                onClick = onCreate,
                shape = MaterialTheme.shapes.medium
            ) {
                Icon(Icons.Default.Add, contentDescription = "Añadir")
                Spacer(Modifier.width(6.dp))
                Text("Añadir")
            }
        }

        // Buscador
        Row(
            Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            OutlinedTextField(
                value = search,
                onValueChange = {
                    search = it
                    viewModel.loadAdmins(search)
                },
                modifier = Modifier.weight(1f),
                placeholder = { Text("Buscar por cédula o correo") },
                singleLine = true
            )
            Button(
                onClick = { viewModel.loadAdmins(search) },
                shape = MaterialTheme.shapes.medium
            ) {
                Text("Buscar")
            }
        }

        // Tabla de administradores
        Box(
            Modifier
                .fillMaxWidth()
                .weight(1f)
        ) {
            when (uiState) {
                is AdminUiState.Loading -> Box(Modifier.fillMaxSize(), Alignment.Center) {
                    CircularProgressIndicator()
                }
                is AdminUiState.Error -> Box(Modifier.fillMaxSize(), Alignment.Center) {
                    Text(
                        (uiState as AdminUiState.Error).message,
                        color = MaterialTheme.colorScheme.error,
                        style = MaterialTheme.typography.titleMedium
                    )
                }
                is AdminUiState.Success -> {
                    val admins = (uiState as AdminUiState.Success).admins
                    if (admins.isEmpty()) {
                        Box(Modifier.fillMaxSize(), Alignment.Center) {
                            Text(
                                "No hay administradores registrados",
                                color = MaterialTheme.colorScheme.outline,
                                style = MaterialTheme.typography.titleMedium
                            )
                        }
                    } else {
                        DataTable(
                            columns = listOf("ID", "Cédula", "Correo institucional"),
                            rows = admins.map { listOf(it.id.toString(), it.cedula, it.correo) },
                            actions = { rowIdx ->
                                IconButton(onClick = { onEdit(admins[rowIdx].cedula) }) {
                                    Icon(Icons.Default.Edit, contentDescription = "Editar")
                                }
                                IconButton(onClick = { onDelete(admins[rowIdx].cedula) }) {
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
