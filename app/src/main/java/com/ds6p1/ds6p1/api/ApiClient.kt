package com.ds6p1.ds6p1.api

import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import com.ds6p1.ds6p1.api.OptionsApi

object ApiClient {
    private const val BASE_URL = "http://10.0.2.2/ds6p1/backend/"

    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }

    val departmentApi: DepartmentApi by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(DepartmentApi::class.java)
    }

    val positionsApi: PositionsApi by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(PositionsApi::class.java)
    }

    val adminsApi: AdminsApi by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(AdminsApi::class.java)
    }

    val employeesApi: EmployeesApi by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(EmployeesApi::class.java)
    }

    val optionsApi: OptionsApi by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(OptionsApi::class.java)
    }

    val cargoApi: CargoApi by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(CargoApi::class.java)
    }

    val adminApi: AdminApi by lazy {
        retrofit2.Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(retrofit2.converter.gson.GsonConverterFactory.create())
            .build()
            .create(AdminApi::class.java)
    }


}
