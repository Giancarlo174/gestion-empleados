package com.ds6p1.ds6p1.modules.admin.sections.empleados

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.EmpleadoDetalle
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch

sealed class EmpleadoDetalleUiState {
    object Loading : EmpleadoDetalleUiState()
    data class Success(val empleado: EmpleadoDetalle) : EmpleadoDetalleUiState()
    data class Error(val message: String) : EmpleadoDetalleUiState()
}

class EmpleadoDetailViewModel : ViewModel() {
    private val _uiState = MutableStateFlow<EmpleadoDetalleUiState>(EmpleadoDetalleUiState.Loading)
    val uiState: StateFlow<EmpleadoDetalleUiState> = _uiState.asStateFlow()

    fun loadDetalle(cedula: String) {
        viewModelScope.launch {
            _uiState.value = EmpleadoDetalleUiState.Loading
            try {
                val response = ApiClient.employeesApi.getEmpleadoDetalle(cedula)
                if (response.success && response.empleado != null) {
                    _uiState.value = EmpleadoDetalleUiState.Success(response.empleado)
                } else {
                    _uiState.value = EmpleadoDetalleUiState.Error(response.empleado?.let { "Error desconocido" } ?: "No se encontró el empleado")
                }
            } catch (e: Exception) {
                _uiState.value = EmpleadoDetalleUiState.Error("Error cargando empleado: ${e.localizedMessage}")
            }
        }
    }
}
