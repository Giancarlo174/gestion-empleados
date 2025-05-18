package com.ds6p1.ds6p1.api

data class EmpleadoDetalle(
    val cedula: String,
    val prefijo: String?,
    val tomo: String?,
    val asiento: String?,
    val nombre1: String,
    val nombre2: String?,
    val apellido1: String,
    val apellido2: String?,
    val apellidoc: String?,
    val genero: Int,
    val estado_civil: Int,
    val tipo_sangre: String?,
    val usa_ac: Int,
    val f_nacimiento: String?,
    val celular: String?,
    val telefono: String?,
    val correo: String?,
    val provincia: String?,
    val provincia_nombre: String?,
    val distrito: String?,
    val distrito_nombre: String?,
    val corregimiento: String?,
    val corregimiento_nombre: String?,
    val calle: String?,
    val casa: String?,
    val comunidad: String?,
    val nacionalidad: String?,
    val nacionalidad_nombre: String?,
    val f_contra: String?,
    val cargo: String?,
    val nombre_cargo: String?,
    val departamento: String?,
    val nombre_departamento: String?,
    val estado: Int
) {
    fun getDireccionFormateada(): String {
        return listOfNotNull(
            provincia_nombre?.takeIf { it.isNotBlank() },
            distrito_nombre?.takeIf { it.isNotBlank() },
            corregimiento_nombre?.takeIf { it.isNotBlank() },
            calle?.takeIf { it.isNotBlank() },
            casa?.takeIf { it.isNotBlank() },
            comunidad?.takeIf { it.isNotBlank() }
        ).joinToString(", ").ifEmpty { "No especificada" }
    }
}