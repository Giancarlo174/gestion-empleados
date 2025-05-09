package com.ds6p1.ds6p1.network

import com.google.gson.GsonBuilder
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object ApiClient {
    
    private const val BASE_URL = "http://10.0.2.2/ds6p12/backend/"
    
    // Crear cliente OkHttp con interceptores
    private val okHttpClient = OkHttpClient.Builder()
        .addInterceptor(HttpLoggingInterceptor().apply { 
            level = HttpLoggingInterceptor.Level.BODY 
        })
        .addInterceptor(ErrorInterceptor()) // Nuestro interceptor personalizado
        .connectTimeout(30, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .writeTimeout(30, TimeUnit.SECONDS)
        .build()
    
    // Configurar Gson para ser más tolerante con errores
    private val gson = GsonBuilder()
        .setLenient()
        .create()
    
    // Crear cliente Retrofit
    private val retrofit = Retrofit.Builder()
        .baseUrl(BASE_URL)
        .client(okHttpClient)
        .addConverterFactory(GsonConverterFactory.create(gson))
        .build()
    
    // Crear instancia de la API
    val apiService: ApiService = retrofit.create(ApiService::class.java)
}
