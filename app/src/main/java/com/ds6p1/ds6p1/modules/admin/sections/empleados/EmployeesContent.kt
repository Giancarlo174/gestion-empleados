package com.ds6p1.ds6p1.modules.admin.sections.empleados

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.ui.theme.DataTable

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmpleadosScreen() {
    var mostrarCrearEmpleados by remember { mutableStateOf(false) }

    if (mostrarCrearEmpleados) {
        EmpleadoCreateScreen(
            onVolverLista = { mostrarCrearEmpleados = false }
        )
    } else {
        EmployeesContent(
            onCreate = { mostrarCrearEmpleados = true },
            onView = { }
        )
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmployeesContent(
    viewModel: EmployeesViewModel = viewModel(),
    onCreate: () -> Unit = {},
    onView: (String) -> Unit,
    onEdit: (String) -> Unit = {},
    onDelete: (String) -> Unit = {},
) {
    val uiState by viewModel.uiState.collectAsState()
    var search by remember { mutableStateOf("") }
    var filter by remember { mutableStateOf("all") }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(20.dp)
    ) {
        // Header minimalista
        Row(
            Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = "Empleados",
                fontSize = 24.sp,
                color = MaterialTheme.colorScheme.primary,
                style = MaterialTheme.typography.titleLarge
            )
            Button(
                onClick = onCreate,
                shape = MaterialTheme.shapes.medium,
                contentPadding = PaddingValues(horizontal = 16.dp, vertical = 8.dp)
            ) {
                Icon(Icons.Default.Add, contentDescription = "Añadir")
                Spacer(Modifier.width(4.dp))
                Text("Añadir Empleado")
            }
        }

        // Buscador y filtros
        OutlinedTextField(
            value = search,
            onValueChange = {
                search = it
                viewModel.loadEmployees(search, filter)
            },
            modifier = Modifier.fillMaxWidth(),
            placeholder = { Text("Buscar...") },
            leadingIcon = { Icon(Icons.Default.Search, contentDescription = null) },
            singleLine = true,
            colors = OutlinedTextFieldDefaults.colors(
                unfocusedContainerColor = Color.Transparent,
                focusedContainerColor = Color.Transparent
            )
        )

        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            listOf("all" to "Todos", "active" to "Activos", "inactive" to "Inactivos").forEach { (key, label) ->
                FilterChip(
                    selected = filter == key,
                    onClick = {
                        filter = key
                        viewModel.loadEmployees(search, filter)
                    },
                    label = { Text(label) },
                    shape = MaterialTheme.shapes.medium
                )
            }
        }

        // Tabla minimalista
        Box(
            Modifier
                .fillMaxWidth()
                .weight(1f)
        ) {
            when (uiState) {
                is EmployeesUiState.Loading -> Box(Modifier.fillMaxSize(), Alignment.Center) {
                    CircularProgressIndicator()
                }
                is EmployeesUiState.Error -> Column(
                    Modifier.fillMaxWidth(),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Icon(
                        Icons.Default.Error,
                        contentDescription = null,
                        tint = MaterialTheme.colorScheme.error,
                        modifier = Modifier.size(64.dp)
                    )
                    Text(
                        (uiState as EmployeesUiState.Error).message,
                        color = MaterialTheme.colorScheme.error
                    )
                    Spacer(Modifier.height(8.dp))
                    Button(
                        onClick = { viewModel.loadEmployees(search, filter) },
                        shape = MaterialTheme.shapes.medium
                    ) { Text("Reintentar") }
                }
                is EmployeesUiState.Success -> {
                    val list = (uiState as EmployeesUiState.Success).employees
                    if (list.isEmpty()) {
                        Box(Modifier.fillMaxSize(), Alignment.Center) {
                            Text("No hay empleados", color = MaterialTheme.colorScheme.onBackground)
                        }
                    } else {
                        DataTable(
                            columns = listOf("Cédula", "Nombre", "Apellido", "Departamento", "Estado"),
                            rows = list.map {
                                listOf(
                                    it.cedula,
                                    it.nombre,
                                    it.apellido,
                                    it.departamento,
                                    // Aquí usamos un emoji para estado: ✅ o ❌
                                    if (it.estado == 1) "Activo" else "Inactivo"
                                )
                            },
                            actions = { rowIdx ->
                                val emp = list[rowIdx]
                                IconButton(onClick = { onView(emp.cedula) }) {
                                    Icon(Icons.Default.Visibility, contentDescription = "Ver")
                                }
                                IconButton(onClick = { onEdit(emp.cedula) }) {
                                    Icon(Icons.Default.Edit, contentDescription = "Editar")
                                }
                                IconButton(onClick = { onDelete(emp.cedula) }) {
                                    Icon(Icons.Default.Delete, contentDescription = "Eliminar", tint = MaterialTheme.colorScheme.error)
                                }
                            }
                        )
                    }
                }
            }
        }
    }
}
