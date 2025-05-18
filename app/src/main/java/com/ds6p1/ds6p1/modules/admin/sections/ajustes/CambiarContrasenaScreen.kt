package com.ds6p1.ds6p1.modules.admin.sections.ajustes

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import com.ds6p1.ds6p1.api.ApiClient

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CambiarContrasenaScreen(
    onBack: () -> Unit,
    cedula: String,
    viewModel: CambiarPassViewModel = androidx.lifecycle.viewmodel.compose.viewModel(
        factory = CambiarPassViewModelFactory(cedula)
    )
) {
    var actual by remember { mutableStateOf("") }
    var nueva by remember { mutableStateOf("") }
    var repetir by remember { mutableStateOf("") }
    var error by remember { mutableStateOf<String?>(null) }
    var success by remember { mutableStateOf<String?>(null) }

    var showActual by remember { mutableStateOf(false) }
    var showNueva by remember { mutableStateOf(false) }
    var showRepetir by remember { mutableStateOf(false) }

    val scope = rememberCoroutineScope()
    val uiState by viewModel.uiState.collectAsState()

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Cambiar contraseña") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.Filled.ArrowBack, contentDescription = "Volver")
                    }
                }
            )
        }
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .padding(24.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            OutlinedTextField(
                value = actual,
                onValueChange = { actual = it },
                label = { Text("Contraseña actual") },
                singleLine = true,
                visualTransformation = if (showActual) VisualTransformation.None else PasswordVisualTransformation(),
                trailingIcon = {
                    IconButton(onClick = { showActual = !showActual }) {
                        Icon(
                            if (showActual) Icons.Default.VisibilityOff else Icons.Default.Visibility,
                            contentDescription = if (showActual) "Ocultar contraseña" else "Mostrar contraseña"
                        )
                    }
                },
                modifier = Modifier.fillMaxWidth()
            )
            OutlinedTextField(
                value = nueva,
                onValueChange = { nueva = it },
                label = { Text("Nueva contraseña") },
                singleLine = true,
                visualTransformation = if (showNueva) VisualTransformation.None else PasswordVisualTransformation(),
                trailingIcon = {
                    IconButton(onClick = { showNueva = !showNueva }) {
                        Icon(
                            if (showNueva) Icons.Default.VisibilityOff else Icons.Default.Visibility,
                            contentDescription = if (showNueva) "Ocultar contraseña" else "Mostrar contraseña"
                        )
                    }
                },
                modifier = Modifier.fillMaxWidth()
            )
            OutlinedTextField(
                value = repetir,
                onValueChange = { repetir = it },
                label = { Text("Repetir contraseña") },
                singleLine = true,
                visualTransformation = if (showRepetir) VisualTransformation.None else PasswordVisualTransformation(),
                trailingIcon = {
                    IconButton(onClick = { showRepetir = !showRepetir }) {
                        Icon(
                            if (showRepetir) Icons.Default.VisibilityOff else Icons.Default.Visibility,
                            contentDescription = if (showRepetir) "Ocultar contraseña" else "Mostrar contraseña"
                        )
                    }
                },
                modifier = Modifier.fillMaxWidth()
            )

            error?.let { Text(it, color = MaterialTheme.colorScheme.error) }
            success?.let { Text(it, color = MaterialTheme.colorScheme.primary) }

            when (uiState) {
                is CambioPasswordState.Loading -> LinearProgressIndicator(modifier = Modifier.fillMaxWidth())
                is CambioPasswordState.Success -> {
                    Text(
                        (uiState as CambioPasswordState.Success).message,
                        color = MaterialTheme.colorScheme.primary
                    )
                }
                is CambioPasswordState.Error -> {
                    Text(
                        (uiState as CambioPasswordState.Error).message,
                        color = MaterialTheme.colorScheme.error
                    )
                }
                else -> {}
            }

            Spacer(modifier = Modifier.height(16.dp))

            Button(
                onClick = {
                    error = null
                    success = null

                    if (actual.isBlank()) {
                        error = "Ingresa tu contraseña actual"
                    } else if (nueva.length < 6) {
                        error = "La contraseña debe tener al menos 6 caracteres"
                    } else if (nueva != repetir) {
                        error = "Las contraseñas no coinciden"
                    } else {
                        scope.launch {
                            viewModel.cambiarPassword(actual, nueva)
                        }
                    }
                },
                enabled = actual.isNotBlank() && nueva.isNotBlank() && repetir.isNotBlank(),
                modifier = Modifier.fillMaxWidth()
            ) {
                Text("Cambiar contraseña")
            }
        }
    }
}

class CambiarPassViewModel(private val cedula: String) : ViewModel() {
    private val _uiState = MutableStateFlow<CambioPasswordState>(CambioPasswordState.Idle)
    val uiState: StateFlow<CambioPasswordState> = _uiState.asStateFlow()

    fun cambiarPassword(actual: String, nueva: String) {
        viewModelScope.launch {
            _uiState.value = CambioPasswordState.Loading
            try {
                val response = ApiClient.adminApi.cambiarPassword(
                    cedula = cedula,
                    passwordActual = actual,
                    passwordNueva = nueva
                )
                if (response.success) {
                    _uiState.value = CambioPasswordState.Success(response.message)
                } else {
                    _uiState.value = CambioPasswordState.Error(response.message)
                }
            } catch (e: Exception) {
                _uiState.value = CambioPasswordState.Error("Error de conexión: ${e.message}")
            }
        }
    }
}

class CambiarPassViewModelFactory(private val cedula: String) : androidx.lifecycle.ViewModelProvider.Factory {
    override fun <T : ViewModel> create(modelClass: Class<T>): T {
        return CambiarPassViewModel(cedula) as T
    }
}

sealed class CambioPasswordState {
    object Idle : CambioPasswordState()
    object Loading : CambioPasswordState()
    data class Success(val message: String) : CambioPasswordState()
    data class Error(val message: String) : CambioPasswordState()
}
