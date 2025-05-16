package com.ds6p1.ds6p1.api

import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import com.ds6p1.ds6p1.api.Provincia

// Modelo de login
data class LoginRequest(
    val email: String,
    val password: String
)

// Respuesta del login
data class LoginResponse(
    val success: Boolean,
    val data: LoginData?,
    val message: String?
)

data class LoginData(
    val user_type: String,
    val api_key: String,
    val cedula: String
)


interface ApiService {
    @POST("auth/login.php")
    suspend fun login(@Body request: LoginRequest): LoginResponse

    @GET("admin/employees_list.php")
    suspend fun getEmployees(): List<Employee>

}
