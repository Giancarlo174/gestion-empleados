package com.ds6p1.ds6p1.modules.admin.sections.empleados

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.ds6p1.ds6p1.ui.theme.DataTable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import com.ds6p1.ds6p1.api.EmpleadoDetalle

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmpleadosScreen(
    navController: NavController
) {
    var mostrarCrearEmpleados by remember { mutableStateOf(false) }

    if (mostrarCrearEmpleados) {
        EmpleadoCreateScreen(
            onVolverLista = { mostrarCrearEmpleados = false }
        )
    } else {
        EmployeesContent(
            onCreate = { mostrarCrearEmpleados = true },
            onView = { cedula -> navController.navigate("empleado_detalle/$cedula") }
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
    var cedulaAEliminar by remember { mutableStateOf<String?>(null) }
    var mostrarDialogoEliminar by remember { mutableStateOf(false) }
    var mensajeEliminacion by remember { mutableStateOf<String?>(null) }
    var empleadoAEditar by remember { mutableStateOf<EmpleadoDetalle?>(null) }
    var errorMsg by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()
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
                shape = RoundedCornerShape(8.dp),
                contentPadding = PaddingValues(horizontal = 12.dp, vertical = 8.dp),
                colors = ButtonDefaults.buttonColors(
                    containerColor = MaterialTheme.colorScheme.primary.copy(alpha = 0.9f),
                ),
                modifier = Modifier.height(36.dp)
            ) {
                Icon(
                    Icons.Default.Add,
                    contentDescription = "Añadir",
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

        // Buscador y filtros
        OutlinedTextField(
            value = search,
            onValueChange = {
                search = it
                viewModel.loadEmployees(search, filter)
            },
            modifier = Modifier.fillMaxWidth(),
            placeholder = { Text("Buscar...") },
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
                        viewModel.loadEmployees("", filter)
                    }) {
                        Icon(
                            Icons.Default.Clear,
                            contentDescription = "Limpiar",
                            tint = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.6f)
                        )
                    }
                }
            },
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
                                    if (it.estado == 1) "Activo" else "Inactivo"
                                )
                            },
                            actions = { rowIdx ->
                                val emp = list[rowIdx]
                                Row {
                                    IconButton(onClick = { onView(emp.cedula) }) {
                                        Icon(Icons.Default.Visibility, contentDescription = "Ver")
                                    }

                                    IconButton(onClick = { onEdit(emp.cedula) }) {
                                        Icon(Icons.Default.Edit, contentDescription = "Editar")
                                    }
                                    IconButton(onClick = {
                                        cedulaAEliminar = emp.cedula
                                        mostrarDialogoEliminar = true
                                    }) {
                                        Icon(Icons.Default.Delete, contentDescription = "Eliminar", tint = MaterialTheme.colorScheme.error)
                                    }
                                }
                            }
                        )
                    }
                }
            }
        }

        if (mostrarDialogoEliminar && cedulaAEliminar != null) {
            AlertDialog(
                onDismissRequest = { mostrarDialogoEliminar = false },
                title = { Text("¿Eliminar empleado?") },
                text = { Text("¿Seguro que quieres eliminar a este empleado?") },
                confirmButton = {
                    TextButton(onClick = {
                        mostrarDialogoEliminar = false
                        viewModel.deleteEmployee(cedulaAEliminar!!) { exito, mensaje ->
                            mensajeEliminacion = mensaje
                        }
                    }) { Text("Eliminar") }
                },
                dismissButton = {
                    TextButton(onClick = { mostrarDialogoEliminar = false }) { Text("Cancelar") }
                }
            )
        }
        if (mensajeEliminacion != null) {
            AlertDialog(
                onDismissRequest = { mensajeEliminacion = null },
                title = { Text("Resultado") },
                text = { Text(mensajeEliminacion!!) },
                confirmButton = {
                    TextButton(onClick = { mensajeEliminacion = null }) { Text("OK") }
                }
            )
        }
    }
}
