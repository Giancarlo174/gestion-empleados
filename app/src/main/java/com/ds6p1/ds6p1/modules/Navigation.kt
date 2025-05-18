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
import com.ds6p1.ds6p1.modules.employee.EmpleadoPerfilScreen
import com.ds6p1.ds6p1.modules.employee.EmpleadoPerfilEditableScreen

object NavigationDestinations {
    const val LOGIN = "login"
    const val ADMIN_DASHBOARD = "admin_dashboard"
    const val EMPLOYEE_DASHBOARD = "employee_dashboard"
}

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
        // LOGIN
        composable(NavigationDestinations.LOGIN) {
            LoginScreen(
                onLoginAttempt = { email, password ->
                    val result = authenticateUser(email, password)
                    when (result) {
                        is AuthResult.Admin -> {
                            navController.navigate(
                                "${NavigationDestinations.ADMIN_DASHBOARD}/${result.cedula}/${result.apiKey}"
                            ) {
                                popUpTo(NavigationDestinations.LOGIN) { inclusive = true }
                            }
                        }
                        is AuthResult.Employee -> {
                            navController.navigate("empleado_perfil/${result.cedula}") {
                                popUpTo(NavigationDestinations.LOGIN) { inclusive = true }
                            }
                        }
                        else -> {}
                    }
                    result
                }
            )
        }

        // DASHBOARD ADMIN
        composable(
            route = "${NavigationDestinations.ADMIN_DASHBOARD}/{cedula}/{apiKey}",
            arguments = listOf(
                navArgument("cedula") { type = NavType.StringType },
                navArgument("apiKey") { type = NavType.StringType }
            )
        ) { backStackEntry ->
            val cedula = backStackEntry.arguments?.getString("cedula") ?: ""
            val apiKey = backStackEntry.arguments?.getString("apiKey") ?: ""
            val adminInfo = remember { AdminInfo(cedula = cedula, apiKey = apiKey) }

            AdminDashboardScreen(
                adminInfo = adminInfo,
                onLogout = {
                    navController.navigate(NavigationDestinations.LOGIN) {
                        popUpTo(0) { inclusive = true }
                    }
                }
            )
        }

        // PERFIL DEL EMPLEADO (solo vista)
        composable(
            route = "empleado_perfil/{cedula}",
            arguments = listOf(navArgument("cedula") { type = NavType.StringType })
        ) { backStackEntry ->
            val cedula = backStackEntry.arguments?.getString("cedula") ?: ""
            EmpleadoPerfilScreen(
                cedula = cedula,
                onBack = {
                    navController.navigate(NavigationDestinations.LOGIN) {
                        popUpTo(0) { inclusive = true }
                    }
                },
                onLogout = {
                    navController.navigate(NavigationDestinations.LOGIN) {
                        popUpTo(0) { inclusive = true }
                    }
                },
                onEditar = {
                    navController.navigate("empleado_perfil_edit/$cedula")
                }
            )
        }

        // EDICIÓN DEL PERFIL DEL EMPLEADO
        composable(
            route = "empleado_perfil_edit/{cedula}",
            arguments = listOf(navArgument("cedula") { type = NavType.StringType })
        ) { backStackEntry ->
            val cedula = backStackEntry.arguments?.getString("cedula") ?: ""
            EmpleadoPerfilEditableScreen(
                cedula = cedula,
                onBack = {
                    navController.popBackStack() // Vuelve a la pantalla de perfil
                }
            )
        }
    }
}
