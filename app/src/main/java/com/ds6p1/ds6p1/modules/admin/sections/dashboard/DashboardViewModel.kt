package com.ds6p1.ds6p1.modules.admin.sections.dashboard

import android.util.Log
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL

class DashboardViewModel : ViewModel() {
    
    private val TAG = "DashboardViewModel"
    private val _state = MutableStateFlow<DashboardState>(DashboardState.Loading)
    val state = _state.asStateFlow()
    
    init {
        loadDashboardStats()
    }
    
    fun loadDashboardStats() {
        viewModelScope.launch {
            _state.value = DashboardState.Loading
            
            try {
                val stats = withContext(Dispatchers.IO) {
                    fetchFromApi()
                }
                _state.value = DashboardState.Success(stats)
                Log.d(TAG, "Datos cargados exitosamente")
            } catch (e: Exception) {
                Log.e(TAG, "Error al cargar datos: ${e.message}", e)
                _state.value = DashboardState.Error(e.message ?: "Error desconocido")
            }
        }
    }
    
    private suspend fun fetchFromApi(): DashboardStats {
        val url = URL("http://10.0.2.2/ds6p1/backend/admin/dashboard_stats.php")
        
        try {
            val connection = (url.openConnection() as HttpURLConnection).apply {
                connectTimeout = 15000
                readTimeout = 15000
            }
            
            val responseCode = connection.responseCode
            if (responseCode != HttpURLConnection.HTTP_OK) {
                throw Exception("Error del servidor: código $responseCode")
            }
            
            val response = connection.inputStream.bufferedReader().use { it.readText() }
            Log.d(TAG, "Respuesta JSON: $response")
            
            val jsonResponse = JSONObject(response)
            if (!jsonResponse.getBoolean("success")) {
                throw Exception(jsonResponse.getString("message"))
            }
            
            val data = jsonResponse.getJSONObject("data")
            
            // Obtener datos básicos
            val totalEmpleados = data.getInt("totalEmpleados")
            val empleadosActivos = data.getInt("empleadosActivos")
            val empleadosInactivos = data.getInt("empleadosInactivos")
            
            // Obtener departamentos
            val departamentosArray = data.getJSONArray("departamentos")
            val departamentos = mutableListOf<DepartamentoStat>()
            
            for (i in 0 until departamentosArray.length()) {
                val depto = departamentosArray.getJSONObject(i)
                departamentos.add(
                    DepartamentoStat(
                        nombre = depto.getString("nombre"),
                        totalEmpleados = depto.getInt("totalEmpleados"),
                        porcentaje = depto.getDouble("porcentaje").toFloat()
                    )
                )
            }
            
            return DashboardStats(
                totalEmpleados = totalEmpleados,
                empleadosActivos = empleadosActivos,
                empleadosInactivos = empleadosInactivos,
                departamentos = departamentos
            )
        } catch (e: Exception) {
            Log.e(TAG, "Error en fetchFromApi: ${e.message}", e)
            throw e
        }
    }
}

sealed class DashboardState {
    object Loading : DashboardState()
    data class Success(val data: DashboardStats) : DashboardState()
    data class Error(val message: String) : DashboardState()
}
