package com.ds6p1.ds6p1.modules.admin

/**
 * Clase para almacenar la información del administrador
 */
data class AdminInfo(
    val cedula: String,
    val apiKey: String = "",
    val correoInstitucional: String = ""
)
