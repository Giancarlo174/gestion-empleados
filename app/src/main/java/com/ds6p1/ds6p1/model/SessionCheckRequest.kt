package com.ds6p1.ds6p1.model

import com.google.gson.annotations.SerializedName

data class SessionCheckRequest(
    @SerializedName("session_token")
    val sessionToken: String
)
