package com.ds6p1.ds6p1.modules.admin

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.Logout
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.AccountCircle
import androidx.compose.material.icons.outlined.Logout
import androidx.compose.material.icons.outlined.Menu
import androidx.compose.material.icons.outlined.Person
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.modules.admin.sections.admin.AdminCreateScreen
import com.ds6p1.ds6p1.modules.admin.sections.admins.AdminsScreen
import com.ds6p1.ds6p1.modules.admin.sections.cargos.CargosScreen
import com.ds6p1.ds6p1.modules.admin.sections.cargos.CargoCreateScreen
import com.ds6p1.ds6p1.modules.admin.sections.dashboard.DashboardContent
import com.ds6p1.ds6p1.modules.admin.sections.dashboard.DashboardViewModel
import com.ds6p1.ds6p1.modules.admin.sections.departamentos.DepartamentosScreen
import com.ds6p1.ds6p1.modules.admin.sections.departamentos.DepartamentoCreateScreen
import com.ds6p1.ds6p1.modules.admin.sections.empleados.EmpleadosScreen
import com.ds6p1.ds6p1.modules.admin.sections.empleados.EmpleadoCreateScreen
import kotlinx.coroutines.launch
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.material3.DropdownMenu
import androidx.compose.material3.DropdownMenuItem
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.IconButton
import androidx.compose.material3.Text
import androidx.compose.ui.draw.shadow

// ------ HEADER MINIMALISTA MODERNO --------
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DashboardHeader(
    title: String,
    onMenuClick: () -> Unit,
    onProfileClick: () -> Unit,
    onLogoutClick: () -> Unit
) {
    var showMenu by remember { mutableStateOf(false) }
    TopAppBar(
        title = {
            Text(
                text = title,
                color = MaterialTheme.colorScheme.primary,
                style = MaterialTheme.typography.titleMedium,
                maxLines = 1,
            )
        },
        navigationIcon = {
            IconButton(onClick = onMenuClick) {
                Icon(
                    imageVector = Icons.Outlined.Menu,
                    contentDescription = "Menú",
                    tint = MaterialTheme.colorScheme.onSurface
                )
            }
        },
        actions = {
            Box {
                IconButton(onClick = { showMenu = !showMenu }) {
                    Icon(
                        imageVector = Icons.Outlined.AccountCircle,
                        contentDescription = "Perfil",
                        tint = MaterialTheme.colorScheme.onSurface
                    )
                }
                DropdownMenu(
                    expanded = showMenu,
                    onDismissRequest = { showMenu = false },
                    modifier = Modifier
                        .width(160.dp)
                ) {
                    DropdownMenuItem(
                        text = {
                            Text(
                                "Mi perfil",
                                style = MaterialTheme.typography.bodyLarge
                            )
                        },
                        onClick = {
                            showMenu = false
                            onLogoutClick()
                        },
                        leadingIcon = {
                            Icon(
                                Icons.Outlined.Person,
                                contentDescription = null
                            )
                        }
                    )
                    Divider()
                    DropdownMenuItem(
                        text = {
                            Text(
                                "Cerrar sesión",
                                color = MaterialTheme.colorScheme.error
                            )
                        },
                        onClick = { /* ... */ },
                        leadingIcon = {
                            Icon(
                                Icons.Outlined.Logout,
                                contentDescription = null,
                                tint = MaterialTheme.colorScheme.error
                            )
                        }
                    )
                }
            }
        },
        colors = TopAppBarDefaults.topAppBarColors(
            containerColor = MaterialTheme.colorScheme.surface,
            titleContentColor = MaterialTheme.colorScheme.primary,
            actionIconContentColor = MaterialTheme.colorScheme.onSurface,
            navigationIconContentColor = MaterialTheme.colorScheme.onSurface
        ),
        modifier = Modifier.statusBarsPadding()
    )
}

// ------ DASHBOARD COMPLETO --------
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AdminDashboardScreen(
    adminInfo: AdminInfo,
    onLogout: () -> Unit
) {
    var selectedSection by remember { mutableStateOf(AdminSection.Dashboard) }
    val dashboardViewModel: DashboardViewModel = viewModel()
    val drawerState = rememberDrawerState(initialValue = DrawerValue.Closed)
    val scope = rememberCoroutineScope()

    ModalNavigationDrawer(
        drawerState = drawerState,
        drawerContent = {
            ModalDrawerSheet(
                modifier = Modifier.width(300.dp),
                drawerContainerColor = MaterialTheme.colorScheme.surface,
                drawerContentColor = MaterialTheme.colorScheme.onSurface
            ) {
                // Sidebar con info y navegación
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(vertical = 24.dp, horizontal = 16.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Box(
                        modifier = Modifier
                            .size(80.dp)
                            .background(
                                color = MaterialTheme.colorScheme.primary,
                                shape = CircleShape
                            ),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = adminInfo.cedula.take(2).uppercase(),
                            color = MaterialTheme.colorScheme.onPrimary,
                            fontSize = 28.sp,
                            fontWeight = FontWeight.Bold
                        )
                    }
                    Spacer(modifier = Modifier.height(16.dp))
                    Text(
                        text = "Administrador",
                        style = MaterialTheme.typography.titleMedium,
                        fontWeight = FontWeight.Bold
                    )
                    Text(
                        text = adminInfo.cedula,
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.7f)
                    )
                }

                Divider()

                // Secciones principales
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(vertical = 8.dp)
                ) {
                    NavigationDrawerItem(
                        label = { Text("Dashboard") },
                        selected = selectedSection == AdminSection.Dashboard,
                        onClick = {
                            selectedSection = AdminSection.Dashboard
                            scope.launch { drawerState.close() }
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Default.Dashboard,
                                contentDescription = "Dashboard"
                            )
                        },
                        modifier = Modifier.padding(NavigationDrawerItemDefaults.ItemPadding)
                    )
                    NavigationDrawerItem(
                        label = { Text("Empleados") },
                        selected = selectedSection == AdminSection.Employees,
                        onClick = {
                            selectedSection = AdminSection.Employees
                            scope.launch { drawerState.close() }
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Default.People,
                                contentDescription = "Empleados"
                            )
                        },
                        modifier = Modifier.padding(NavigationDrawerItemDefaults.ItemPadding)
                    )
                    NavigationDrawerItem(
                        label = { Text("Departamentos") },
                        selected = selectedSection == AdminSection.Departments,
                        onClick = {
                            selectedSection = AdminSection.Departments
                            scope.launch { drawerState.close() }
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Default.Business,
                                contentDescription = "Departamentos"
                            )
                        },
                        modifier = Modifier.padding(NavigationDrawerItemDefaults.ItemPadding)
                    )
                    NavigationDrawerItem(
                        label = { Text("Cargos") },
                        selected = selectedSection == AdminSection.Positions,
                        onClick = {
                            selectedSection = AdminSection.Positions
                            scope.launch { drawerState.close() }
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Default.Work,
                                contentDescription = "Cargos"
                            )
                        },
                        modifier = Modifier.padding(NavigationDrawerItemDefaults.ItemPadding)
                    )
                    NavigationDrawerItem(
                        label = { Text("Administradores") },
                        selected = selectedSection == AdminSection.Admin,
                        onClick = {
                            selectedSection = AdminSection.Admin
                            scope.launch { drawerState.close() }
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Default.SupervisorAccount,
                                contentDescription = "Administradores"
                            )
                        },
                        modifier = Modifier.padding(NavigationDrawerItemDefaults.ItemPadding)
                    )
                }

                Divider(modifier = Modifier.padding(vertical = 8.dp))

                // Label para la sección "Nuevo"
                Text(
                    text = "NUEVO",
                    style = MaterialTheme.typography.labelMedium,
                    color = MaterialTheme.colorScheme.primary,
                    fontWeight = FontWeight.Bold,
                    modifier = Modifier.padding(start = 28.dp, top = 16.dp, bottom = 8.dp)
                )

                // Opciones para crear nuevos elementos
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(vertical = 8.dp)
                ) {
                    NavigationDrawerItem(
                        label = { Text("Nuevo Empleado") },
                        selected = selectedSection == AdminSection.NewEmployee,
                        onClick = {
                            selectedSection = AdminSection.NewEmployee
                            scope.launch { drawerState.close() }
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Default.PersonAdd,
                                contentDescription = "Nuevo Empleado"
                            )
                        },
                        modifier = Modifier.padding(NavigationDrawerItemDefaults.ItemPadding)
                    )
                    NavigationDrawerItem(
                        label = { Text("Nuevo Departamento") },
                        selected = selectedSection == AdminSection.NewDepartment,
                        onClick = {
                            selectedSection = AdminSection.NewDepartment
                            scope.launch { drawerState.close() }
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Default.AddBusiness,
                                contentDescription = "Nuevo Departamento"
                            )
                        },
                        modifier = Modifier.padding(NavigationDrawerItemDefaults.ItemPadding)
                    )
                    NavigationDrawerItem(
                        label = { Text("Nuevo Cargo") },
                        selected = selectedSection == AdminSection.NewPosition,
                        onClick = {
                            selectedSection = AdminSection.NewPosition
                            scope.launch { drawerState.close() }
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Default.AddCard,
                                contentDescription = "Nuevo Cargo"
                            )
                        },
                        modifier = Modifier.padding(NavigationDrawerItemDefaults.ItemPadding)
                    )
                    NavigationDrawerItem(
                        label = { Text("Nuevo Administrador") },
                        selected = selectedSection == AdminSection.NewAdmin,
                        onClick = {
                            selectedSection = AdminSection.NewAdmin
                            scope.launch { drawerState.close() }
                        },
                        icon = {
                            Icon(
                                imageVector = Icons.Default.PersonAdd,
                                contentDescription = "Nuevo Administrador"
                            )
                        },
                        modifier = Modifier.padding(NavigationDrawerItemDefaults.ItemPadding)
                    )
                }

                Spacer(modifier = Modifier.weight(1f))
                Spacer(modifier = Modifier.height(16.dp))
            }
        }
    ) {
        Scaffold(
            topBar = {
                DashboardHeader(
                    title = selectedSection.title,
                    onMenuClick = { scope.launch { drawerState.open() } },
                    onProfileClick = { /* Acción de perfil aquí */ },
                    onLogoutClick = { onLogout() }
                )
            }
        ) { paddingValues ->
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(paddingValues)
            ) {
                when (selectedSection) {
                    AdminSection.Dashboard      -> DashboardContent()
                    AdminSection.Employees      -> EmpleadosScreen()
                    AdminSection.Departments    -> DepartamentosScreen()
                    AdminSection.Positions      -> CargosScreen()
                    AdminSection.Admin          -> AdminsScreen()
                    AdminSection.NewEmployee    -> EmpleadoCreateScreen(onVolverLista = { selectedSection = AdminSection.Employees })
                    AdminSection.NewDepartment  -> DepartamentoCreateScreen(onVolverLista = { selectedSection = AdminSection.Departments })
                    AdminSection.NewPosition    -> CargoCreateScreen(onVolverLista = { selectedSection = AdminSection.Positions })
                    AdminSection.NewAdmin       -> AdminCreateScreen(onVolverLista = { selectedSection = AdminSection.Admin })
                    else -> Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                        Text("En construcción… 🚧")
                    }
                }
            }
        }
    }
}

// ------ ENUM DE SECCIONES --------
enum class AdminSection(val title: String) {
    Dashboard("Dashboard"),
    Employees("Empleados"),
    Departments("Departamentos"),
    Positions("Cargos"),
    Admin("Administradores"),
    NewEmployee("Nuevo Empleado"),
    NewDepartment("Nuevo Departamento"),
    NewPosition("Nuevo Cargo"),
    NewAdmin("Nuevo Administrador")
}
