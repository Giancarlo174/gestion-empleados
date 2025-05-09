package com.ds6p1.ds6p1.model

import com.google.gson.annotations.SerializedName

data class LoginRequest(
    @SerializedName("correo_institucional")
    val email: String,
    
    @SerializedName("contraseña")
    val password: String
)
