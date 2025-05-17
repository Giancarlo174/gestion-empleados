// MainBottomBar.kt

package com.ds6p1.ds6p1.modules.admin

import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Dashboard
import androidx.compose.material.icons.filled.People
import androidx.compose.material.icons.filled.Business
import androidx.compose.material.icons.filled.Settings
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.ds6p1.ds6p1.modules.admin.sections.ajustes.SettingsScreen
import com.ds6p1.ds6p1.modules.admin.sections.dashboard.DashboardContent
import com.ds6p1.ds6p1.modules.admin.sections.empleados.EmpleadoDetailScreen
import com.ds6p1.ds6p1.modules.admin.sections.empleados.EmpleadoEditLoader
import com.ds6p1.ds6p1.modules.admin.sections.empleados.EmpleadosScreen
import com.ds6p1.ds6p1.modules.admin.sections.organizacion.OrganizacionScreen

@Composable
fun MainBottomBar(
    adminInfo: AdminInfo,
    onLogout: () -> Unit
) {
    var selectedIndex by remember { mutableStateOf(0) }
    val navController = rememberNavController()

    val sections = listOf(
        BottomNavSection("Dashboard", Icons.Default.Dashboard),
        BottomNavSection("Empleados", Icons.Default.People),
        BottomNavSection("Organización", Icons.Default.Business),
        BottomNavSection("Ajustes", Icons.Default.Settings)
    )

    Scaffold(
        bottomBar = {
            NavigationBar(
                tonalElevation = 8.dp
            ) {
                sections.forEachIndexed { i, section ->
                    NavigationBarItem(
                        selected = selectedIndex == i,
                        onClick = { 
                            selectedIndex = i
                            // Cuando se cambia de tab, navegar a la ruta principal
                            when (i) {
                                0 -> navController.navigate("dashboard") { 
                                    popUpTo("dashboard") { inclusive = true }
                                }
                                1 -> navController.navigate("empleados") {
                                    popUpTo("empleados") { inclusive = true }
                                }
                                2 -> navController.navigate("organizacion") {
                                    popUpTo("organizacion") { inclusive = true }
                                }
                                3 -> navController.navigate("ajustes") {
                                    popUpTo("ajustes") { inclusive = true }
                                }
                            }
                        },
                        icon = { Icon(section.icon, contentDescription = section.label) },
                        label = { Text(section.label) }
                    )
                }
            }
        }
    ) { paddingValues ->
        // Implementar NavHost para manejar la navegación anidada
        NavHost(
            navController = navController,
            startDestination = "dashboard",
            modifier = Modifier.padding(paddingValues)
        ) {
            composable("dashboard") {
                DashboardContent(
                    showLogout = true,
                    onLogout = onLogout
                )
            }
            composable("empleados") {
                EmpleadosScreen(
                    onView = { cedula ->
                        navController.navigate("empleado_detalle/$cedula")
                    },
                    onEdit = { cedula ->
                        // Navegar a la pantalla de edición
                        navController.navigate("empleado_editar/$cedula")
                    }
                )
            }
            composable(
                route = "empleado_detalle/{cedula}",
                arguments = listOf(navArgument("cedula") { type = NavType.StringType })
            ) { backStackEntry ->
                val cedula = backStackEntry.arguments?.getString("cedula") ?: ""
                EmpleadoDetailScreen(
                    cedula = cedula,
                    onBack = { navController.popBackStack() },
                    onEdit = { cedula -> 
                        // También permitir editar desde la pantalla de detalles
                        navController.navigate("empleado_editar/$cedula")
                    }
                )
            }
            // Nueva ruta para la edición de empleados
            composable(
                route = "empleado_editar/{cedula}",
                arguments = listOf(navArgument("cedula") { type = NavType.StringType })
            ) { backStackEntry ->
                val cedula = backStackEntry.arguments?.getString("cedula") ?: ""
                // Componente para cargar y editar empleado
                EmpleadoEditLoader(
                    cedula = cedula,
                    onBack = { navController.popBackStack() },
                    onSuccess = { navController.popBackStack() }
                )
            }
            composable("organizacion") {
                OrganizacionScreen()
            }
            composable("ajustes") {
                SettingsScreen(
                    adminInfo = adminInfo,
                    onLogout = onLogout
                )
            }
        }
    }
}

data class BottomNavSection(val label: String, val icon: ImageVector)
