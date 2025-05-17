package com.ds6p1.ds6p1.modules.admin.sections.admin

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.*
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.ui.Alignment

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AdminCreateScreen(
    onVolverLista: () -> Unit,
    viewModel: AdminCreateViewModel = viewModel()
) {
    var cedula by remember { mutableStateOf("") }
    var contrasena by remember { mutableStateOf("") }
    var contrasena2 by remember { mutableStateOf("") }
    var correo by remember { mutableStateOf("") }
    var showPassword by remember { mutableStateOf(false) }
    var showPassword2 by remember { mutableStateOf(false) }

    val state by viewModel.state.collectAsState()

    when (state) {
        is AdminCreateState.Loading -> LinearProgressIndicator(Modifier.fillMaxWidth())
        is AdminCreateState.Success -> {
            AlertDialog(
                onDismissRequest = { viewModel.resetState(); onVolverLista() },
                title = { Text("¡Éxito!") },
                text = { Text((state as AdminCreateState.Success).message) },
                confirmButton = { 
                    TextButton(
                        onClick = { viewModel.resetState(); onVolverLista() },
                        contentPadding = PaddingValues(horizontal = 16.dp, vertical = 6.dp),
                        modifier = Modifier.height(32.dp)
                    ) { 
                        Text(
                            "Aceptar",
                            style = MaterialTheme.typography.labelLarge,
                            color = MaterialTheme.colorScheme.primary
                        ) 
                    } 
                }
            )
        }
        is AdminCreateState.Error -> {
            AlertDialog(
                onDismissRequest = { viewModel.resetState() },
                title = { Text("Error") },
                text = { Text((state as AdminCreateState.Error).message) },
                confirmButton = { TextButton(onClick = { viewModel.resetState() }) { Text("OK") } }
            )
        }
        else -> {}
    }

    Column(
        Modifier
            .padding(16.dp)
            .fillMaxWidth()
    ) {
        // Encabezado con flecha de regreso
        Row(
            modifier = Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically
        ) {
            IconButton(
                onClick = onVolverLista,
                modifier = Modifier.padding(end = 8.dp)
            ) {
                Icon(
                    Icons.Default.ArrowBack,
                    contentDescription = "Regresar",
                    tint = MaterialTheme.colorScheme.primary
                )
            }
            Text("Agregar Nuevo Administrador", style = MaterialTheme.typography.headlineSmall)
        }
        Spacer(Modifier.height(18.dp))

        OutlinedTextField(
            value = cedula,
            onValueChange = { cedula = it },
            label = { Text("Cédula *") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true
        )
        Spacer(Modifier.height(8.dp))
        OutlinedTextField(
            value = contrasena,
            onValueChange = { contrasena = it },
            label = { Text("Contraseña *") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true,
            visualTransformation = if (showPassword) VisualTransformation.None else PasswordVisualTransformation(),
            trailingIcon = {
                val image = if (showPassword) Icons.Default.VisibilityOff else Icons.Default.Visibility
                IconButton(onClick = { showPassword = !showPassword }) {
                    Icon(image, contentDescription = if (showPassword) "Ocultar" else "Mostrar")
                }
            }
        )
        Spacer(Modifier.height(8.dp))
        OutlinedTextField(
            value = contrasena2,
            onValueChange = { contrasena2 = it },
            label = { Text("Confirmar Contraseña *") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true,
            visualTransformation = if (showPassword2) VisualTransformation.None else PasswordVisualTransformation(),
            trailingIcon = {
                val image = if (showPassword2) Icons.Default.VisibilityOff else Icons.Default.Visibility
                IconButton(onClick = { showPassword2 = !showPassword2 }) {
                    Icon(image, contentDescription = if (showPassword2) "Ocultar" else "Mostrar")
                }
            }
        )
        Spacer(Modifier.height(8.dp))
        OutlinedTextField(
            value = correo,
            onValueChange = { correo = it },
            label = { Text("Correo Institucional *") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true,
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email)
        )
        Spacer(Modifier.height(24.dp))
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(16.dp)) {
            Button(
                onClick = {
                    viewModel.crearAdmin(cedula, contrasena, contrasena2, correo)
                }
            ) { Text("Guardar Administrador") }
            OutlinedButton(onClick = onVolverLista) { Text("Cancelar") }
        }
    }
}
