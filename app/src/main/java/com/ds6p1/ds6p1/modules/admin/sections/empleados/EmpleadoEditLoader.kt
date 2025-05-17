package com.ds6p1.ds6p1.modules.admin.sections.empleados

import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Text
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.lifecycle.viewmodel.compose.viewModel

@Composable
fun EmpleadoEditLoader(
    cedula: String,
    onBack: () -> Unit,
    onSuccess: () -> Unit,
    viewModel: EmpleadoDetailViewModel = viewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    
    // Cargar los detalles del empleado cuando se monta el componente
    LaunchedEffect(cedula) {
        viewModel.loadDetalle(cedula)
    }
    
    Box(modifier = Modifier.fillMaxSize()) {
        when (uiState) {
            is EmpleadoDetalleUiState.Loading -> {
                CircularProgressIndicator(modifier = Modifier.align(Alignment.Center))
            }
            is EmpleadoDetalleUiState.Error -> {
                Text(
                    text = (uiState as EmpleadoDetalleUiState.Error).message,
                    color = Color.Red,
                    modifier = Modifier.align(Alignment.Center)
                )
            }
            is EmpleadoDetalleUiState.Success -> {
                val empleado = (uiState as EmpleadoDetalleUiState.Success).empleado
                // Mostrar la pantalla de edición con los datos cargados
                EmpleadoEditScreen(
                    empleado = empleado,
                    onBack = onBack,
                    onSuccess = onSuccess
                )
            }
        }
    }
}
