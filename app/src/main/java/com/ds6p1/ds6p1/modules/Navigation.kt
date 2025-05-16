package com.ds6p1.ds6p1.modules

import androidx.compose.runtime.Composable
import androidx.compose.runtime.remember
import androidx.compose.ui.Modifier
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.ds6p1.ds6p1.api.AuthResult
import com.ds6p1.ds6p1.LoginScreen
import com.ds6p1.ds6p1.modules.admin.AdminDashboardScreen
import com.ds6p1.ds6p1.modules.admin.AdminInfo

/**
 * Rutas de navegación de la aplicación
 */
object NavigationDestinations {
    const val LOGIN = "login"
    const val ADMIN_DASHBOARD = "admin_dashboard"
    const val EMPLOYEE_DASHBOARD = "employee_dashboard"
}

/**
 * Componente principal de navegación que gestiona todas las pantallas de la app
 */
@Composable
fun AppNavigation(
    modifier: Modifier = Modifier,
    navController: NavHostController = rememberNavController(),
    authenticateUser: suspend (String, String) -> AuthResult
) {
    NavHost(
        navController = navController,
        startDestination = NavigationDestinations.LOGIN,
        modifier = modifier
    ) {
        // Pantalla de Login
        composable(NavigationDestinations.LOGIN) {
            LoginScreen(
                onLoginAttempt = { email, password ->
                    val result = authenticateUser(email, password)
                    
                    // Navegar según el tipo de usuario
                    when (result) {
                        is AuthResult.Admin -> {
                            // Navegar al dashboard de administrador con la información del admin
                            navController.navigate(
                                "${NavigationDestinations.ADMIN_DASHBOARD}/${result.cedula}/${result.apiKey}"
                            ) {
                                // Eliminar la pantalla de login del backstack
                                popUpTo(NavigationDestinations.LOGIN) { inclusive = true }
                            }
                        }
                        is AuthResult.Employee -> {
                            // Navegar al dashboard de empleado con parámetros
                            navController.navigate(
                                "${NavigationDestinations.EMPLOYEE_DASHBOARD}/${result.cedula}/${result.apiKey}"
                            ) {
                                popUpTo(NavigationDestinations.LOGIN) { inclusive = true }
                            }
                        }
                        else -> { /* No hacer nada, se queda en login */ }
                    }
                    
                    result
                }
            )
        }
        
        // Pantalla de Dashboard de Administrador
        composable(
            route = "${NavigationDestinations.ADMIN_DASHBOARD}/{cedula}/{apiKey}",
            arguments = listOf(
                navArgument("cedula") { type = NavType.StringType },
                navArgument("apiKey") { type = NavType.StringType }
            )
        ) { backStackEntry ->
            // Obtener parámetros de la ruta
            val cedula = backStackEntry.arguments?.getString("cedula") ?: ""
            val apiKey = backStackEntry.arguments?.getString("apiKey") ?: ""
            
            // Crear objeto AdminInfo
            val adminInfo = remember {
                AdminInfo(cedula = cedula, apiKey = apiKey)
            }
            
            // Mostrar pantalla de administrador
            AdminDashboardScreen(
                adminInfo = adminInfo,
                onLogout = {
                    // Navegar de vuelta al login
                    navController.navigate(NavigationDestinations.LOGIN) {
                        popUpTo(0) { inclusive = true }
                    }
                }
            )
        }
        
        // Pantalla de Dashboard de Empleado
        composable(
            route = "${NavigationDestinations.EMPLOYEE_DASHBOARD}/{cedula}/{apiKey}",
            arguments = listOf(
                navArgument("cedula") { type = NavType.StringType },
                navArgument("apiKey") { type = NavType.StringType }
            )
        ) { backStackEntry ->
            // Obtener parámetros de la ruta
            val cedula = backStackEntry.arguments?.getString("cedula") ?: ""
            val apiKey = backStackEntry.arguments?.getString("apiKey") ?: ""
        }
    }
}
