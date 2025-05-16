package com.ds6p1.ds6p1.modules.admin.sections.admin

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.AdminApi
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.NuevoAdmin
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

sealed class AdminCreateState {
    object Idle : AdminCreateState()
    object Loading : AdminCreateState()
    data class Success(val message: String): AdminCreateState()
    data class Error(val message: String): AdminCreateState()
}

class AdminCreateViewModel : ViewModel() {
    private val _state = MutableStateFlow<AdminCreateState>(AdminCreateState.Idle)
    val state: StateFlow<AdminCreateState> get() = _state

    private val api = ApiClient.adminApi

    fun crearAdmin(cedula: String, contrasena: String, contrasena2: String, correo: String) {
        if (contrasena != contrasena2) {
            _state.value = AdminCreateState.Error("Las contraseñas no coinciden")
            return
        }
        if (contrasena.length < 6) {
            _state.value = AdminCreateState.Error("La contraseña debe tener mínimo 6 caracteres")
            return
        }
        if (correo.isBlank() || cedula.isBlank()) {
            _state.value = AdminCreateState.Error("Todos los campos son obligatorios")
            return
        }
        _state.value = AdminCreateState.Loading
        viewModelScope.launch {
            try {
                val res = api.crearAdmin(NuevoAdmin(cedula, contrasena, correo))
                if (res.success) {
                    _state.value = AdminCreateState.Success(res.message)
                } else {
                    _state.value = AdminCreateState.Error(res.message)
                }
            } catch (e: Exception) {
                _state.value = AdminCreateState.Error("Error de red: ${e.message}")
            }
        }
    }

    fun resetState() {
        _state.value = AdminCreateState.Idle
    }
}
