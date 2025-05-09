package com.ds6p1.ds6p1.network

import android.util.Log
import com.google.gson.JsonSyntaxException
import com.google.gson.JsonParser
import okhttp3.Interceptor
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.Response
import okhttp3.ResponseBody.Companion.toResponseBody
import org.json.JSONObject
import java.io.IOException

/**
 * Custom interceptor to handle non-JSON responses from the server
 * Converts any non-JSON response to a properly formatted JSON error response
 */
class ErrorInterceptor : Interceptor {
    
    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request()
        
        try {
            val response = chain.proceed(request)
            
            // If it's not a successful response, handle it as error
            if (!response.isSuccessful) {
                return handleErrorResponse(response)
            }
            
            // Get the response body and content type
            val responseBody = response.body?.string() ?: ""
            val contentType = response.header("Content-Type") ?: ""
            
            // Check if the response should be JSON but isn't valid
            if (contentType.contains("application/json", ignoreCase = true) || 
                responseBody.trim().startsWith("{") || 
                responseBody.trim().startsWith("[")) {
                
                try {
                    // Try to parse as JSON to check if valid
                    JsonParser.parseString(responseBody)
                    
                    // If we get here, it's valid JSON, rebuild response
                    val newBody = responseBody.toResponseBody("application/json".toMediaTypeOrNull())
                    return response.newBuilder().body(newBody).build()
                } catch (e: JsonSyntaxException) {
                    // Not valid JSON, convert to JSON error response
                    Log.e("API_ERROR", "Invalid JSON response: $responseBody")
                    
                    val errorJson = JSONObject().apply {
                        put("success", false)
                        put("message", "Error in response format: $responseBody")
                    }
                    
                    val newBody = errorJson.toString().toResponseBody("application/json".toMediaTypeOrNull())
                    return response.newBuilder()
                        .body(newBody)
                        .build()
                }
            }
            
            // If not JSON, proceed with original response
            val newBody = responseBody.toResponseBody(response.body?.contentType())
            return response.newBuilder().body(newBody).build()
            
        } catch (e: IOException) {
            // Network error, create a friendly JSON response
            val errorJson = JSONObject().apply {
                put("success", false)
                put("message", "Network error: ${e.message}")
            }
            
            return Response.Builder()
                .request(request)
                .protocol(okhttp3.Protocol.HTTP_1_1)
                .code(500)
                .message("Network error")
                .body(errorJson.toString().toResponseBody("application/json".toMediaTypeOrNull()))
                .build()
        }
    }
    
    private fun handleErrorResponse(response: Response): Response {
        val responseBody = response.body?.string() ?: ""
        
        // Convert error response to proper JSON
        try {
            // If already valid JSON, keep it
            JsonParser.parseString(responseBody)
            val newBody = responseBody.toResponseBody("application/json".toMediaTypeOrNull())
            return response.newBuilder().body(newBody).build()
        } catch (e: JsonSyntaxException) {
            // Not valid JSON, create a properly formatted error
            val errorJson = JSONObject().apply {
                put("success", false)
                put("message", "Server error: $responseBody")
                put("status_code", response.code)
            }
            
            return response.newBuilder()
                .body(errorJson.toString().toResponseBody("application/json".toMediaTypeOrNull()))
                .build()
        }
    }
}
