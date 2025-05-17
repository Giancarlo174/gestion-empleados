package com.ds6p1.ds6p1.modules.admin.sections.empleados

import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.runtime.*
import androidx.compose.material3.*
import androidx.compose.foundation.layout.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.api.ApiClient
import com.ds6p1.ds6p1.api.EmpleadoDetalle
import kotlinx.coroutines.launch
import androidx.compose.ui.Alignment

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmpleadosScreen(
    onView: (String) -> Unit = {},
    onEdit: (String) -> Unit = {}
) {
    val scope = rememberCoroutineScope()
    val empleadosViewModel: EmployeesViewModel = viewModel()

    // Estados de navegación interna
    var showCreate by remember { mutableStateOf(false) }
    var empleadoDetalle by remember { mutableStateOf<EmpleadoDetalle?>(null) }
    var empleadoAEditar by remember { mutableStateOf<EmpleadoDetalle?>(null) }
    var errorMsg by remember { mutableStateOf<String?>(null) }

    when {
        // --- Pantalla de Edición ---
        empleadoAEditar != null -> {
            EmpleadoEditScreen(
                empleado = empleadoAEditar!!,
                onBack = { empleadoAEditar = null },
                onSuccess = {
                    empleadoAEditar = null
                    empleadosViewModel.loadEmployees("", "all")
                }
            )
        }
        // --- Pantalla de Detalle ---
        empleadoDetalle != null -> {
            EmpleadoDetailScreen(
                cedula = empleadoDetalle!!.cedula,
                onBack = { empleadoDetalle = null },
                onEdit = { cedula ->
                    scope.launch {
                        val res = try {
                            ApiClient.employeesApi.getEmpleadoDetalle(cedula)
                        } catch (e: Exception) {
                            errorMsg = "Error al cargar datos para editar"
                            return@launch
                        }
                        if (res.success && res.empleado != null) {
                            empleadoAEditar = res.empleado
                        } else {
                            if (res.success && res.empleado != null) {
                                empleadoDetalle = res.empleado
                            } else {
                                errorMsg = "No se encontró el empleado"
                            }
                        }
                    }
                }
            )
        }
        // --- Pantalla de Crear Empleado ---
        showCreate -> {
            EmpleadoCreateScreen(
                onVolverLista = {
                    showCreate = false
                    empleadosViewModel.loadEmployees("", "all")
                }
            )
        }
        // --- Pantalla de Listado (por defecto) ---
        else -> {
            Column(Modifier.fillMaxSize()) {

                EmployeesContent(
                    viewModel = empleadosViewModel,
                    onCreate = { showCreate = true },
                    onView = { cedula ->
                        scope.launch {
                            val res = try {
                                ApiClient.employeesApi.getEmpleadoDetalle(cedula)
                            } catch (e: Exception) {
                                errorMsg = "Error al cargar detalle"
                                return@launch
                            }
                            if (res.success && res.empleado != null) {
                                empleadoDetalle = res.empleado
                            } else {
                                if (res.success && res.empleado != null) {
                                    empleadoDetalle = res.empleado
                                } else {
                                    errorMsg = "No se encontró el empleado"
                                }
                            }
                        }
                    },
                    onEdit = { cedula ->
                        scope.launch {
                            val res = try {
                                ApiClient.employeesApi.getEmpleadoDetalle(cedula)
                            } catch (e: Exception) {
                                errorMsg = "Error al cargar datos para editar"
                                return@launch
                            }
                            if (res.success && res.empleado != null) {
                                empleadoAEditar = res.empleado
                            } else {
                                if (res.success && res.empleado != null) {
                                    empleadoDetalle = res.empleado
                                } else {
                                    errorMsg = "No se encontró el empleado"
                                }
                            }
                        }
                    }
                )
            }
        }
    }
}
