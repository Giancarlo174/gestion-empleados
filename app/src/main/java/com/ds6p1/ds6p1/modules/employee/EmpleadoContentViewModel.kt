// EmpleadoPerfilViewModel.kt
package com.ds6p1.ds6p1.modules.employee

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.EmpleadoDetalle
import com.ds6p1.ds6p1.api.EmpleadoDetalleResponse
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

sealed class EmpleadoDetalleUiState {
    object Loading : EmpleadoDetalleUiState()
    data class Success(val empleado: EmpleadoDetalle) : EmpleadoDetalleUiState()
    data class Error(val message: String) : EmpleadoDetalleUiState()
}

class EmpleadoPerfilViewModel : ViewModel() {
    private val _uiState = MutableStateFlow<EmpleadoDetalleUiState>(EmpleadoDetalleUiState.Loading)
    val uiState = _uiState.asStateFlow()

    fun loadDetalle(cedula: String) {
        viewModelScope.launch {
            _uiState.value = EmpleadoDetalleUiState.Loading
            try {
                val response = ApiClient.employeesApi.getEmpleadoDetalle(cedula)
                if (response.success && response.empleado != null) {
                    _uiState.value = EmpleadoDetalleUiState.Success(response.empleado)
                } else {
                    _uiState.value = EmpleadoDetalleUiState.Error(response.message ?: "No se encontró el empleado")
                }

            } catch (e: Exception) {
                _uiState.value = EmpleadoDetalleUiState.Error("Error de conexión: ${e.message}")
            }
        }
    }
}
