package com.ds6p1.ds6p1.api

import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

object RetrofitInstance {
    private const val BASE_URL = "http://10.0.2.2/ds6p1/backend/"

    val employeesApi: EmployeesApi by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(EmployeesApi::class.java)
    }
}
