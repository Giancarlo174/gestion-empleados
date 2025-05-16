package com.ds6p1.ds6p1.modules.admin.models

/**
 * Modelo de datos para información del administrador
 * Basado en la estructura de la tabla 'usuarios' de la base de datos
 */
data class AdminInfo(
    val id: Long? = null,
    val cedula: String,
    val apiKey: String,
    val correoInstitucional: String? = null
)
