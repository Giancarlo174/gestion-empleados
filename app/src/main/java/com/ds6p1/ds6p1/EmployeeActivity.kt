package com.ds6p1.ds6p1

import android.content.Intent
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.ds6p1.ds6p1.ui.theme.Ds6p1Theme
import kotlinx.coroutines.launch

class EmployeeActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // Get user info from intent
        val userEmail = intent.getStringExtra("USER_EMAIL") ?: "usuario@example.com"
        val userName = intent.getStringExtra("USER_NAME") ?: "Empleado"
        
        setContent {
            Ds6p1Theme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    EmployeeScreen(userName, userEmail)
                }
            }
        }
    }
    
    @OptIn(ExperimentalMaterial3Api::class)
    @Composable
    fun EmployeeScreen(userName: String, userEmail: String) {
        val drawerState = rememberDrawerState(initialValue = DrawerValue.Closed)
        val scope = rememberCoroutineScope()
        
        // Estado para controlar la visibilidad del menú desplegable de perfil
        var showProfileMenu by rememberSaveable { mutableStateOf(false) }
        
        ModalNavigationDrawer(
            drawerState = drawerState,
            drawerContent = {
                ModalDrawerSheet(
                    modifier = Modifier.width(300.dp)
                ) {
                    // Simplificar el sidebar
                    Spacer(modifier = Modifier.height(12.dp))
                    
                    // Navigation items
                    NavigationDrawerItem(
                        label = { Text("Mi Perfil") },
                        icon = { Icon(Icons.Default.Person, contentDescription = "Perfil") },
                        selected = true,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                    
                    NavigationDrawerItem(
                        label = { Text("Solicitudes") },
                        icon = { Icon(Icons.Default.Description, contentDescription = "Solicitudes") },
                        selected = false,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                    
                    NavigationDrawerItem(
                        label = { Text("Calendario") },
                        icon = { Icon(Icons.Default.DateRange, contentDescription = "Calendario") },
                        selected = false,
                        onClick = { scope.launch { drawerState.close() } }
                    )
                }
            },
            content = {
                Scaffold(
                    topBar = {
                        TopAppBar(
                            title = { Text("Portal del Empleado") },
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
                                                val intent = Intent(this@EmployeeActivity, LoginActivity::class.java)
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
                    Column(
                        modifier = Modifier
                            .fillMaxSize()
                            .padding(paddingValues)
                            .padding(16.dp),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {
                        Text(
                            text = "Empleado",
                            fontSize = 24.sp,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.tertiary,
                            textAlign = TextAlign.Center,
                            modifier = Modifier.padding(bottom = 24.dp)
                        )
                        
                        // Contenido del panel de empleado
                        Card(
                            modifier = Modifier
                                .fillMaxWidth()
                                .padding(vertical = 8.dp)
                        ) {
                            Column(
                                modifier = Modifier
                                    .fillMaxWidth()
                                    .padding(16.dp)
                            ) {
                                Text(
                                    text = "Mi Información",
                                    fontSize = 18.sp,
                                    fontWeight = FontWeight.Bold
                                )
                                
                                Spacer(modifier = Modifier.height(16.dp))
                                
                                Text("Email: $userEmail")
                                Text("Nombre: $userName")
                                Text("Departamento: [Pendiente]")
                                Text("Cargo: [Pendiente]")
                            }
                        }
                    }
                }
            }
        )
    }
}
