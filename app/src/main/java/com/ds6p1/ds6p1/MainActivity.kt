package com.ds6p1.ds6p1

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Scaffold
import androidx.compose.ui.Modifier
import com.ds6p1.ds6p1.api.AuthResult
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.LoginRequest
import com.ds6p1.ds6p1.modules.AppNavigation
import com.ds6p1.ds6p1.ui.theme.Ds6p1Theme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContent {
            Ds6p1Theme {
                Scaffold(modifier = Modifier.fillMaxSize()) { innerPadding ->
                    AppNavigation(
                        modifier = Modifier.padding(innerPadding),
                        authenticateUser = { email, password ->
                            authenticateUser(email, password)
                        }
                    )
                }
            }
        }
    }

    private suspend fun authenticateUser(email: String, password: String): AuthResult {
        return try {
            val response = ApiClient.apiService.login(LoginRequest(email, password))
            if (response.success && response.data != null) {
                val userType = response.data.user_type
                val apiKey = response.data.api_key
                val cedula = response.data.cedula

                if (userType == "admin") {
                    AuthResult.Admin(cedula, apiKey)
                } else {
                    AuthResult.Employee(cedula, apiKey)
                }
            } else {
                AuthResult.Error(response.message ?: "Error en la autenticación")
            }
        } catch (e: Exception) {
            AuthResult.Error("Error de conexión: ${e.message}")
        }
    }
}
