package com.ds6p1.ds6p1.modules.admin.sections.cargos

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Clear
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.outlined.Clear
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
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

    var idCargoAEliminar by remember { mutableStateOf<String?>(null) }
    var mostrarDialogoEliminar by remember { mutableStateOf(false) }
    
    // Variables para el Snackbar
    val snackbarHostState = remember { SnackbarHostState() }
    var mensajeSnackbar by remember { mutableStateOf<String?>(null) }
    var esError by remember { mutableStateOf(false) }
    
    // Efecto para mostrar el Snackbar cuando hay un mensaje
    LaunchedEffect(mensajeSnackbar) {
        mensajeSnackbar?.let { mensaje ->
            snackbarHostState.showSnackbar(
                message = mensaje,
                duration = SnackbarDuration.Short
            )
            // Limpiar el mensaje después de mostrarlo
            mensajeSnackbar = null
        }
    }

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
                viewModel.loadCargos(search)
            },
            modifier = Modifier.fillMaxWidth(),
            placeholder = { Text("Buscar por código o nombre") },
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
                        viewModel.loadCargos("")
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
                                val cargo = cargos[rowIdx]
                                IconButton(onClick = { onEdit(cargos[rowIdx].codigo) }) {
                                    Icon(Icons.Default.Edit, contentDescription = "Editar")
                                }
                                IconButton(onClick = {
                                    idCargoAEliminar = cargo.codigo
                                    mostrarDialogoEliminar = true
                                }) {
                                    Icon(Icons.Default.Delete, contentDescription = "Eliminar", tint = MaterialTheme.colorScheme.error)
                                }
                            }
                        )
                        if (mostrarDialogoEliminar && idCargoAEliminar != null) {
                            AlertDialog(
                                onDismissRequest = { mostrarDialogoEliminar = false },
                                title = { Text("¿Eliminar cargo?") },
                                text = { Text("¿Seguro que quieres eliminar este cargo?") },
                                confirmButton = {
                                    TextButton(onClick = {
                                        mostrarDialogoEliminar = false
                                        viewModel.deleteCargo(idCargoAEliminar!!) { exito, mensaje ->
                                            mensajeSnackbar = mensaje
                                            esError = !exito
                                        }
                                    }) { Text("Eliminar") }
                                },
                                dismissButton = {
                                    TextButton(onClick = { mostrarDialogoEliminar = false }) { Text("Cancelar") }
                                }
                            )
                        }
                    }
                }
            }
            
            // Snackbar para mostrar mensajes (éxito o error)
            SnackbarHost(
                hostState = snackbarHostState,
                modifier = Modifier
                    .align(Alignment.BottomCenter)
                    .padding(bottom = 16.dp)
            ) { snackbarData ->
                Snackbar(
                    containerColor = if (esError) MaterialTheme.colorScheme.errorContainer else MaterialTheme.colorScheme.primaryContainer,
                    contentColor = if (esError) MaterialTheme.colorScheme.onErrorContainer else MaterialTheme.colorScheme.onPrimaryContainer
                ) {
                    Text(snackbarData.visuals.message)
                }
            }
        }
    }
}
