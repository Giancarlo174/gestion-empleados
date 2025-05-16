package com.ds6p1.ds6p1.modules.admin.sections.departamentos

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.Department
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

sealed class DepartmentUiState {
    object Loading : DepartmentUiState()
    data class Success(val departments: List<Department>) : DepartmentUiState()
    data class Error(val message: String) : DepartmentUiState()
}

class DepartmentViewModel : ViewModel() {

    private val _uiState = MutableStateFlow<DepartmentUiState>(DepartmentUiState.Loading)
    val uiState: StateFlow<DepartmentUiState> = _uiState.asStateFlow()

    init {
        loadDepartments("")
    }

    fun loadDepartments(search: String) {
        viewModelScope.launch {
            _uiState.value = DepartmentUiState.Loading
            try {
                val departments = ApiClient.departmentApi.getDepartments(search)
                _uiState.value = DepartmentUiState.Success(departments)
            } catch (e: Exception) {
                _uiState.value = DepartmentUiState.Error("Error cargando departamentos: ${e.localizedMessage}")
            }
        }
    }
}
