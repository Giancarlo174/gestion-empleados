package com.ds6p1.ds6p1.api

import okhttp3.ResponseBody
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.POST
import retrofit2.http.GET

interface ApiService {
    @POST("login.php")
    suspend fun login(@Body loginRequest: LoginRequest): Response<LoginResponse>
    
    @POST("get_user_role.php")
    suspend fun getUserRole(@Body request: UserRoleRequest): Response<UserRoleResponse>
    
    @GET("get_dashboard_stats.php")
    suspend fun getDashboardStats(): Response<DashboardStatsResponse>
}

data class LoginRequest(
    val correo_institucional: String,
    val contraseña: String
)

data class LoginResponse(
    val success: Boolean,
    val message: String,
    val role: String? = null,
    val user: UserData? = null
)

data class UserData(
    val id: Int? = null,
    val cedula: String,
    val correo_institucional: String,
    val nombre: String? = null
)

data class UserRoleRequest(
    val email: String
)

data class UserRoleResponse(
    val success: Boolean,
    val role: String,
    val message: String? = null
)

enum class UserRole {
    ADMIN, EMPLOYEE, UNKNOWN
}
