// SettingsScreen.kt

package com.ds6p1.ds6p1.modules.admin.sections.ajustes

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.filled.Logout
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.ds6p1.ds6p1.modules.admin.AdminInfo

@Composable
fun SettingsScreen(
    adminInfo: AdminInfo,
    modifier: Modifier = Modifier,
    onLogout: () -> Unit
) {
    Column(
        modifier = modifier
            .fillMaxSize()
            .padding(24.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        Text("Ajustes", style = MaterialTheme.typography.headlineSmall)
        Divider()
        Text("Mi Perfil", style = MaterialTheme.typography.titleMedium)
        Text("Cédula: ${adminInfo.cedula}")
        Text("Correo: ${adminInfo.correoInstitucional}")
        // Aquí puedes poner más ajustes

        Spacer(Modifier.height(24.dp))
        Button(
            onClick = onLogout,
            colors = ButtonDefaults.buttonColors(
                containerColor = MaterialTheme.colorScheme.errorContainer,
                contentColor = MaterialTheme.colorScheme.onErrorContainer
            ),
            shape = RoundedCornerShape(8.dp),
            contentPadding = PaddingValues(horizontal = 16.dp, vertical = 10.dp),
            modifier = Modifier.width(170.dp)
        ) {
            Icon(
                Icons.Default.Logout,
                contentDescription = "Salir",
                modifier = Modifier.size(18.dp)
            )
            Spacer(Modifier.width(8.dp))
            Text(
                "Cerrar sesión",
                style = MaterialTheme.typography.bodyMedium.copy(
                    fontWeight = FontWeight.Medium
                )
            )
        }
    }
}
