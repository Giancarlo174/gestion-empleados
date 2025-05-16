package com.ds6p1.ds6p1.api

/**
 * Clase sellada que representa los posibles resultados de autenticación
 */
sealed class AuthResult {
    /**
     * Usuario administrador autenticado correctamente
     */
    data class Admin(val cedula: String, val apiKey: String) : AuthResult()
    
    /**
     * Usuario empleado autenticado correctamente
     */
    data class Employee(val cedula: String, val apiKey: String) : AuthResult()
    
    /**
     * Autenticación exitosa genérica
     */
    object Success : AuthResult()
    
    /**
     * Error en la autenticación
     */
    data class Error(val message: String) : AuthResult()
}

/**
 * Datos básicos del usuario
 */
data class UserData(
    val id: String,
    val name: String,
    val email: String
)