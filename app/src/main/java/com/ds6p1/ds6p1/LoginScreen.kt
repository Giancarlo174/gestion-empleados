package com.ds6p1.ds6p1

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.Email
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.People
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.focus.FocusDirection
import androidx.compose.ui.focus.FocusRequester
import androidx.compose.ui.focus.focusRequester
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.ds6p1.ds6p1.api.AuthResult
import com.ds6p1.ds6p1.ui.theme.Ds6p1Theme
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

@Composable
fun LoginScreen(
    modifier: Modifier = Modifier,
    onLoginAttempt: suspend (String, String) -> AuthResult
) {
    var startAnimation by remember { mutableStateOf(false) }
    val emailFocusRequester = remember { FocusRequester() }

    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var passwordVisible by remember { mutableStateOf(false) }
    var authResult by remember { mutableStateOf<AuthResult?>(null) }
    var isLoading by remember { mutableStateOf(false) }
    var showSuccessCheck by remember { mutableStateOf(false) }

    val coroutineScope = rememberCoroutineScope()
    val focusManager = LocalFocusManager.current

    // Fondo degradado oscuro moderno
    val backgroundBrush = Brush.verticalGradient(
        listOf(Color(0xFF22355C), Color(0xFF16213E))
    )
    val primaryColor = Color(0xFF5A7CF4)
    val secondaryColor = Color(0xFF3D5A98)

    LaunchedEffect(Unit) {
        startAnimation = true
        delay(200)
        emailFocusRequester.requestFocus()
    }

    fun tryLogin() {
        coroutineScope.launch {
            focusManager.clearFocus()
            if (!isLoading && email.isNotEmpty() && password.isNotEmpty()) {
                isLoading = true
                authResult = null
                try {
                    val result = onLoginAttempt(email.trim(), password)
                    authResult = result
                    when (result) {
                        is AuthResult.Success, is AuthResult.Admin, is AuthResult.Employee -> {
                            showSuccessCheck = true
                            delay(1000)
                        }
                        else -> {}
                    }
                } catch (e: Exception) {
                    authResult = AuthResult.Error("Error de conexión: ${e.message}")
                } finally {
                    isLoading = false
                }
            }
        }
    }

    Box(
        modifier = modifier
            .fillMaxSize()
            .background(brush = backgroundBrush)
    ) {
        // HEADER CON IMAGEN O SVG
        Box(
            Modifier
                .fillMaxWidth()
                .height(220.dp)
                .align(Alignment.TopCenter)
        ) {
            // Aquí pon tu imagen/SVG con painterResource o AsyncImage
            // Ejemplo: (puedes cambiarlo por tu imagen/logo de empresa)
            // Image(
            //     painter = painterResource(id = R.drawable.mi_logo),
            //     contentDescription = null,
            //     modifier = Modifier
            //         .size(120.dp)
            //         .align(Alignment.Center)
            // )
            Icon(
                imageVector = Icons.Default.People,
                contentDescription = "Logo",
                modifier = Modifier
                    .size(100.dp)
                    .align(Alignment.Center),
                tint = primaryColor
            )
        }

        // CARD BLANCA GRANDE, PEGADA LATERAL Y ABAJO, SOLO REDONDEADA ARRIBA
        Surface(
            modifier = Modifier
                .fillMaxWidth()
                .fillMaxHeight(0.80f)
                .align(Alignment.BottomCenter),
            shape = RoundedCornerShape(topStart = 36.dp, topEnd = 36.dp),
            color = Color.White,
            shadowElevation = 16.dp
        ) {
            Column(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(horizontal = 28.dp, vertical = 32.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                Text(
                    text = "Inicio Sesión",
                    style = MaterialTheme.typography.headlineMedium.copy(fontWeight = FontWeight.Bold, color = primaryColor),
                    modifier = Modifier.padding(bottom = 4.dp)
                )
                Text(
                    text = "Sistema de Gestión de Empleados",
                    style = MaterialTheme.typography.bodyLarge.copy(color = Color.Gray),
                    modifier = Modifier.padding(bottom = 20.dp)
                )

                OutlinedTextField(
                    value = email,
                    onValueChange = { email = it },
                    label = { Text("Correo Electrónico") },
                    singleLine = true,
                    leadingIcon = {
                        Icon(Icons.Default.Email, contentDescription = "Email", tint = secondaryColor)
                    },
                    modifier = Modifier
                        .fillMaxWidth()
                        .focusRequester(emailFocusRequester),
                    isError = authResult is AuthResult.Error,
                    shape = RoundedCornerShape(14.dp),
                    colors = TextFieldDefaults.colors(
                        focusedIndicatorColor = primaryColor,
                        unfocusedIndicatorColor = secondaryColor.copy(alpha = 0.5f)
                    )
                )

                Spacer(Modifier.height(18.dp))

                OutlinedTextField(
                    value = password,
                    onValueChange = { password = it },
                    label = { Text("Contraseña") },
                    singleLine = true,
                    leadingIcon = {
                        Icon(Icons.Default.Lock, contentDescription = "Password", tint = secondaryColor)
                    },
                    trailingIcon = {
                        IconButton(
                            onClick = { passwordVisible = !passwordVisible },
                            modifier = Modifier.size(40.dp)
                        ) {
                            Icon(
                                if (passwordVisible) Icons.Default.VisibilityOff else Icons.Default.Visibility,
                                contentDescription = if (passwordVisible) "Ocultar contraseña" else "Mostrar contraseña",
                                tint = secondaryColor,
                                modifier = Modifier.size(20.dp)
                            )
                        }
                    },
                    visualTransformation = if (passwordVisible) VisualTransformation.None else PasswordVisualTransformation(),
                    keyboardOptions = KeyboardOptions(
                        keyboardType = KeyboardType.Password,
                        imeAction = ImeAction.Done
                    ),
                    keyboardActions = KeyboardActions(
                        onDone = { tryLogin() }
                    ),
                    modifier = Modifier.fillMaxWidth(),
                    isError = authResult is AuthResult.Error,
                    shape = RoundedCornerShape(14.dp),
                    colors = TextFieldDefaults.colors(
                        focusedIndicatorColor = primaryColor,
                        unfocusedIndicatorColor = secondaryColor.copy(alpha = 0.5f)
                    )
                )

                AnimatedVisibility(
                    visible = authResult is AuthResult.Error,
                    enter = fadeIn(),
                    exit = fadeOut()
                ) {
                    val errorMessage = when (val result = authResult) {
                        is AuthResult.Error -> {
                            when {
                                result.message.contains("401", ignoreCase = true) -> "Correo o contraseña incorrectos."
                                result.message.contains("timeout", ignoreCase = true) -> "No se pudo conectar al servidor. Intenta más tarde."
                                result.message.contains("network", ignoreCase = true) -> "Sin conexión a internet."
                                result.message.contains("empty", ignoreCase = true) -> "Por favor ingresa tus credenciales."
                                else -> result.message
                            }
                        }
                        else -> "Error desconocido"
                    }
                    Text(
                        text = errorMessage,
                        color = MaterialTheme.colorScheme.error,
                        style = MaterialTheme.typography.bodySmall,
                        modifier = Modifier.padding(top = 8.dp, start = 4.dp)
                    )
                }

                // Mensaje de éxito opcional
                AnimatedVisibility(
                    visible = showSuccessCheck,
                    enter = fadeIn(),
                    exit = fadeOut()
                ) {
                    Text(
                        text = "¡Bienvenido!",
                        color = MaterialTheme.colorScheme.primary,
                        style = MaterialTheme.typography.bodyMedium,
                        modifier = Modifier.padding(top = 8.dp)
                    )
                }

                Spacer(Modifier.height(26.dp))

                Button(
                    onClick = { tryLogin() },
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(50.dp),
                    enabled = !isLoading && email.isNotEmpty() && password.isNotEmpty(),
                    shape = RoundedCornerShape(20.dp),
                    colors = ButtonDefaults.buttonColors(
                        containerColor = primaryColor, // El color normal
                        contentColor = Color.White, // El color del texto
                        disabledContainerColor = primaryColor, // MISMO color aunque esté deshabilitado
                        disabledContentColor = Color.White // MISMO color para el texto deshabilitado
                    )
                ) {
                    Box(
                        contentAlignment = Alignment.Center,
                        modifier = Modifier.fillMaxSize()
                    ) {
                        when {
                            isLoading -> {
                                CircularProgressIndicator(
                                    modifier = Modifier.size(24.dp),
                                    color = Color.White,
                                    strokeWidth = 2.dp
                                )
                            }
                            showSuccessCheck -> {
                                Icon(
                                    imageVector = Icons.Default.CheckCircle,
                                    contentDescription = "Success",
                                    tint = Color.White,
                                    modifier = Modifier.size(24.dp)
                                )
                            }
                            else -> {
                                Text(
                                    "Iniciar Sesión",
                                    fontSize = 16.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = Color.White
                                )
                            }
                        }
                    }
                }
            }
        }

        // Pie con el nombre del proyecto (opcional)
        AnimatedVisibility(
            visible = startAnimation,
            enter = fadeIn(animationSpec = tween(1000, delayMillis = 600)),
            modifier = Modifier
                .align(Alignment.BottomCenter)
                .padding(bottom = 24.dp)
        ) {
            Text(
                "Proyecto DS6",
                style = MaterialTheme.typography.bodyMedium,
                color = Color.Black.copy(alpha = 0.5f)
            )
        }
    }
}

// Util para gradiente en Button
fun Brush.toBrushColor(): Color = Color.Unspecified // Solo para compatibilidad, puedes quitar el gradiente del botón si no quieres líos


@Preview(showBackground = true)
@Composable
fun LoginScreenPreview() {
    Ds6p1Theme {
        LoginScreen(
            onLoginAttempt = { _, _ ->
                AuthResult.Error("Credenciales incorrectas")
            }
        )
    }
}
