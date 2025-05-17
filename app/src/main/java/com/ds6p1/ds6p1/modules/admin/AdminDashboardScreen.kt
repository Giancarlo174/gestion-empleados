package com.ds6p1.ds6p1.modules.admin

import androidx.compose.runtime.Composable
import com.ds6p1.ds6p1.modules.admin.sections.dashboard.DashboardContent
import com.ds6p1.ds6p1.modules.admin.sections.empleados.EmpleadosScreen
import com.ds6p1.ds6p1.modules.admin.sections.ajustes.SettingsScreen // Asegúrate de crear este archivo
import com.ds6p1.ds6p1.modules.admin.sections.organizacion.OrganizacionScreen // Asegúrate de crear este archivo

@Composable
fun AdminDashboardScreen(
    adminInfo: AdminInfo,
    onLogout: () -> Unit
) {
    // La barra inferior controla toda la navegación principal.
    MainBottomBar(
        adminInfo = adminInfo,
        onLogout = onLogout
    )
}
