package com.ds6p1.ds6p1.modules.employee

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.ds6p1.ds6p1.api.EmpleadoDetalle

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun EmpleadoPerfilScreen(
    cedula: String,
    onBack: () -> Unit,
    onLogout: () -> Unit = {},
    onEditar: (String) -> Unit = {},
    viewModel: EmpleadoPerfilViewModel = viewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    val scrollState = rememberScrollState()

    LaunchedEffect(cedula) { viewModel.loadDetalle(cedula) }

    Surface(
        modifier = Modifier.fillMaxSize(),
        color = MaterialTheme.colorScheme.background
    ) {
        when (uiState) {
            is EmpleadoDetalleUiState.Loading -> Box(Modifier.fillMaxSize(), Alignment.Center) {
                CircularProgressIndicator()
            }
            is EmpleadoDetalleUiState.Error -> Box(Modifier.fillMaxSize(), Alignment.Center) {
                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                    Text(
                        (uiState as EmpleadoDetalleUiState.Error).message,
                        color = MaterialTheme.colorScheme.error
                    )
                    Spacer(Modifier.height(8.dp))
                    Button(onClick = onBack) { Text("Volver") }
                }
            }
            is EmpleadoDetalleUiState.Success -> {
                val e = (uiState as EmpleadoDetalleUiState.Success).empleado

                Column(
                    Modifier
                        .verticalScroll(scrollState)
                        .fillMaxSize()
                        .padding(horizontal = 16.dp, vertical = 16.dp),
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Row(
                        Modifier
                            .fillMaxWidth()
                            .padding(bottom = 8.dp),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.SpaceBetween
                    ) {
                        Text("Mi Perfil", style = MaterialTheme.typography.titleLarge)
                        
                        TextButton(
                            onClick = onLogout,
                            colors = ButtonDefaults.textButtonColors(
                                contentColor = MaterialTheme.colorScheme.error
                            )
                        ) {
                            Icon(Icons.Default.Logout, contentDescription = "Cerrar sesión")
                            Spacer(Modifier.width(4.dp))
                            Text("Cerrar sesión")
                        }
                    }

                    Card(
                        Modifier
                            .fillMaxWidth()
                            .padding(vertical = 8.dp),
                        shape = RoundedCornerShape(12.dp),
                        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
                    ) {
                        Box(Modifier.fillMaxWidth()) {
                            IconButton(
                                onClick = { onEditar(cedula) },
                                modifier = Modifier
                                    .align(Alignment.TopEnd)
                                    .padding(4.dp)
                            ) {
                                Icon(
                                    Icons.Default.Edit, 
                                    contentDescription = "Editar perfil",
                                    tint = MaterialTheme.colorScheme.primary
                                )
                            }
                            
                            Column(
                                Modifier
                                    .fillMaxWidth()
                                    .padding(vertical = 20.dp),
                                horizontalAlignment = Alignment.CenterHorizontally
                            ) {
                                Box(
                                    Modifier
                                        .size(80.dp)
                                        .clip(CircleShape)
                                        .background(Color(0xFF2196F3)),
                                    contentAlignment = Alignment.Center
                                ) {
                                    Icon(Icons.Default.Person, contentDescription = null, modifier = Modifier.size(50.dp), tint = Color.White)
                                }
                                Spacer(Modifier.height(8.dp))
                                Text(
                                    "${e.nombre1} ${e.nombre2.orEmpty()} ${e.apellido1} ${e.apellido2.orEmpty()}".trim(),
                                    style = MaterialTheme.typography.titleLarge.copy(fontWeight = FontWeight.Bold),
                                    color = MaterialTheme.colorScheme.onSurface,
                                    modifier = Modifier.padding(bottom = 2.dp)
                                )
                                Row(
                                    Modifier.padding(top = 4.dp, bottom = 4.dp),
                                    verticalAlignment = Alignment.CenterVertically
                                ) {
                                    ChipView(label = e.nombre_cargo ?: "", color = Color(0xFF00BCD4))
                                    Spacer(Modifier.width(8.dp))
                                    ChipView(label = e.nombre_departamento ?: "", color = Color(0xFF757575))
                                }
                                Spacer(Modifier.height(2.dp))
                                Row(
                                    Modifier.fillMaxWidth(),
                                    horizontalArrangement = Arrangement.Center
                                ) {
                                    Icon(Icons.Default.CreditCard, contentDescription = null, tint = Color.Gray, modifier = Modifier.size(18.dp))
                                    Spacer(Modifier.width(4.dp))
                                    Text(e.cedula, fontSize = 15.sp, color = Color.Gray)
                                }
                                Row(
                                    Modifier.fillMaxWidth(),
                                    horizontalArrangement = Arrangement.Center
                                ) {
                                    Icon(Icons.Default.Email, contentDescription = null, tint = Color.Gray, modifier = Modifier.size(18.dp))
                                    Spacer(Modifier.width(4.dp))
                                    Text(e.correo.orEmpty(), fontSize = 15.sp, color = Color.Gray)
                                }
                                Row(
                                    Modifier.fillMaxWidth(),
                                    horizontalArrangement = Arrangement.Center
                                ) {
                                    Icon(Icons.Default.Phone, contentDescription = null, tint = Color.Gray, modifier = Modifier.size(18.dp))
                                    Spacer(Modifier.width(4.dp))
                                    Text(e.celular.orEmpty(), fontSize = 15.sp, color = Color.Gray)
                                }
                                Spacer(Modifier.height(10.dp))
                                ChipView(
                                    label = if (e.estado == 1) "Activo" else "Inactivo",
                                    color = if (e.estado == 1) Color(0xFF43A047) else Color.Gray
                                )
                            }
                        }
                    }

                    InfoCard(
                        icon = Icons.Default.Info,
                        title = "Información Personal"
                    ) {
                        InfoLabelValue("Nombres:", "${e.nombre1} ${e.nombre2.orEmpty()}")
                        InfoLabelValue("Apellidos:", "${e.apellido1} ${e.apellido2.orEmpty()}")
                        InfoLabelValue("Género:", if (e.genero == 1) "Masculino" else "Femenino")
                        InfoLabelValue(
                            "Estado Civil:",
                            listOf("Soltero/a", "Casado/a", "Viudo/a", "Divorciado/a").getOrNull(e.estado_civil) ?: "No definido"
                        )
                        InfoLabelValue("Fecha de Nacimiento:", e.f_nacimiento.orEmpty())
                        InfoLabelValue("Nacionalidad:", e.nacionalidad_nombre.orEmpty())
                        InfoLabelValue("Tipo de Sangre:", e.tipo_sangre.orEmpty())
                    }

                    InfoCard(
                        icon = Icons.Default.LocationOn,
                        title = "Dirección y Contacto"
                    ) {
                        InfoLabelValue(
                            "Dirección:",
                            listOfNotNull(
                                e.provincia_nombre?.takeIf { it.isNotBlank() },
                                e.distrito_nombre?.takeIf { it.isNotBlank() },
                                e.corregimiento_nombre?.takeIf { it.isNotBlank() },
                                e.calle?.takeIf { it.isNotBlank() },
                                e.comunidad?.takeIf { it.isNotBlank() },
                                e.casa?.takeIf { it.isNotBlank() }
                            ).joinToString(", ").ifEmpty { "No especificada" }
                        )
                        InfoLabelValue("Celular:", e.celular?.takeIf { it.isNotBlank() } ?: "No especificado")
                        InfoLabelValue("Teléfono Fijo:", e.telefono?.takeIf { it.isNotBlank() } ?: "No especificado")
                        InfoLabelValue("Correo Electrónico:", e.correo?.takeIf { it.isNotBlank() } ?: "No especificado")
                    }

                    InfoCard(
                        icon = Icons.Default.Work,
                        title = "Información Laboral"
                    ) {
                        InfoLabelValue("Cargo:", e.nombre_cargo.orEmpty())
                        InfoLabelValue("Departamento:", e.nombre_departamento.orEmpty())
                        InfoLabelValue("Fecha de Contratación:", e.f_contra.orEmpty())
                        InfoLabelValue("Estado:", if (e.estado == 1) "Activo" else "Inactivo")
                    }
                }
            }
        }
    }
}

@Composable
fun ChipView(label: String, color: Color) {
    Surface(
        color = color,
        shape = RoundedCornerShape(50),
        modifier = Modifier.padding(horizontal = 2.dp)
    ) {
        Text(
            label,
            Modifier.padding(horizontal = 10.dp, vertical = 3.dp),
            color = Color.White,
            fontSize = 13.sp,
            maxLines = 1
        )
    }
}

@Composable
fun InfoCard(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    title: String,
    content: @Composable ColumnScope.() -> Unit
) {
    Card(
        Modifier
            .fillMaxWidth()
            .padding(vertical = 8.dp),
        shape = RoundedCornerShape(12.dp),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(Modifier.padding(18.dp)) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(icon, contentDescription = null, tint = MaterialTheme.colorScheme.primary, modifier = Modifier.size(22.dp))
                Spacer(Modifier.width(8.dp))
                Text(title, fontWeight = FontWeight.Bold, fontSize = 17.sp)
            }
            Spacer(Modifier.height(8.dp))
            content()
        }
    }
}

@Composable
fun InfoLabelValue(label: String, value: String) {
    Row(Modifier.padding(bottom = 3.dp)) {
        Text(label, fontWeight = FontWeight.SemiBold)
        Spacer(Modifier.width(6.dp))
        Text(value)
    }
}
