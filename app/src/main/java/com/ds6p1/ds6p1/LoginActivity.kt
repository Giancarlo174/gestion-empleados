package com.ds6p1.ds6p1

import android.content.Intent
import android.os.Bundle
import android.util.Log
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Email
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.focus.FocusDirection
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.lifecycleScope
import com.ds6p1.ds6p1.model.LoginRequest
import com.ds6p1.ds6p1.network.ApiClient
import com.ds6p1.ds6p1.ui.theme.Ds6p1Theme
import com.google.gson.JsonElement
import kotlinx.coroutines.launch
import retrofit2.HttpException
import java.io.IOException
import android.content.Context

class LoginActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            Ds6p1Theme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    LoginScreen()
                }
            }
        }
    }

    @Composable
    fun LoginScreen() {
        var email by remember { mutableStateOf("") }
        var password by remember { mutableStateOf("") }
        var isPasswordVisible by remember { mutableStateOf(false) }
        var isLoading by remember { mutableStateOf(false) }
        var emailError by remember { mutableStateOf<String?>(null) }
        var passwordError by remember { mutableStateOf<String?>(null) }
        
        val focusManager = LocalFocusManager.current

        // Define functions inline as variables
        val login = { emailValue: String, passwordValue: String ->
            isLoading = true
            
            lifecycleScope.launch {
                try {
                    // First, authenticate the user using our new ApiClient
                    val loginResponse = ApiClient.apiService.login(LoginRequest(emailValue, passwordValue))
                    
                    if (loginResponse.isSuccessful) {
                        val loginResult = loginResponse.body()
                        if (loginResult != null && loginResult.success) {
                            // Login successful
                            Toast.makeText(this@LoginActivity, "Inicio de sesión exitoso", Toast.LENGTH_SHORT).show()
                            
                            // Determine which activity to open based on user role
                            val intent = when (loginResult.role) {
                                "ADMIN" -> Intent(this@LoginActivity, MainActivity::class.java)
                                "EMPLOYEE" -> Intent(this@LoginActivity, EmployeeActivity::class.java)
                                else -> Intent(this@LoginActivity, MainActivity::class.java) // Default fallback
                            }
                            
                            // Pass user data
                            intent.putExtra("USER_EMAIL", emailValue)
                            intent.putExtra("USER_NAME", loginResult.user?.nombre ?: "Usuario")
                            intent.putExtra("USER_ROLE", loginResult.role)
                            intent.putExtra("USER_CEDULA", loginResult.user?.cedula)
                            
                            // Store session data
                            saveSessionData(loginResult.sessionToken, loginResult.role, null)
                            
                            startActivity(intent)
                            finish()  // Close login activity
                        } else {
                            // Login failed but with valid response
                            val message = loginResult?.message ?: "Error de autenticación"
                            showError(message)
                        }
                    } else {
                        // HTTP error with error body
                        val errorMsg = try {
                            loginResponse.errorBody()?.string() ?: "Error desconocido"
                        } catch (e: Exception) {
                            "Error de comunicación con el servidor"
                        }
                        showError("Error: $errorMsg")
                    }
                } catch (e: IOException) {
                    // Network error
                    showError("Error de conexión: Verifique su conexión a internet o que el servidor XAMPP esté activo")
                    e.printStackTrace()
                } catch (e: Exception) {
                    // Other errors
                    showError("Error: ${e.message}")
                    e.printStackTrace()
                } finally {
                    isLoading = false
                }
            }
        }
        
        val validateAndLogin = {
            val trimmedEmail = email.trim()
            val trimmedPassword = password.trim()
            
            var hasError = false
            
            if (trimmedEmail.isEmpty()) {
                emailError = "El correo es requerido"
                hasError = true
            }
            
            if (trimmedPassword.isEmpty()) {
                passwordError = "La contraseña es requerida"
                hasError = true
            }
            
            if (!hasError) {
                login(trimmedEmail, trimmedPassword)
            }
        }

        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(MaterialTheme.colorScheme.background)
        ) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 24.dp)
                    .padding(top = 48.dp, bottom = 32.dp)
                    .align(Alignment.Center),
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.spacedBy(24.dp)
            ) {
                // Title
                Text(
                    text = "Sistema de Gestión de Empleados",
                    fontSize = 24.sp,
                    color = MaterialTheme.colorScheme.primary,
                    fontWeight = FontWeight.Bold,
                    textAlign = TextAlign.Center,
                    modifier = Modifier.padding(bottom = 32.dp)
                )

                // Email field
                OutlinedTextField(
                    value = email,
                    onValueChange = { 
                        email = it
                        emailError = null 
                    },
                    label = { Text("Correo Institucional") },
                    leadingIcon = {
                        Icon(
                            imageVector = Icons.Default.Email,
                            contentDescription = "Email Icon"
                        )
                    },
                    isError = emailError != null,
                    supportingText = emailError?.let { 
                        { Text(it) } 
                    },
                    singleLine = true,
                    keyboardOptions = KeyboardOptions(
                        keyboardType = KeyboardType.Email,
                        imeAction = ImeAction.Next
                    ),
                    keyboardActions = KeyboardActions(
                        onNext = { focusManager.moveFocus(FocusDirection.Down) }
                    ),
                    modifier = Modifier.fillMaxWidth()
                )

                // Password field
                OutlinedTextField(
                    value = password,
                    onValueChange = { 
                        password = it
                        passwordError = null 
                    },
                    label = { Text("Contraseña") },
                    leadingIcon = {
                        Icon(
                            imageVector = Icons.Default.Lock,
                            contentDescription = "Password Icon"
                        )
                    },
                    trailingIcon = {
                        IconButton(onClick = { isPasswordVisible = !isPasswordVisible }) {
                            Icon(
                                imageVector = if (isPasswordVisible) Icons.Default.VisibilityOff else Icons.Default.Visibility,
                                contentDescription = if (isPasswordVisible) "Hide Password" else "Show Password"
                            )
                        }
                    },
                    visualTransformation = if (isPasswordVisible) VisualTransformation.None else PasswordVisualTransformation(),
                    isError = passwordError != null,
                    supportingText = passwordError?.let { 
                        { Text(it) } 
                    },
                    singleLine = true,
                    keyboardOptions = KeyboardOptions(
                        keyboardType = KeyboardType.Password,
                        imeAction = ImeAction.Done
                    ),
                    keyboardActions = KeyboardActions(
                        onDone = { 
                            focusManager.clearFocus()
                            validateAndLogin()
                        }
                    ),
                    modifier = Modifier.fillMaxWidth()
                )

                Spacer(modifier = Modifier.height(16.dp))

                // Login button
                Button(
                    onClick = { validateAndLogin() },
                    enabled = !isLoading,
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(56.dp)
                        .clip(RoundedCornerShape(12.dp)),
                    colors = ButtonDefaults.buttonColors(
                        containerColor = MaterialTheme.colorScheme.primary
                    )
                ) {
                    if (isLoading) {
                        CircularProgressIndicator(
                            color = MaterialTheme.colorScheme.onPrimary,
                            modifier = Modifier.size(24.dp)
                        )
                    } else {
                        Text(
                            text = "Iniciar Sesión",
                            fontSize = 16.sp,
                            fontWeight = FontWeight.Bold
                        )
                    }
                }
            }
            
            // Loading overlay
            AnimatedVisibility(
                visible = isLoading,
                enter = fadeIn(),
                exit = fadeOut(),
                modifier = Modifier.fillMaxSize()
            ) {
                if (isLoading) {
                    Box(
                        modifier = Modifier
                            .fillMaxSize()
                            .background(Color.Black.copy(alpha = 0.3f)),
                        contentAlignment = Alignment.Center
                    ) {
                        CircularProgressIndicator(
                            color = MaterialTheme.colorScheme.primary
                        )
                    }
                }
            }
        }
    }

    // Simplified error display function that works with Compose
    private fun showError(message: String) {
        Toast.makeText(this, message, Toast.LENGTH_LONG).show()
    }

    // Simplified session data storage
    private fun saveSessionData(token: String?, role: String?, userData: JsonElement?) {
        val prefs = getSharedPreferences("auth_prefs", Context.MODE_PRIVATE)
        prefs.edit().apply {
            putString("session_token", token)
            putString("role", role)
            putString("user_data", userData?.toString())
            apply()
        }
    }
}
