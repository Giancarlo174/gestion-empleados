package com.ds6p1.ds6p1.api

data class DashboardStatsResponse(
    val success: Boolean,
    val message: String? = null,
    val total_employees: Int = 0,
    val active_employees: Int = 0,
    val inactive_employees: Int = 0,
    val departments: List<DepartmentStats> = emptyList()
)

data class DepartmentStats(
    val codigo: String,
    val nombre: String,
    val employee_count: Int,
    val percentage: Double
)
