package com.ds6p1.ds6p1.modules.admin.sections.departamentos

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.DepartmentApi
import com.ds6p1.ds6p1.api.DepartamentoNuevo
import com.ds6p1.ds6p1.api.DepartamentoResponse
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

sealed class DepartamentoCreateState {
    object Idle : DepartamentoCreateState()
    object Loading : DepartamentoCreateState()
    data class Success(val codigo: String, val message: String): DepartamentoCreateState()
    data class Error(val message: String): DepartamentoCreateState()
}

class DepartamentoCreateViewModel: ViewModel() {
    private val _state = MutableStateFlow<DepartamentoCreateState>(DepartamentoCreateState.Idle)
    val state: StateFlow<DepartamentoCreateState> get() = _state

    private val api = ApiClient.departmentApi

    fun crearDepartamento(nombre: String) {
        _state.value = DepartamentoCreateState.Loading
        viewModelScope.launch {
            try {
                val res = api.crearDepartamento(DepartamentoNuevo(nombre))
                if (res.success) {
                    _state.value = DepartamentoCreateState.Success(res.codigo ?: "", res.message)
                } else {
                    _state.value = DepartamentoCreateState.Error(res.message)
                }
            } catch (e: Exception) {
                _state.value = DepartamentoCreateState.Error(e.message ?: "Error desconocido")
            }
        }
    }

    fun resetState() {
        _state.value = DepartamentoCreateState.Idle
    }
}
