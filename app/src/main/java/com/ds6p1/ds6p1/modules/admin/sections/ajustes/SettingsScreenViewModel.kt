package com.ds6p1.ds6p1.modules.admin.sections.ajustes

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.AdminsApi
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.NuevoAdmin
import com.ds6p1.ds6p1.api.AdminResponse
import com.ds6p1.ds6p1.modules.admin.models.AdminUser
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import com.ds6p1.ds6p1.api.EditAdminBody
import com.ds6p1.ds6p1.api.DeleteAdminBody

sealed class AdminsUiState {
    object Loading : AdminsUiState()
    data class Success(val admins: List<AdminUser>) : AdminsUiState()
    data class Error(val message: String) : AdminsUiState()
}

class AdminsViewModel : ViewModel() {
    private val _uiState = MutableStateFlow<AdminsUiState>(AdminsUiState.Loading)
    val uiState = _uiState.asStateFlow()

    fun loadAdmins() {
        viewModelScope.launch {
            _uiState.value = AdminsUiState.Loading
            try {
                val admins = ApiClient.adminsApi.getAdmins()
                _uiState.value = AdminsUiState.Success(admins)
            } catch (e: Exception) {
                _uiState.value = AdminsUiState.Error(e.message ?: "Error al cargar administradores")
            }
        }
    }

    fun crearAdmin(nuevoAdmin: NuevoAdmin, onResult: (Boolean, String) -> Unit) {
        viewModelScope.launch {
            try {
                val response = ApiClient.adminApi.crearAdmin(nuevoAdmin)
                onResult(response.success, response.message)
                if (response.success) {
                    loadAdmins() // Recargar lista
                }
            } catch (e: Exception) {
                onResult(false, e.message ?: "Error al agregar administrador")
            }
        }
    }
    
    fun editarAdmin(
        id: Int,
        cedula: String,
        correo: String,
        contrasenaActual: String,
        nuevaContrasena: String?,
        onResult: (Boolean, String) -> Unit
    ) {
        viewModelScope.launch {
            try {
                val response = ApiClient.adminApi.editarAdmin(
                    EditAdminBody(id, cedula, correo, contrasenaActual, nuevaContrasena)
                )
                onResult(response.success, response.message)
                if (response.success) loadAdmins()
            } catch (e: Exception) {
                onResult(false, e.message ?: "Error de conexión")
            }
        }
    }

    fun eliminarAdmin(
        admin: AdminUser,
        onResult: (Boolean, String) -> Unit
    ) {
        viewModelScope.launch {
            try {
                val response = ApiClient.adminApi.eliminarAdmin(DeleteAdminBody(admin.id))
                onResult(response.success, response.message)
                if (response.success) loadAdmins()
            } catch (e: Exception) {
                onResult(false, e.message ?: "Error de conexión")
            }
        }
    }
}
