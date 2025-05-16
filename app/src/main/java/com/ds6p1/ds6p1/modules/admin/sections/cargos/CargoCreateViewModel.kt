package com.ds6p1.ds6p1.modules.admin.sections.cargos

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.CargoNuevo
import com.ds6p1.ds6p1.api.CargoResponse
import com.ds6p1.ds6p1.api.Department
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

sealed class CargoCreateState {
    object Idle : CargoCreateState()
    object Loading : CargoCreateState()
    data class Success(val message: String): CargoCreateState()
    data class Error(val message: String): CargoCreateState()
}

class CargoCreateViewModel : ViewModel() {
    private val _state = MutableStateFlow<CargoCreateState>(CargoCreateState.Idle)
    val state: StateFlow<CargoCreateState> get() = _state

    private val _departamentos = MutableStateFlow<List<Department>>(emptyList())
    val departamentos: StateFlow<List<Department>> get() = _departamentos

    init {
        cargarDepartamentos()
    }

    private fun cargarDepartamentos() {
        viewModelScope.launch {
            try {
                val result = ApiClient.cargoApi.getDepartamentos()
                _departamentos.value = result
            } catch (e: Exception) {
                // Nada por ahora, solo deja vacío
            }
        }
    }

    fun crearCargo(depCodigo: String, codigo: String, nombre: String) {
        _state.value = CargoCreateState.Loading
        viewModelScope.launch {
            try {
                val res = ApiClient.cargoApi.crearCargo(CargoNuevo(depCodigo, codigo, nombre))
                if (res.success) {
                    _state.value = CargoCreateState.Success(res.message)
                } else {
                    _state.value = CargoCreateState.Error(res.message)
                }
            } catch (e: Exception) {
                _state.value = CargoCreateState.Error(e.message ?: "Error desconocido")
            }
        }
    }

    fun resetState() {
        _state.value = CargoCreateState.Idle
    }
}
