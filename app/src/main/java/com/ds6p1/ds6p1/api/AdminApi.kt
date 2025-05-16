package com.ds6p1.ds6p1.api

import retrofit2.http.Body
import retrofit2.http.POST

data class NuevoAdmin(
    val cedula: String,
    val contrasena: String,
    val correo_institucional: String
)

data class AdminResponse(
    val success: Boolean,
    val message: String
)

interface AdminApi {
    @POST("admin/admin_create.php")
    suspend fun crearAdmin(@Body admin: NuevoAdmin): AdminResponse
}
