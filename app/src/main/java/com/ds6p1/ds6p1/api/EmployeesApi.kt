package com.ds6p1.ds6p1.api

import retrofit2.http.GET
import retrofit2.http.Query
import retrofit2.http.Body
import retrofit2.http.POST
import retrofit2.http.FormUrlEncoded
import retrofit2.http.Field

// Modelo de empleado existente (para listar)
data class Employee(
    val cedula: String,
    val nombre: String,
    val apellido: String,
    val departamento: String,
    val estado: Int,
    val estadoTexto: String
)

// Modelo para crear empleado
data class NuevoEmpleado(
    val cedula: String,
    val prefijo: String,
    val tomo: String,
    val asiento: String,
    val nombre1: String,
    val nombre2: String?,
    val apellido1: String,
    val apellido2: String?,
    val apellidoc: String?,
    val genero: Int,
    val estado_civil: Int,
    val tipo_sangre: String,
    val usa_ac: Int,
    val f_nacimiento: String,
    val celular: String,
    val telefono: String?,
    val correo: String,
    val provincia: String,
    val distrito: String,
    val corregimiento: String,
    val calle: String?,
    val casa: String?,
    val comunidad: String?,
    val nacionalidad: String,
    val f_contra: String,
    val cargo: String,
    val departamento: String,
    val estado: Int
)

// Modelo para la respuesta de la API
data class ApiResponse(
    val success: Boolean,
    val message: String
)

interface EmployeesApi {
    @GET("admin/employees_list.php")
    suspend fun getEmployees(
        @Query("search") search: String? = null,
        @Query("filter") filter: String? = null
    ): List<Employee>

    @POST("admin/empleados_create.php")
    suspend fun createEmployee(@Body empleado: NuevoEmpleado): ApiResponse

    @POST("admin/delete_employee.php")
    @FormUrlEncoded
    suspend fun deleteEmployee(
        @Field("cedula") cedula: String
    ): ApiResponse

    @GET("admin/empleado_detail.php")
    suspend fun getEmpleadoDetalle(@Query("cedula") cedula: String): EmpleadoDetalleResponse

    @POST("admin/empleado_update.php")
    suspend fun updateEmployee(@Body empleado: NuevoEmpleado): ApiResponse

}

data class EmpleadoDetalleResponse(
    val success: Boolean,
    val empleado: EmpleadoDetalle?
)




