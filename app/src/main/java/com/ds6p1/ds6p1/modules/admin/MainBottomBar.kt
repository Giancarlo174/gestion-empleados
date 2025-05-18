package com.ds6p1.ds6p1.modules.admin

import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Dashboard
import androidx.compose.material.icons.filled.People
import androidx.compose.material.icons.filled.Business
import androidx.compose.material.icons.filled.Work
import androidx.compose.material.icons.filled.Settings
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.unit.dp
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.ds6p1.ds6p1.modules.admin.sections.admin.AdminCreateScreen
import com.ds6p1.ds6p1.modules.admin.sections.admin.AdminEditScreen
import com.ds6p1.ds6p1.modules.admin.sections.admins.AdminsScreen
import com.ds6p1.ds6p1.modules.admin.sections.ajustes.*
import com.ds6p1.ds6p1.modules.admin.sections.cargos.CargoCreateScreen
import com.ds6p1.ds6p1.modules.admin.sections.cargos.CargosScreen
import com.ds6p1.ds6p1.modules.admin.sections.dashboard.DashboardContent
import com.ds6p1.ds6p1.modules.admin.sections.departamentos.DepartamentoCreateScreen
import com.ds6p1.ds6p1.modules.admin.sections.departamentos.DepartamentosScreen
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
        BottomNavSection("", Icons.Default.Dashboard),
        BottomNavSection("", Icons.Default.People),
        BottomNavSection("", Icons.Default.Business),
        BottomNavSection("", Icons.Default.Work),
        BottomNavSection("", Icons.Default.Settings)
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
                                3 -> navController.navigate("cargos") {
                                    popUpTo("cargos") { inclusive = true }
                                }
                                4 -> navController.navigate("ajustes") {
                                    popUpTo("ajustes") { inclusive = true }
                                }
                            }
                        },
                        icon = { Icon(section.icon, contentDescription = null) },
                        // No label para solo iconos
                        alwaysShowLabel = false,
                        label = null
                    )
                }
            }
        }
    ) { paddingValues ->
        // Elimina el padding superior extra
        Box(Modifier.fillMaxSize()) {
            NavHost(
                navController = navController,
                startDestination = "dashboard",
                modifier = Modifier
                    .fillMaxSize()
                    .padding(bottom = paddingValues.calculateBottomPadding())
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
                            navController.navigate("empleado_editar/$cedula")
                        }
                    )
                }
                composable(
                    route = "empleado_editar/{cedula}",
                    arguments = listOf(navArgument("cedula") { type = NavType.StringType })
                ) { backStackEntry ->
                    val cedula = backStackEntry.arguments?.getString("cedula") ?: ""
                    EmpleadoEditLoader(
                        cedula = cedula,
                        onBack = { navController.popBackStack() },
                        onSuccess = { navController.popBackStack() }
                    )
                }
                // ORGANIZACION: manda ambos callbacks para "Nuevo"
                composable("organizacion") {
                    OrganizacionScreen(
                        onNuevoDepartamento = {
                            navController.navigate("departamento_crear")
                        },

                    )
                }
                // DEPARTAMENTOS desde menú
                composable("departamentos") {
                    DepartamentosScreen(
                        onNuevo = {
                            navController.navigate("departamento_crear")
                        }
                    )
                }
                // CARGOS desde menú
                composable("cargos") {
                    CargosScreen(
                        onNuevo = {
                            navController.navigate("cargo_crear")
                        }
                    )
                }
                // CREAR DEPARTAMENTO
                composable("departamento_crear") {
                    DepartamentoCreateScreen(
                        onVolverLista = { navController.popBackStack() }
                    )
                }
                // CREAR CARGO
                composable("cargo_crear") {
                    CargoCreateScreen(
                        onVolverLista = { navController.popBackStack() }
                    )
                }

                // --- AJUSTES Y SUS SECCIONES HIJAS ---
                composable("ajustes") {
                    AjustesMenuScreenModern(
                        adminInfo = adminInfo,
                        onPerfil = { navController.navigate("ajustes/perfil") },
                        onAdministradores = { navController.navigate("ajustes/admins") },
                        onCambiarPass = { navController.navigate("ajustes/cambiarpass") },
                        onLogout = onLogout
                    )
                }
                composable("ajustes/perfil") {
                    PerfilScreen(
                        adminInfo = adminInfo,
                        onBack = { navController.popBackStack() }
                    )
                }
                composable("ajustes/admins") {
                    AdminsScreen(
                        onBack = { navController.popBackStack() },
                        onEdit = { admin ->
                            // Navegar a la pantalla de edición pasando cédula y correo como parámetros
                            navController.navigate("ajustes/admins/edit/${admin.cedula}/${admin.correo}")
                        },
                        onNuevo = {
                            // Navegar a la pantalla de creación de administrador
                            navController.navigate("ajustes/admins/nuevo")
                        }
                    )
                }
                
                // Ruta para crear nuevo administrador
                composable("ajustes/admins/nuevo") {
                    AdminCreateScreen(
                        onVolverLista = { navController.popBackStack() }
                    )
                }
                
                // Ruta para editar administrador existente
                composable(
                    route = "ajustes/admins/edit/{cedula}/{correo}",
                    arguments = listOf(
                        navArgument("cedula") { type = NavType.StringType },
                        navArgument("correo") { type = NavType.StringType }
                    )
                ) { backStackEntry ->
                    val cedula = backStackEntry.arguments?.getString("cedula") ?: ""
                    val correo = backStackEntry.arguments?.getString("correo") ?: ""
                    AdminEditScreen(
                        cedula = cedula,
                        correo = correo,
                        onBack = { navController.popBackStack() }
                    )
                }

                composable("ajustes/cambiarpass") {
                    CambiarContrasenaScreen(
                        onBack = { navController.popBackStack() },
                        cedula = adminInfo.cedula
                    )
                }

            }
        }
    }
}

data class BottomNavSection(val label: String, val icon: ImageVector)