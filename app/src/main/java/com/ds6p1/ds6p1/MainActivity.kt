package com.ds6p1.ds6p1

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.lifecycleScope
import com.ds6p1.ds6p1.api.DashboardStatsResponse
import com.ds6p1.ds6p1.api.DepartmentStats
import com.ds6p1.ds6p1.api.RetrofitClient
import com.ds6p1.ds6p1.ui.theme.Ds6p1Theme
import kotlinx.coroutines.launch
import retrofit2.HttpException
import java.io.IOException
import androidx.compose.foundation.clickable

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // Get user info from intent
        val userEmail = intent.getStringExtra("USER_EMAIL") ?: "usuario@example.com"
        val userName = intent.getStringExtra("USER_NAME") ?: "Administrador"
        
        setContent {
            Ds6p1Theme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    AdminScreen(userName, userEmail)
                }
            }
        }
    }
    
    @OptIn(ExperimentalMaterial3Api::class)
    @Composable
    fun AdminScreen(userName: String, userEmail: String) {
        val drawerState = rememberDrawerState(initialValue = DrawerValue.Closed)
        val scope = rememberCoroutineScope()
        
        // Estado para controlar la visibilidad del menú desplegable de perfil
        var showProfileMenu by rememberSaveable { mutableStateOf(false) }
        
        // Estado para almacenar las estadísticas del dashboard
        var dashboardStats by remember { mutableStateOf<DashboardStatsResponse?>(null) }
        var isLoading by remember { mutableStateOf(true) }
        var errorMessage by remember { mutableStateOf<String?>(null) }
        
        // Cargar las estadísticas del dashboard
        LaunchedEffect(key1 = true) {
            loadDashboardStats(
                onSuccess = { stats -> 
                    dashboardStats = stats
                    isLoading = false 
                },
                onError = { message -> 
                    errorMessage = message
                    isLoading = false 
                }
            )
        }
        
        ModalNavigationDrawer(
            drawerState = drawerState,
            drawerContent = {
                ModalDrawerSheet(
                    modifier = Modifier.width(300.dp)
                ) {
                    // Eliminar el bloque de bienvenida y simplificar el sidebar
                    Spacer(modifier = Modifier.height(12.dp))
                    
                    // Sección principal
                    NavigationDrawerItem(
                        label = { Text("Dashboard") },
                        icon = { Icon(Icons.Default.Dashboard, contentDescription = "Dashboard") },
                        selected = true,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                    
                    NavigationDrawerItem(
                        label = { Text("Empleados") },
                        icon = { Icon(Icons.Default.Groups, contentDescription = "Empleados") },
                        selected = false,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                    
                    NavigationDrawerItem(
                        label = { Text("Departamentos") },
                        icon = { Icon(Icons.Default.Business, contentDescription = "Departamentos") },
                        selected = false,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                    
                    NavigationDrawerItem(
                        label = { Text("Cargos") },
                        icon = { Icon(Icons.Default.Work, contentDescription = "Cargos") },
                        selected = false,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                    
                    NavigationDrawerItem(
                        label = { Text("Administradores") },
                        icon = { Icon(Icons.Default.AdminPanelSettings, contentDescription = "Administradores") },
                        selected = false,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                    
                    Spacer(modifier = Modifier.height(16.dp))
                    Divider(modifier = Modifier.padding(vertical = 8.dp))
                    
                    // Sección de Administración
                    Text(
                        text = "Administración",
                        color = MaterialTheme.colorScheme.primary,
                        fontWeight = FontWeight.Bold,
                        fontSize = 14.sp,
                        modifier = Modifier.padding(start = 28.dp, top = 8.dp, bottom = 8.dp)
                    )
                    
                    NavigationDrawerItem(
                        label = { Text("Nuevo Empleado") },
                        icon = { Icon(Icons.Default.PersonAdd, contentDescription = "Nuevo Empleado") },
                        selected = false,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                    
                    NavigationDrawerItem(
                        label = { Text("Nuevo Departamento") },
                        icon = { Icon(Icons.Default.AddBusiness, contentDescription = "Nuevo Departamento") },
                        selected = false,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                    
                    NavigationDrawerItem(
                        label = { Text("Nuevo Cargo") },
                        icon = { Icon(Icons.Default.AddTask, contentDescription = "Nuevo Cargo") },
                        selected = false,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                    
                    NavigationDrawerItem(
                        label = { Text("Nuevo Administrador") },
                        icon = { Icon(Icons.Default.SupervisorAccount, contentDescription = "Nuevo Administrador") },
                        selected = false,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                }
            },
            content = {
                Scaffold(
                    topBar = {
                        TopAppBar(
                            title = { Text("Sistema de Gestión") },
                            navigationIcon = {
                                IconButton(onClick = { scope.launch { drawerState.open() } }) {
                                    Icon(Icons.Default.Menu, contentDescription = "Menu")
                                }
                            },
                            actions = {
                                // Añadir icono de perfil con menú desplegable
                                Box {
                                    IconButton(onClick = { showProfileMenu = !showProfileMenu }) {
                                        Icon(
                                            imageVector = Icons.Default.AccountCircle, 
                                            contentDescription = "Perfil",
                                            modifier = Modifier.size(28.dp)
                                        )
                                    }
                                    
                                    // Menú desplegable del perfil
                                    DropdownMenu(
                                        expanded = showProfileMenu,
                                        onDismissRequest = { showProfileMenu = false },
                                        modifier = Modifier.width(180.dp)
                                    ) {
                                        // Información del usuario
                                        Column(
                                            modifier = Modifier
                                                .fillMaxWidth()
                                                .padding(horizontal = 16.dp, vertical = 8.dp),
                                            horizontalAlignment = Alignment.CenterHorizontally
                                        ) {
                                            Text(
                                                text = userName,
                                                fontWeight = FontWeight.Bold,
                                                style = MaterialTheme.typography.bodyLarge
                                            )
                                            Text(
                                                text = userEmail,
                                                style = MaterialTheme.typography.bodySmall,
                                                color = MaterialTheme.colorScheme.onSurfaceVariant
                                            )
                                        }
                                        
                                        Divider()
                                        
                                        // Opción Mi Perfil
                                        DropdownMenuItem(
                                            text = { Text("Mi Perfil") },
                                            leadingIcon = { 
                                                Icon(
                                                    Icons.Default.Person, 
                                                    contentDescription = "Mi Perfil"
                                                ) 
                                            },
                                            onClick = { 
                                                showProfileMenu = false
                                                // Aquí iría la navegación al perfil
                                            }
                                        )
                                        
                                        // Opción Cerrar Sesión
                                        DropdownMenuItem(
                                            text = { Text("Cerrar Sesión") },
                                            leadingIcon = { 
                                                Icon(
                                                    Icons.Default.Logout, 
                                                    contentDescription = "Cerrar Sesión"
                                                ) 
                                            },
                                            onClick = { 
                                                // Crear un intent para volver a LoginActivity
                                                val intent = Intent(this@MainActivity, LoginActivity::class.java)
                                                // Limpiar todas las actividades anteriores de la pila
                                                intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                                                startActivity(intent)
                                            }
                                        )
                                    }
                                }
                            }
                        )
                    }
                ) { paddingValues ->
                    // Contenido principal del dashboard
                    LazyColumn(
                        modifier = Modifier
                            .fillMaxSize()
                            .padding(paddingValues)
                            .padding(horizontal = 16.dp),
                        verticalArrangement = Arrangement.spacedBy(16.dp)
                    ) {
                        item {
                            Text(
                                text = "Panel de Administración",
                                fontSize = 24.sp,
                                fontWeight = FontWeight.Bold,
                                color = MaterialTheme.colorScheme.primary,
                                textAlign = TextAlign.Center,
                                modifier = Modifier
                                    .fillMaxWidth()
                                    .padding(vertical = 16.dp)
                            )
                        }
                        
                        // Mostrar spinner de carga si está cargando
                        if (isLoading) {
                            item {
                                Box(
                                    contentAlignment = Alignment.Center,
                                    modifier = Modifier
                                        .fillMaxWidth()
                                        .height(200.dp)
                                ) {
                                    CircularProgressIndicator()
                                }
                            }
                        }
                        
                        // Mostrar mensaje de error si ocurre algún error
                        else if (errorMessage != null) {
                            item {
                                Box(
                                    contentAlignment = Alignment.Center,
                                    modifier = Modifier
                                        .fillMaxWidth()
                                        .padding(16.dp)
                                ) {
                                    Text(
                                        text = "Error: $errorMessage",
                                        color = MaterialTheme.colorScheme.error
                                    )
                                }
                            }
                        }
                        
                        // Mostrar estadísticas si hay datos
                        else if (dashboardStats != null) {
                            // Stats Cards - Row with 3 cards
                            item {
                                Row(
                                    modifier = Modifier.fillMaxWidth(),
                                    horizontalArrangement = Arrangement.spacedBy(8.dp)
                                ) {
                                    // Total Employees Card
                                    DashboardStatCard(
                                        icon = Icons.Default.Groups,
                                        value = dashboardStats?.total_employees ?: 0,
                                        label = "Total Empleados",
                                        subLabel = "Ver todos los empleados",
                                        modifier = Modifier.weight(1f),
                                        onClick = { /* Navigate to all employees */ }
                                    )
                                    
                                    // Active Employees Card
                                    DashboardStatCard(
                                        icon = Icons.Default.CheckCircle,
                                        value = dashboardStats?.active_employees ?: 0,
                                        label = "Empleados Activos",
                                        subLabel = "Ver empleados activos",
                                        modifier = Modifier.weight(1f),
                                        cardColor = MaterialTheme.colorScheme.primaryContainer,
                                        onClick = { /* Navigate to active employees */ }
                                    )
                                    
                                    // Inactive Employees Card
                                    DashboardStatCard(
                                        icon = Icons.Default.DoNotDisturb,
                                        value = dashboardStats?.inactive_employees ?: 0,
                                        label = "Empleados Inactivos",
                                        subLabel = "Ver empleados inactivos",
                                        modifier = Modifier.weight(1f),
                                        cardColor = MaterialTheme.colorScheme.errorContainer,
                                        onClick = { /* Navigate to inactive employees */ }
                                    )
                                }
                            }
                            
                            // Departments Distribution Title
                            item {
                                Text(
                                    text = "Distribución por Departamento",
                                    fontSize = 18.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = MaterialTheme.colorScheme.primary,
                                    modifier = Modifier.padding(top = 16.dp, bottom = 8.dp)
                                )
                            }
                            
                            // Departments Table
                            item {
                                DepartmentsTable(departments = dashboardStats?.departments ?: emptyList())
                            }
                        }
                    }
                }
            }
        )
    }
    
    @Composable
    fun DashboardStatCard(
        icon: androidx.compose.ui.graphics.vector.ImageVector,
        value: Int,
        label: String,
        subLabel: String,
        modifier: Modifier = Modifier,
        cardColor: Color = MaterialTheme.colorScheme.surface,
        onClick: () -> Unit
    ) {
        Card(
            modifier = modifier.clickable { onClick() },
            colors = CardDefaults.cardColors(containerColor = cardColor)
        ) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                Icon(
                    imageVector = icon,
                    contentDescription = label,
                    tint = MaterialTheme.colorScheme.primary,
                    modifier = Modifier.size(32.dp)
                )
                
                Text(
                    text = value.toString(),
                    fontSize = 24.sp,
                    fontWeight = FontWeight.Bold,
                    color = MaterialTheme.colorScheme.onSurface
                )
                
                Text(
                    text = label,
                    fontSize = 14.sp,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    textAlign = TextAlign.Center
                )
                
                Spacer(modifier = Modifier.height(8.dp))
                
                Text(
                    text = subLabel,
                    fontSize = 12.sp,
                    color = MaterialTheme.colorScheme.primary,
                    textAlign = TextAlign.Center,
                    modifier = Modifier.padding(top = 4.dp)
                )
            }
        }
    }
    
    @Composable
    fun DepartmentsTable(departments: List<DepartmentStats>) {
        Card {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp)
            ) {
                // Table Header
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .background(MaterialTheme.colorScheme.primaryContainer)
                        .padding(8.dp)
                ) {
                    Text(
                        text = "Departamento",
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier.weight(2f),
                        color = MaterialTheme.colorScheme.onPrimaryContainer
                    )
                    Text(
                        text = "Empleados",
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier.weight(1f),
                        textAlign = TextAlign.Center,
                        color = MaterialTheme.colorScheme.onPrimaryContainer
                    )
                    Text(
                        text = "Porcentaje",
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier.weight(1f),
                        textAlign = TextAlign.End,
                        color = MaterialTheme.colorScheme.onPrimaryContainer
                    )
                }
                
                // Table Rows
                departments.forEach { department ->
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(vertical = 8.dp, horizontal = 8.dp)
                    ) {
                        Text(
                            text = department.nombre,
                            modifier = Modifier.weight(2f),
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                        Text(
                            text = department.employee_count.toString(),
                            modifier = Modifier.weight(1f),
                            textAlign = TextAlign.Center
                        )
                        Text(
                            text = "${department.percentage}%",
                            modifier = Modifier.weight(1f),
                            textAlign = TextAlign.End
                        )
                    }
                    Divider()
                }
                
                // Empty state if no departments
                if (departments.isEmpty()) {
                    Box(
                        contentAlignment = Alignment.Center,
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(16.dp)
                    ) {
                        Text(
                            text = "No hay departamentos disponibles",
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }
            }
        }
    }
    
    @OptIn(ExperimentalMaterial3Api::class)
    @Composable
    private fun CardRow(content: @Composable RowScope.() -> Unit) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(vertical = 8.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp),
            content = content
        )
    }
    
    private fun loadDashboardStats(
        onSuccess: (DashboardStatsResponse) -> Unit,
        onError: (String) -> Unit
    ) {
        lifecycleScope.launch {
            try {
                val response = RetrofitClient.apiService.getDashboardStats()
                if (response.isSuccessful) {
                    val stats = response.body()
                    if (stats != null && stats.success) {
                        onSuccess(stats)
                    } else {
                        onError(stats?.message ?: "Error al cargar estadísticas")
                    }
                } else {
                    onError("Error ${response.code()}: ${response.message()}")
                }
            } catch (e: IOException) {
                onError("Error de conexión. Verifique su conexión a internet o que el servidor XAMPP esté activo")
            } catch (e: HttpException) {
                onError("Error ${e.code()}: ${e.message()}")
            } catch (e: Exception) {
                onError("Error inesperado: ${e.message}")
            }
        }
    }
    
    @Composable
    fun StatisticItem(
        icon: androidx.compose.ui.graphics.vector.ImageVector,
        value: String,
        label: String,
        modifier: Modifier = Modifier
    ) {
        Column(
            modifier = modifier.padding(8.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Icon(
                imageVector = icon,
                contentDescription = label,
                tint = MaterialTheme.colorScheme.primary,
                modifier = Modifier.size(32.dp)
            )
            
            Text(
                text = value,
                fontSize = 20.sp,
                fontWeight = FontWeight.Bold
            )
            
            Text(
                text = label,
                fontSize = 14.sp,
                color = Color.Gray
            )
        }
    }
}