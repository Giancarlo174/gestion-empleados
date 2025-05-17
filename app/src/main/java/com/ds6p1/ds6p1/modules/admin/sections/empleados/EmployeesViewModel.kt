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

    fun deleteEmployee(cedula: String, onResult: (Boolean, String) -> Unit = { _, _ -> }) {
        viewModelScope.launch {
            try {
                val response = RetrofitInstance.employeesApi.deleteEmployee(cedula)
                if (response.success) {
                    onResult(true, response.message)
                    loadEmployees("", "all")
                } else {
                    onResult(false, response.message)
                }
            } catch (e: Exception) {
                // Manejo especial de errores HTTP para ver la respuesta cruda
                if (e is retrofit2.HttpException) {
                    val errorBody = e.response()?.errorBody()?.string()
                    // Logueá la respuesta cruda pa' que pillés si es HTML, warning, etc.
                    android.util.Log.e("deleteEmployee", "ErrorBody: $errorBody")
                    // Le mandás el mensaje real al usuario pa' que sepa qué fue lo que pasó
                    onResult(false, "Respuesta inválida del servidor: $errorBody")
                } else {
                    onResult(false, "Error eliminando empleado: ${e.localizedMessage}")
                }
            }
        }
    }


}
