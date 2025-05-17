package com.ds6p1.ds6p1.modules.admin.sections.empleados

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.ds6p1.ds6p1.api.*
import com.ds6p1.ds6p1.api.ApiClient.apiService
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch

open class EmpleadoEditState {
    object Idle : EmpleadoEditState()
    object Loading : EmpleadoEditState()
    data class Success(val message: String) : EmpleadoEditState()
    data class Error(val message: String) : EmpleadoEditState()
}

class EmpleadoEditScreenModel : ViewModel() {

    private val _provincias = MutableStateFlow<List<Provincia>>(emptyList())
    val provincias = _provincias.asStateFlow()

    private val _distritos = MutableStateFlow<List<Distrito>>(emptyList())
    val distritos = _distritos.asStateFlow()

    private val _corregimientos = MutableStateFlow<List<Corregimiento>>(emptyList())
    val corregimientos = _corregimientos.asStateFlow()

    private val _departamentos = MutableStateFlow<List<Departamento>>(emptyList())
    val departamentos = _departamentos.asStateFlow()

    private val _cargos = MutableStateFlow<List<Cargo>>(emptyList())
    val cargos = _cargos.asStateFlow()

    private val _nacionalidades = MutableStateFlow<List<Nacionalidad>>(emptyList())
    val nacionalidades = _nacionalidades.asStateFlow()

    private val _state = MutableStateFlow<EmpleadoEditState>(EmpleadoEditState.Idle)
    val state = _state.asStateFlow()

    init {
        loadProvincias()
        loadDepartamentos()
        loadNacionalidades()
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

    fun loadNacionalidades() = viewModelScope.launch {
        try {
            _nacionalidades.value = ApiClient.optionsApi.getNacionalidades()
        } catch (e: Exception) {
            _nacionalidades.value = emptyList()
        }
    }

    fun actualizarEmpleado(empleado: NuevoEmpleado) = viewModelScope.launch {
        _state.value = EmpleadoEditState.Loading
        try {
            val response = ApiClient.employeesApi.updateEmployee(empleado)
            if (response.success) {
                _state.value = EmpleadoEditState.Success(response.message)
            } else {
                _state.value = EmpleadoEditState.Error(response.message)
            }
        } catch (e: Exception) {
            _state.value = EmpleadoEditState.Error("Error: ${e.localizedMessage}")
        }
    }

    fun resetState() {
        _state.value = EmpleadoEditState.Idle
    }
}

