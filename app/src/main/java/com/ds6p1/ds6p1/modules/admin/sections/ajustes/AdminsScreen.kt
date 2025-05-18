package com.ds6p1.ds6p1.modules.admin.sections.ajustes

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Add
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.modules.admin.models.AdminUser
import kotlinx.coroutines.delay

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AdminsScreen(
    onBack: () -> Unit,
    onEdit: (AdminUser) -> Unit,
    onDelete: (AdminUser) -> Unit = {},
    onNuevo: () -> Unit = {},
    viewModel: AdminsViewModel = viewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    var mensaje by remember { mutableStateOf<String?>(null) }
    var isError by remember { mutableStateOf(false) }
    var showDeleteDialog by remember { mutableStateOf<AdminUser?>(null) }

    // Cargar datos cuando se inicia la pantalla
    LaunchedEffect(Unit) {
        viewModel.loadAdmins()
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Administradores") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Volver")
                    }
                },
                actions = {
                    // Botón de Nuevo administrador
                    IconButton(onClick = onNuevo) {
                        Icon(Icons.Default.Add, contentDescription = "Agregar administrador")
                    }
                }
            )
        }
    ) { innerPadding ->
        Box(Modifier.fillMaxSize().padding(innerPadding)) {
            when (uiState) {
                is AdminsUiState.Loading -> CircularProgressIndicator(Modifier.align(Alignment.Center))
                is AdminsUiState.Error -> Text(
                    (uiState as AdminsUiState.Error).message,
                    color = MaterialTheme.colorScheme.error,
                    modifier = Modifier.align(Alignment.Center)
                )
                is AdminsUiState.Success -> {
                    LazyColumn(Modifier.fillMaxSize()) {
                        items((uiState as AdminsUiState.Success).admins) { admin ->
                            Card(
                                Modifier
                                    .fillMaxWidth()
                                    .padding(vertical = 4.dp, horizontal = 12.dp)
                            ) {
                                ListItem(
                                    headlineContent = { Text(admin.cedula) },
                                    supportingContent = { Text(admin.correo) },
                                    leadingContent = { Icon(Icons.Default.Person, null) },
                                    trailingContent = {
                                        Row {
                                            IconButton(onClick = { onEdit(admin) }) {
                                                Icon(Icons.Default.Edit, contentDescription = "Editar")
                                            }
                                            IconButton(onClick = { showDeleteDialog = admin }) {
                                                Icon(Icons.Default.Delete, contentDescription = "Eliminar", tint = MaterialTheme.colorScheme.error)
                                            }
                                        }
                                    }
                                )
                            }
                        }
                    }
                }
            }

            // Snackbar para mensajes
            mensaje?.let {
                Snackbar(
                    modifier = Modifier.align(Alignment.BottomCenter).padding(16.dp),
                    containerColor = if (isError) MaterialTheme.colorScheme.errorContainer else MaterialTheme.colorScheme.primaryContainer
                ) {
                    Text(it)
                    LaunchedEffect(it) {
                        delay(2000)
                        mensaje = null
                    }
                }
            }
        }
    }

    // Diálogo de confirmación de eliminación
    showDeleteDialog?.let { admin ->
        AlertDialog(
            onDismissRequest = { showDeleteDialog = null },
            title = { Text("Eliminar Administrador") },
            text = { Text("¿Seguro que deseas eliminar a ${admin.cedula}?") },
            confirmButton = {
                TextButton(onClick = {
                    // Llamar al ViewModel para eliminar
                    viewModel.eliminarAdmin(admin) { success, msg ->
                        mensaje = msg
                        isError = !success
                    }
                    showDeleteDialog = null
                }) { Text("Eliminar") }
            },
            dismissButton = {
                TextButton(onClick = { showDeleteDialog = null }) { Text("Cancelar") }
            }
        )
    }
}
