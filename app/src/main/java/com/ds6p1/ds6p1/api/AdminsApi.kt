package com.ds6p1.ds6p1.api

import retrofit2.http.GET

import com.ds6p1.ds6p1.modules.admin.models.AdminUser

interface AdminsApi {
    @GET("admin/admin_list.php")
    suspend fun getAdmins(): List<AdminUser>
}
