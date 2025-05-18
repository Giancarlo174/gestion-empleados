package com.ds6p1.ds6p1.modules.admin.sections.organizacion

import androidx.compose.foundation.layout.Column
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import com.ds6p1.ds6p1.modules.admin.sections.departamentos.DepartmentContent

@Composable
fun OrganizacionScreen(
    modifier: Modifier = Modifier,
    onNuevoDepartamento: () -> Unit = {}
) {
    Column(modifier) {
        DepartmentContent(onCreate = onNuevoDepartamento)
    }
}
