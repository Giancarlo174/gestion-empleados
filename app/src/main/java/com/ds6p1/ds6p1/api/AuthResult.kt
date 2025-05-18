package com.ds6p1.ds6p1.api


sealed class AuthResult {

    data class Admin(val cedula: String, val apiKey: String) : AuthResult()
    
    data class Employee(val cedula: String, val apiKey: String) : AuthResult()
    
    object Success : AuthResult()

    data class Error(val message: String) : AuthResult()
}

data class UserData(
    val id: String,
    val name: String,
    val email: String
)