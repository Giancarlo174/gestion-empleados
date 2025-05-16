package com.ds6p1.ds6p1.modules.admin

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.modules.admin.models.AdminInfo
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

/**
 * ViewModel para gestionar la lógica de negocio del módulo de administración
 */
class AdminViewModel(
    private val adminInfo: AdminInfo
) : ViewModel() {
    
    // Estado de la UI
    private val _uiState = MutableStateFlow(AdminUiState(adminInfo = adminInfo))
    val uiState: StateFlow<AdminUiState> = _uiState.asStateFlow()
    
    // Contador de empleados y otros datos del dashboard
    private val _empleadosCount = MutableStateFlow(0)
    val empleadosCount: StateFlow<Int> = _empleadosCount.asStateFlow()
    
    // Funciones para acciones del administrador
    fun loadDashboardData() {
        viewModelScope.launch {
            // Simulamos carga de datos
            _uiState.value = _uiState.value.copy(isLoading = false)
            _empleadosCount.value = 24 // Ejemplo: 24 empleados registrados
        }
    }
    
    fun logout() {
        // Implementar la lógica de cierre de sesión
        // Por ejemplo, limpiar tokens, etc.
    }
    
    // Factory para crear el ViewModel con parámetros
    class Factory(private val adminInfo: AdminInfo) : ViewModelProvider.Factory {
        @Suppress("UNCHECKED_CAST")
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            if (modelClass.isAssignableFrom(AdminViewModel::class.java)) {
                return AdminViewModel(adminInfo) as T
            }
            throw IllegalArgumentException("Unknown ViewModel class")
        }
    }
}

/**
 * Estado de la UI para el módulo de administración
 */
data class AdminUiState(
    val adminInfo: AdminInfo,
    val isLoading: Boolean = true,
    val error: String? = null
)
