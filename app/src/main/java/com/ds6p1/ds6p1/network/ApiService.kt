package com.ds6p1.ds6p1.network

import com.ds6p1.ds6p1.model.ApiResponse
import com.ds6p1.ds6p1.model.LoginRequest
import com.ds6p1.ds6p1.model.SessionCheckRequest
import retrofit2.Call
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST

/**
 * Interface defining all API endpoints for the DS6P12 application.
 * Each method corresponds to an HTTP request to a specific endpoint.
 */
interface ApiService {
    
    /**
     * Login endpoint - authenticates user and returns session token
     */
    @POST("login/login.php")
    suspend fun login(@Body request: LoginRequest): Response<ApiResponse<Any>>
    
    /**
     * Verify if the current session is valid
     */
    @POST("verify_session.php") 
    suspend fun verifySession(@Body request: SessionCheckRequest): Response<ApiResponse<Any>>
    
    /**
     * Logout endpoint - invalidates the current session
     */
    @GET("login/logout.php")
    suspend fun logout(): Response<ApiResponse<Any>>
    
    /**
     * Get all employees from the system
     */
    @GET("empleados/get_employees.php")
    suspend fun getEmployees(): Response<ApiResponse<List<Any>>>
    
    /**
     * Get all departments
     */
    @GET("departamentos/get_departamentos.php")
    suspend fun getDepartments(): Response<ApiResponse<List<Any>>>
    
    /**
     * Get all job titles (cargos)
     */
    @GET("cargos/get_cargos.php")
    suspend fun getCargos(): Response<ApiResponse<List<Any>>>
}
