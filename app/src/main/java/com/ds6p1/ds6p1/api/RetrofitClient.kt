package com.ds6p1.ds6p1.api

import com.google.gson.GsonBuilder
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object RetrofitClient {
    // Para emulador Android, usa 10.0.2.2 para acceder a localhost
    // Para dispositivo físico, usa la IP real de tu computadora
    private const val BASE_URL = "http://10.0.2.2/ds6p12/backend/"
    // Si usas dispositivo físico, descomenta esta línea y usa tu IP real:
    // private const val BASE_URL = "http://192.168.X.X/ds6p12/backend/"

    private val okHttpClient: OkHttpClient by lazy {
        val logging = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY  // Muestra detalles completos en el log
        }
        
        OkHttpClient.Builder()
            .addInterceptor(logging)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .writeTimeout(30, TimeUnit.SECONDS)
            .build()
    }

    // Configuramos Gson para ser más tolerante con JSON mal formado
    private val gson = GsonBuilder()
        .setLenient()
        .create()

    val apiService: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create(gson))
            .build()
            .create(ApiService::class.java)
    }
}
