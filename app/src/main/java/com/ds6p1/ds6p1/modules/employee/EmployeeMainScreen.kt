package com.ds6p1.ds6p1.modules.employee

import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController

@Composable
fun EmployeeMainScreen(
    cedula: String,
    onLogout: () -> Unit
) {
    val navController = rememberNavController()
    
    NavHost(
        navController = navController,
        startDestination = "perfil"
    ) {
        composable("perfil") {
            EmpleadoPerfilScreen(
                cedula = cedula,
                onBack = {  },
                onLogout = onLogout,
                onEditar = { 
                    // Navigate to the editable profile screen
                    navController.navigate("editar_perfil")
                }
            )
        }
        
        composable("editar_perfil") {
            EmpleadoPerfilEditableScreen(
                cedula = cedula,
                onBack = { navController.popBackStack() }
            )
        }
    }
}
