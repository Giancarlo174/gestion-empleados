package com.ds6p1.ds6p1.api

import retrofit2.http.GET
import retrofit2.http.Query
import retrofit2.http.Body
import retrofit2.http.POST
import retrofit2.http.FormUrlEncoded
import retrofit2.http.Field

data class Department(
    val codigo: String,
    val nombre: String
)
data class DepartamentoNuevo(
    val nombre: String
)
data class DepartamentoResponse(
    val success: Boolean,
    val message: String,
    val codigo: String? = null
)

interface DepartmentApi {
    @POST("admin/departamento_create.php")
    suspend fun crearDepartamento(@Body departamento: DepartamentoNuevo): DepartamentoResponse

    @GET("admin/departments_list.php")
    suspend fun getDepartments(
        @Query("search") search: String? = null
    ): List<Department>


    @POST("admin/delete_departamento.php")
    @FormUrlEncoded
    suspend fun deleteDepartamento(
        @Field("codigo") codigo: String
    ): DepartamentoResponse

}
