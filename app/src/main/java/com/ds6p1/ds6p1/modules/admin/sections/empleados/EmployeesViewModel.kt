package com.ds6p1.ds6p1.modules.admin.sections.empleados

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.Employee
import com.ds6p1.ds6p1.api.RetrofitInstance
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch

sealed class EmployeesUiState {
    object Loading : EmployeesUiState()
    data class Success(val employees: List<Employee>) : EmployeesUiState()
    data class Error(val message: String) : EmployeesUiState()
}

class EmployeesViewModel : ViewModel() {

    private val _uiState = MutableStateFlow<EmployeesUiState>(EmployeesUiState.Loading)
    val uiState: StateFlow<EmployeesUiState> = _uiState.asStateFlow()

    init {
        loadEmployees("", "all")
    }

    fun loadEmployees(search: String, filter: String) {
        viewModelScope.launch {
            _uiState.value = EmployeesUiState.Loading
            try {
                val response = RetrofitInstance.employeesApi.getEmployees(search, filter)
                _uiState.value = EmployeesUiState.Success(response)
            } catch (e: Exception) {
                _uiState.value = EmployeesUiState.Error("Error cargando empleados: ${e.localizedMessage}")
            }
        }
    }

    fun deleteEmployee(cedula: String) {
        viewModelScope.launch {
            // TODO: llamada real a API para eliminar el empleado
            loadEmployees("", "all")
        }
    }
}
