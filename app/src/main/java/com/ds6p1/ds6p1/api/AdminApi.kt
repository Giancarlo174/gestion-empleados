package com.ds6p1.ds6p1.api

import retrofit2.http.Body
import retrofit2.http.POST
import retrofit2.http.Field

data class NuevoAdmin(
    val cedula: String,
    val contrasena: String,
    val correo_institucional: String
)

data class AdminResponse(
    val success: Boolean,
    val message: String
)

data class EditAdminBody(
    val id: Int,
    val cedula: String,
    val correo_institucional: String,
    val contrasena_actual: String,
    val nueva_contrasena: String? = null
)

data class DeleteAdminBody(
    val id: Int
)

data class GenericResponse(
    val success: Boolean,
    val message: String
)

interface AdminApi {
    @POST("admin/admin_create.php")
    suspend fun crearAdmin(@Body admin: NuevoAdmin): AdminResponse

    @POST("admin/admin_edit.php")
    suspend fun editarAdmin(@Body body: EditAdminBody): AdminResponse

    @POST("admin/admin_delete.php")
    suspend fun eliminarAdmin(@Body body: DeleteAdminBody): AdminResponse

    @POST("admin/change_password.php")
    suspend fun cambiarPassword(
        @Field("cedula") cedula: String,
        @Field("password_actual") passwordActual: String,
        @Field("password_nueva") passwordNueva: String
    ): GenericResponse
}
