package com.ds6p1.ds6p1.modules.admin.sections.empleados

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.*
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch

sealed class EmpleadoCreateState {
    object Idle : EmpleadoCreateState()
    object Loading : EmpleadoCreateState()
    data class Success(val message: String) : EmpleadoCreateState()
    data class Error(val message: String) : EmpleadoCreateState()
}

class EmpleadoCreateViewModel : ViewModel() {

    private val _provincias = MutableStateFlow<List<Provincia>>(emptyList())
    val provincias: StateFlow<List<Provincia>> = _provincias.asStateFlow()
    private val _distritos = MutableStateFlow<List<Distrito>>(emptyList())
    val distritos: StateFlow<List<Distrito>> = _distritos.asStateFlow()
    private val _corregimientos = MutableStateFlow<List<Corregimiento>>(emptyList())
    val corregimientos: StateFlow<List<Corregimiento>> = _corregimientos.asStateFlow()
    private val _departamentos = MutableStateFlow<List<Departamento>>(emptyList())
    val departamentos: StateFlow<List<Departamento>> = _departamentos.asStateFlow()
    private val _cargos = MutableStateFlow<List<Cargo>>(emptyList())
    val cargos: StateFlow<List<Cargo>> = _cargos.asStateFlow()

    private val _state = MutableStateFlow<EmpleadoCreateState>(EmpleadoCreateState.Idle)
    val state: StateFlow<EmpleadoCreateState> = _state.asStateFlow()

    private val _nacionalidades = MutableStateFlow<List<Nacionalidad>>(emptyList())
    val nacionalidades: StateFlow<List<Nacionalidad>> = _nacionalidades.asStateFlow()
    init {
        loadProvincias()
        loadDepartamentos()
    }

    fun loadProvincias() = viewModelScope.launch {
        _provincias.value = ApiClient.optionsApi.getProvincias()
    }
    fun loadDistritos(provincia: String) = viewModelScope.launch {
        _distritos.value = ApiClient.optionsApi.getDistritos(provincia)
    }
    fun loadCorregimientos(provincia: String, distrito: String) = viewModelScope.launch {
        _corregimientos.value = ApiClient.optionsApi.getCorregimientos(provincia, distrito)
    }
    fun loadDepartamentos() = viewModelScope.launch {
        _departamentos.value = ApiClient.optionsApi.getDepartamentos()
    }
    fun loadCargos(departamento: String) = viewModelScope.launch {
        _cargos.value = ApiClient.optionsApi.getCargos(departamento)
    }
    fun loadNacionalidades() {
        viewModelScope.launch {
            try {
                _nacionalidades.value = ApiClient.optionsApi.getNacionalidades()
            } catch (e: Exception) {
                _nacionalidades.value = emptyList()
            }
        }
    }
    fun registrarEmpleado(empleado: NuevoEmpleado) {
        viewModelScope.launch {
            _state.value = EmpleadoCreateState.Loading
            try {
                val response = ApiClient.employeesApi.createEmployee(empleado)
                if (response.success) {
                    _state.value = EmpleadoCreateState.Success(response.message)
                } else {
                    _state.value = EmpleadoCreateState.Error(response.message)
                }
            } catch (e: Exception) {
                _state.value = EmpleadoCreateState.Error("Error: ${e.localizedMessage}")
            }
        }
    }

    fun resetState() { _state.value = EmpleadoCreateState.Idle }
}
