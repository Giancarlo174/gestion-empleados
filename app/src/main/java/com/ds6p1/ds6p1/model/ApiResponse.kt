package com.ds6p1.ds6p1.model

import com.google.gson.JsonElement
import com.google.gson.annotations.SerializedName

/**
 * Clase genérica para todas las respuestas de la API
 * Permite manejar respuestas exitosas y errores de manera uniforme
 */
data class ApiResponse<T>(
    @SerializedName("success")
    val success: Boolean = false,
    
    @SerializedName("message")
    val message: String? = null,
    
    // Campo genérico para datos específicos (user, employees, etc.)
    // Puede ser null en caso de error
    @SerializedName("data")
    val data: T? = null,
    
    // Para respuestas de autenticación
    @SerializedName("authenticated")
    val authenticated: Boolean? = null,
    
    // Para respuestas de login
    @SerializedName("session_token")
    val sessionToken: String? = null,
    
    @SerializedName("role")
    val role: String? = null,
    
    // Para capturar cualquier campo adicional no previsto
    @SerializedName("user")
    val user: User? = null
)

/**
 * User data structure returned from API
 */
data class User(
    @SerializedName("id")
    val id: String? = null,
    
    @SerializedName("cedula")
    val cedula: String? = null,
    
    @SerializedName("correo_institucional")
    val correoInstitucional: String? = null,
    
    @SerializedName("nombre")
    val nombre: String? = null
)
