package com.ds6p1.ds6p1.modules.admin.sections.admins

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.modules.admin.models.AdminUser
import com.ds6p1.ds6p1.api.ApiClient
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

sealed class AdminUiState {
    object Loading : AdminUiState()
    data class Success(val admins: List<AdminUser>) : AdminUiState()
    data class Error(val message: String) : AdminUiState()
}

class AdminViewModel : ViewModel() {

    private val _uiState = MutableStateFlow<AdminUiState>(AdminUiState.Loading)
    val uiState: StateFlow<AdminUiState> = _uiState.asStateFlow()

    init {
        loadAdmins("")
    }

    fun loadAdmins(search: String) {
        viewModelScope.launch {
            _uiState.value = AdminUiState.Loading
            try {
                val admins = ApiClient.adminsApi.getAdmins()
                val filtered = if (search.isBlank()) admins else
                    admins.filter {
                        it.cedula.contains(search, true) ||
                                it.correo.contains(search, true)
                    }
                _uiState.value = AdminUiState.Success(filtered)
            } catch (e: Exception) {
                _uiState.value = AdminUiState.Error("Error cargando administradores: ${e.localizedMessage}")
            }
        }
    }
}
