package com.ds6p1.ds6p1.modules.admin.sections.cargos

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.Cargo
import com.ds6p1.ds6p1.api.RetrofitInstance
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

sealed class CargosUiState {
    object Loading : CargosUiState()
    data class Success(val cargos: List<Cargo>) : CargosUiState()
    data class Error(val message: String) : CargosUiState()
}

class CargosViewModel : ViewModel() {

    private val _uiState = MutableStateFlow<CargosUiState>(CargosUiState.Loading)
    val uiState: StateFlow<CargosUiState> = _uiState.asStateFlow()

    init {
        loadCargos("")
    }

    fun loadCargos(search: String) {
        viewModelScope.launch {
            _uiState.value = CargosUiState.Loading
            try {
                val cargos = ApiClient.positionsApi.getCargos(search)
                _uiState.value = CargosUiState.Success(cargos)
            } catch (e: Exception) {
                _uiState.value = CargosUiState.Error("Error cargando cargos: ${e.localizedMessage}")
            }
        }
    }

    fun deleteCargo(codigo: String, onComplete: (Boolean, String) -> Unit) {
        viewModelScope.launch {
            try {
                val response = ApiClient.cargoApi.deleteCargo(codigo)
                if (response.success) {
                    loadCargos("") // Recargar la lista de cargos
                    onComplete(true, response.message ?: "Cargo eliminado correctamente")
                } else {
                    // Devuelve el mensaje de error exactamente como viene del servidor
                    onComplete(false, response.message ?: "Error al eliminar el cargo")
                }
            } catch (e: Exception) {
                onComplete(false, "Error de conexión: ${e.localizedMessage}")
            }
        }
    }
}

