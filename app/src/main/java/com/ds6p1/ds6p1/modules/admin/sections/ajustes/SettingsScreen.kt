package com.ds6p1.ds6p1.modules.admin.sections.ajustes

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowForwardIos
import androidx.compose.material.icons.filled.Group
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.Logout
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.ds6p1.ds6p1.modules.admin.AdminInfo

@Composable
fun AjustesMenuScreenModern(
    adminInfo: AdminInfo,
    onPerfil: () -> Unit,
    onAdministradores: () -> Unit,
    onCambiarPass: () -> Unit,
    onLogout: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(top = 32.dp, start = 20.dp, end = 20.dp),
        verticalArrangement = Arrangement.spacedBy(10.dp)
    ) {
        Text(
            text = "Ajustes",
            style = MaterialTheme.typography.titleLarge,
            fontWeight = FontWeight.Bold,
            modifier = Modifier.padding(bottom = 8.dp)
        )

        Spacer(Modifier.height(4.dp))

        // --- GENERAL ---
        Text(
            text = "GENERAL",
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.primary.copy(alpha = 0.7f),
            modifier = Modifier.padding(top = 10.dp, bottom = 2.dp)
        )
        SettingMenuItem(
            icon = Icons.Default.Person,
            title = "Mi Perfil",
            subtitle = "Cédula: ${adminInfo.cedula}",
            onClick = onPerfil
        )
        SettingMenuItem(
            icon = Icons.Default.Group,
            title = "Administradores",
            onClick = onAdministradores
        )
        SettingMenuItem(
            icon = Icons.Default.Lock,
            title = "Cambiar contraseña",
            onClick = onCambiarPass
        )

        Spacer(Modifier.weight(1f))
        Button(
            onClick = onLogout,
            colors = ButtonDefaults.buttonColors(
                containerColor = MaterialTheme.colorScheme.errorContainer,
                contentColor = MaterialTheme.colorScheme.onErrorContainer
            ),
            modifier = Modifier
                .fillMaxWidth()
                .padding(bottom = 10.dp)
        ) {
            Icon(Icons.Default.Logout, contentDescription = "Cerrar sesión")
            Spacer(Modifier.width(8.dp))
            Text("Cerrar sesión", fontWeight = FontWeight.Bold)
        }
    }
}

@Composable
fun SettingMenuItem(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    title: String,
    subtitle: String? = null,
    onClick: () -> Unit
) {
    Row(
        Modifier
            .fillMaxWidth()
            .clickable { onClick() }
            .padding(vertical = 12.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Icon(icon, contentDescription = null, modifier = Modifier.size(25.dp))
        Spacer(Modifier.width(14.dp))
        Column(Modifier.weight(1f)) {
            Text(title, style = MaterialTheme.typography.bodyLarge, fontWeight = FontWeight.Medium)
            subtitle?.let {
                Text(
                    it,
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
        }
        Icon(Icons.Default.ArrowForwardIos, contentDescription = null, modifier = Modifier.size(17.dp))
    }
}
