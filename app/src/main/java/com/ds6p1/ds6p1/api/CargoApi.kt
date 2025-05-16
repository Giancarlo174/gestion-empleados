package com.ds6p1.ds6p1.api

import retrofit2.http.GET
import retrofit2.http.Body
import retrofit2.http.POST

data class CargoNuevo(
    val dep_codigo: String,
    val codigo: String,
    val nombre: String
)

data class CargoResponse(
    val success: Boolean,
    val message: String
)

interface CargoApi {
    @POST("admin/cargo_create.php")
    suspend fun crearCargo(@Body cargo: CargoNuevo): CargoResponse

    @GET("admin/departamento_list.php")
    suspend fun getDepartamentos(): List<Department>
}
