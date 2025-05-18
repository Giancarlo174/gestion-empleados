package com.ds6p1.ds6p1.modules.admin.sections.dashboard

import androidx.compose.foundation.background
import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Business
import androidx.compose.material.icons.filled.Cancel
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.Dashboard
import androidx.compose.material.icons.filled.Logout
import androidx.compose.material.icons.filled.People
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel

data class DashboardStats(
    val totalEmpleados: Int = 0,
    val empleadosActivos: Int = 0,
    val empleadosInactivos: Int = 0,
    val departamentos: List<DepartamentoStat> = emptyList()
)

data class DepartamentoStat(
    val nombre: String,
    val totalEmpleados: Int,
    val porcentaje: Float
)

@Composable
fun DashboardContent(
    showLogout: Boolean = false,
    onLogout: (() -> Unit)? = null,
    modifier: Modifier = Modifier,
    viewModel: DashboardViewModel = viewModel()
) {
    val state by viewModel.state.collectAsState()
    val scrollState = rememberScrollState()

    Surface(
        color = MaterialTheme.colorScheme.background,
        modifier = modifier.fillMaxSize()
    ) {
        Column(
            modifier = Modifier
                .verticalScroll(scrollState)
                .padding(24.dp),
            verticalArrangement = Arrangement.spacedBy(24.dp)
        ) {
            // Header con Logout solo si es el principal
            Row(
                Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Panel de Control",
                    style = MaterialTheme.typography.headlineSmall,
                    fontWeight = FontWeight.Bold
                )
            }

            when (val currentState = state) {
                is DashboardState.Loading -> {
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(200.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        LinearProgressIndicator(
                            modifier = Modifier
                                .fillMaxWidth(0.5f)
                                .height(4.dp)
                                .clip(RoundedCornerShape(2.dp))
                        )
                    }
                }
                is DashboardState.Error -> {
                    Column(
                        horizontalAlignment = Alignment.CenterHorizontally,
                        modifier = Modifier.fillMaxWidth()
                    ) {
                        Text(
                            text = "Error al cargar datos",
                            style = MaterialTheme.typography.bodyLarge,
                            color = MaterialTheme.colorScheme.error,
                            fontWeight = FontWeight.SemiBold
                        )
                        Spacer(Modifier.height(8.dp))
                        Text(
                            text = currentState.message,
                            style = MaterialTheme.typography.bodyMedium,
                            color = MaterialTheme.colorScheme.onBackground,
                            textAlign = TextAlign.Center
                        )
                        Spacer(Modifier.height(16.dp))
                        Button(onClick = { viewModel.loadDashboardStats() }) {
                            Text("Reintentar")
                        }
                    }
                }
                is DashboardState.Success -> {
                    val stats = currentState.data

                    // 1. Tarjetas de estadísticas
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.spacedBy(16.dp)
                    ) {
                        StatCard(
                            title = "Total",
                            count = stats.totalEmpleados.toString(),
                            icon = Icons.Default.People,
                            color = MaterialTheme.colorScheme.primary,
                            modifier = Modifier.weight(1f)
                        )
                        StatCard(
                            title = "Activos",
                            count = stats.empleadosActivos.toString(),
                            icon = Icons.Default.CheckCircle,
                            color = MaterialTheme.colorScheme.tertiary,
                            modifier = Modifier.weight(1f)
                        )
                        StatCard(
                            title = "Inactivos",
                            count = stats.empleadosInactivos.toString(),
                            icon = Icons.Default.Cancel,
                            color = MaterialTheme.colorScheme.error,
                            modifier = Modifier.weight(1f)
                        )
                    }

                    // 2. Distribución por departamento con scroll horizontal
                    Column(
                        verticalArrangement = Arrangement.spacedBy(12.dp),
                        modifier = Modifier.fillMaxWidth()
                    ) {
                        Text(
                            text = "Distribución por Departamento",
                            style = MaterialTheme.typography.titleMedium,
                            fontWeight = FontWeight.Medium
                        )

                        Card(
                            modifier = Modifier.fillMaxWidth(),
                            shape = RoundedCornerShape(12.dp),
                            elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                            colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
                        ) {
                            Box(
                                modifier = Modifier
                                    .horizontalScroll(rememberScrollState())
                                    .padding(vertical = 8.dp)
                            ) {
                                Column {
                                    // Encabezado
                                    Row(
                                        modifier = Modifier
                                            .fillMaxWidth()
                                            .padding(horizontal = 16.dp),
                                        verticalAlignment = Alignment.CenterVertically
                                    ) {
                                        Text(
                                            text = "Departamento",
                                            style = MaterialTheme.typography.bodyLarge,
                                            modifier = Modifier.width(200.dp),
                                            fontWeight = FontWeight.SemiBold
                                        )
                                        Spacer(Modifier.width(24.dp))
                                        Text(
                                            text = "Empleados",
                                            style = MaterialTheme.typography.bodyLarge,
                                            modifier = Modifier.width(100.dp),
                                            textAlign = TextAlign.Center,
                                            fontWeight = FontWeight.SemiBold
                                        )
                                        Spacer(Modifier.width(24.dp))
                                        Text(
                                            text = "Porcentaje",
                                            style = MaterialTheme.typography.bodyLarge,
                                            modifier = Modifier.width(150.dp),
                                            textAlign = TextAlign.Center,
                                            fontWeight = FontWeight.SemiBold
                                        )
                                    }

                                    Divider(modifier = Modifier.padding(vertical = 8.dp))

                                    // Filas
                                    stats.departamentos.forEachIndexed { index, depto ->
                                        Row(
                                            modifier = Modifier
                                                .fillMaxWidth()
                                                .padding(horizontal = 16.dp, vertical = 8.dp),
                                            verticalAlignment = Alignment.CenterVertically
                                        ) {
                                            Text(
                                                text = depto.nombre,
                                                style = MaterialTheme.typography.bodyMedium,
                                                modifier = Modifier.width(200.dp),
                                                maxLines = 1,
                                                overflow = TextOverflow.Ellipsis
                                            )
                                            Spacer(Modifier.width(24.dp))
                                            Text(
                                                text = depto.totalEmpleados.toString(),
                                                style = MaterialTheme.typography.bodyMedium,
                                                modifier = Modifier.width(100.dp),
                                                textAlign = TextAlign.Center
                                            )
                                            Spacer(Modifier.width(24.dp))
                                            Box(modifier = Modifier.width(150.dp)) {
                                                LinearProgressIndicator(
                                                    progress = depto.porcentaje / 100f,
                                                    modifier = Modifier
                                                        .fillMaxWidth()
                                                        .height(8.dp)
                                                        .clip(RoundedCornerShape(4.dp)),
                                                    trackColor = MaterialTheme.colorScheme.surfaceVariant,
                                                    color = MaterialTheme.colorScheme.primary
                                                )
                                                Text(
                                                    text = "${depto.porcentaje.format(1)}%",
                                                    style = MaterialTheme.typography.bodySmall,
                                                    modifier = Modifier
                                                        .align(Alignment.CenterEnd)
                                                        .padding(start = 8.dp)
                                                )
                                            }
                                        }
                                        if (index < stats.departamentos.lastIndex) {
                                            Divider(
                                                modifier = Modifier
                                                    .padding(horizontal = 16.dp)
                                                    .fillMaxWidth(),
                                                color = MaterialTheme.colorScheme.outline
                                            )
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun StatCard(
    title: String,
    count: String,
    icon: ImageVector,
    color: Color,
    modifier: Modifier = Modifier
) {
    Card(
        modifier = modifier.height(140.dp),
        shape = RoundedCornerShape(12.dp),
        elevation = CardDefaults.cardElevation(defaultElevation = 4.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
    ) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(16.dp),
            verticalArrangement = Arrangement.SpaceBetween
        ) {
            Text(
                text = title,
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Text(
                text = count,
                style = MaterialTheme.typography.headlineMedium,
                fontWeight = FontWeight.Bold,
                color = color
            )
            Box(
                modifier = Modifier
                    .size(36.dp)
                    .background(color.copy(alpha = 0.1f), RoundedCornerShape(18.dp)),
                contentAlignment = Alignment.Center
            ) {
                Icon(
                    imageVector = icon,
                    contentDescription = title,
                    tint = color,
                    modifier = Modifier.size(20.dp)
                )
            }
        }
    }
}

// Extensión para formatear floats con decimales
private fun Float.format(decimals: Int): String =
    "%.${decimals}f".format(this)
