package com.ds6p1.ds6p1.api

import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST

data class LoginRequest(
    val email: String,
    val password: String
)

data class LoginResponse(
    val success: Boolean,
    val data: LoginData?,
    val message: String?
)

data class LoginData(
    val user_type: String,
    val api_key: String,
    val cedula: String,
    val correo_institucional: String
)

interface ApiService {
    @POST("auth/login.php")
    suspend fun login(@Body request: LoginRequest): LoginResponse

    @GET("admin/employees_list.php")
    suspend fun getEmployees(): List<Employee>
}
