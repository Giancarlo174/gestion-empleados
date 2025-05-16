package com.ds6p1.ds6p1.modules.admin.sections.cargos

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.Cargo
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

    fun deleteCargo(codigo: String, onSuccess: () -> Unit = {}) {
        viewModelScope.launch {
            try {
                ApiClient.positionsApi.deleteCargo(codigo)
                onSuccess()
                loadCargos("")
            } catch (e: Exception) {
                _uiState.value = CargosUiState.Error("Error eliminando cargo: ${e.localizedMessage}")
            }
        }
    }
}
