package com.ds6p1.ds6p1.api

import retrofit2.http.GET
import retrofit2.http.Query
import com.ds6p1.ds6p1.api.Cargo

data class Cargo(
    val codigo: String,
    val nombre: String,
    val departamento: String
)

interface PositionsApi {
    @GET("admin/cargos_list.php")
    suspend fun getCargos(
        @Query("search") search: String? = null
    ): List<Cargo>

    @GET("admin/cargos_delete.php")
    suspend fun deleteCargo(
        @Query("codigo") codigo: String
    ): Any // Cambia por tu respuesta real si el PHP responde algo específico
}
