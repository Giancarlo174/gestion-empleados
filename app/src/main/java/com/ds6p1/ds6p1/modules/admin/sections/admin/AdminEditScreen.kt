package com.ds6p1.ds6p1.modules.admin.sections.admin

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.modules.admin.sections.ajustes.AdminsViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AdminEditScreen(
    cedula: String,
    correo: String,
    onBack: () -> Unit,
    viewModel: AdminsViewModel = viewModel()
) {
    var cedulaInput by remember { mutableStateOf(cedula) }
    var correoInput by remember { mutableStateOf(correo) }
    var passActual by remember { mutableStateOf("") }
    var passNueva by remember { mutableStateOf("") }
    var passConfirm by remember { mutableStateOf("") }
    var showPassword by remember { mutableStateOf(false) }
    var showNewPassword by remember { mutableStateOf(false) }
    
    var showSuccessDialog by remember { mutableStateOf(false) }
    var showErrorDialog by remember { mutableStateOf(false) }
    var dialogMessage by remember { mutableStateOf("") }
    
    // Si hay un diálogo de éxito, regresar después de cerrar
    if (showSuccessDialog) {
        AlertDialog(
            onDismissRequest = { 
                showSuccessDialog = false
                onBack() 
            },
            title = { Text("Éxito") },
            text = { Text(dialogMessage) },
            confirmButton = {
                TextButton(onClick = { 
                    showSuccessDialog = false
                    onBack()
                }) {
                    Text("Aceptar")
                }
            }
        )
    }
    
    // Mostrar errores
    if (showErrorDialog) {
        AlertDialog(
            onDismissRequest = { showErrorDialog = false },
            title = { Text("Error") },
            text = { Text(dialogMessage) },
            confirmButton = {
                TextButton(onClick = { showErrorDialog = false }) {
                    Text("Aceptar")
                }
            }
        )
    }
    
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Editar Administrador") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Volver")
                    }
                }
            )
        }
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            OutlinedTextField(
                value = cedulaInput,
                onValueChange = { cedulaInput = it },
                label = { Text("Cédula") },
                modifier = Modifier.fillMaxWidth()
            )
            
            OutlinedTextField(
                value = correoInput,
                onValueChange = { correoInput = it },
                label = { Text("Correo Institucional") },
                modifier = Modifier.fillMaxWidth(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email)
            )
            
            OutlinedTextField(
                value = passActual,
                onValueChange = { passActual = it },
                label = { Text("Contraseña Actual *") },
                modifier = Modifier.fillMaxWidth(),
                visualTransformation = if (showPassword) VisualTransformation.None else PasswordVisualTransformation(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                trailingIcon = {
                    IconButton(onClick = { showPassword = !showPassword }) {
                        Icon(
                            if (showPassword) Icons.Default.VisibilityOff else Icons.Default.Visibility, 
                            contentDescription = if (showPassword) "Ocultar contraseña" else "Mostrar contraseña"
                        )
                    }
                }
            )
            
            Text(
                "Campos opcionales (dejar en blanco para no cambiar)",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            
            OutlinedTextField(
                value = passNueva,
                onValueChange = { passNueva = it },
                label = { Text("Nueva Contraseña") },
                modifier = Modifier.fillMaxWidth(),
                visualTransformation = if (showNewPassword) VisualTransformation.None else PasswordVisualTransformation(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                trailingIcon = {
                    IconButton(onClick = { showNewPassword = !showNewPassword }) {
                        Icon(
                            if (showNewPassword) Icons.Default.VisibilityOff else Icons.Default.Visibility, 
                            contentDescription = if (showNewPassword) "Ocultar contraseña" else "Mostrar contraseña"
                        )
                    }
                }
            )
            
            OutlinedTextField(
                value = passConfirm,
                onValueChange = { passConfirm = it },
                label = { Text("Confirmar Nueva Contraseña") },
                modifier = Modifier.fillMaxWidth(),
                visualTransformation = PasswordVisualTransformation(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password)
            )
            
            Spacer(modifier = Modifier.weight(1f))
            
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                Button(
                    onClick = {
                        // Validar
                        if (passActual.isBlank()) {
                            dialogMessage = "Debes ingresar la contraseña actual"
                            showErrorDialog = true
                            return@Button
                        }
                        
                        if (passNueva.isNotEmpty() && passNueva != passConfirm) {
                            dialogMessage = "Las contraseñas nuevas no coinciden"
                            showErrorDialog = true
                            return@Button
                        }
                        
                        // Si pasa validación, actualizar
                        viewModel.editarAdmin(
                            id = 1, // Importante: este ID debe venir del admin real
                            cedula = cedulaInput,
                            correo = correoInput,
                            contrasenaActual = passActual,
                            nuevaContrasena = if (passNueva.isNotEmpty()) passNueva else null,
                            onResult = { success, message ->
                                dialogMessage = message
                                if (success) {
                                    showSuccessDialog = true
                                } else {
                                    showErrorDialog = true
                                }
                            }
                        )
                    },
                    modifier = Modifier.weight(1f)
                ) {
                    Text("Guardar Cambios")
                }
                
                OutlinedButton(
                    onClick = onBack,
                    modifier = Modifier.weight(1f)
                ) {
                    Text("Cancelar")
                }
            }
        }
    }
}
