package com.ds6p1.ds6p1.api

import retrofit2.http.GET
import retrofit2.http.Query

data class Provincia(val codigo: String, val nombre: String)
data class Distrito(val codigo: String, val nombre: String, val codigoProvincia: String)
data class Corregimiento(val codigo: String, val nombre: String, val codigoProvincia: String, val codigoDistrito: String)
data class Departamento(val codigo: String, val nombre: String)
data class Cargos(val codigo: String, val nombre: String, val depCodigo: String)
data class Nacionalidad(val codigo: String, val pais: String)

interface OptionsApi {
    @GET("admin/provincia_list.php")
    suspend fun getProvincias(): List<Provincia>

    @GET("admin/distrito_list.php")
    suspend fun getDistritos(@Query("provincia") provincia: String): List<Distrito>

    @GET("admin/corregimiento_list.php")
    suspend fun getCorregimientos(
        @Query("provincia") provincia: String,
        @Query("distrito") distrito: String
    ): List<Corregimiento>

    @GET("admin/nacionalidad_list.php")
    suspend fun getNacionalidades(): List<Nacionalidad>

    @GET("admin/departamento_list.php")
    suspend fun getDepartamentos(): List<Departamento>

    @GET("admin/cargo_list.php")
    suspend fun getCargos(@Query("departamento") departamento: String): List<Cargo>
}
