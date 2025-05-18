package com.ds6p1.ds6p1.modules.admin.sections.ajustes

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import com.ds6p1.ds6p1.modules.admin.models.AdminUser

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EditarAdminScreen(
    admin: AdminUser,
    onBack: () -> Unit,
    onGuardar: (String, String, String, String, String) -> Unit // cedula, correo, actualPass, nuevaPass, repetirPass
) {
    var cedula by remember { mutableStateOf(admin.cedula) }
    var correo by remember { mutableStateOf(admin.correo) }
    var actualPass by remember { mutableStateOf("") }
    var nuevaPass by remember { mutableStateOf("") }
    var repetirPass by remember { mutableStateOf("") }
    var error by remember { mutableStateOf<String?>(null) }

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
            Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .padding(24.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            OutlinedTextField(
                value = cedula,
                onValueChange = { cedula = it },
                label = { Text("Cédula") },
                singleLine = true
            )
            OutlinedTextField(
                value = correo,
                onValueChange = { correo = it },
                label = { Text("Correo Institucional *") },
                singleLine = true
            )
            OutlinedTextField(
                value = actualPass,
                onValueChange = { actualPass = it },
                label = { Text("Contraseña Actual") },
                singleLine = true,
                visualTransformation = PasswordVisualTransformation()
            )
            OutlinedTextField(
                value = nuevaPass,
                onValueChange = { nuevaPass = it },
                label = { Text("Nueva Contraseña (dejar en blanco para mantener la actual)") },
                singleLine = true,
                visualTransformation = PasswordVisualTransformation()
            )
            OutlinedTextField(
                value = repetirPass,
                onValueChange = { repetirPass = it },
                label = { Text("Confirmar Contraseña") },
                singleLine = true,
                visualTransformation = PasswordVisualTransformation()
            )
            error?.let { Text(it, color = MaterialTheme.colorScheme.error) }
            Button(
                onClick = {
                    if (nuevaPass.isNotBlank() && nuevaPass != repetirPass) {
                        error = "Las contraseñas nuevas no coinciden"
                    } else {
                        error = null
                        onGuardar(cedula, correo, actualPass, nuevaPass, repetirPass)
                    }
                },
                enabled = cedula.isNotBlank() && correo.isNotBlank() && actualPass.isNotBlank()
            ) {
                Text("Guardar")
            }
        }
    }
}
