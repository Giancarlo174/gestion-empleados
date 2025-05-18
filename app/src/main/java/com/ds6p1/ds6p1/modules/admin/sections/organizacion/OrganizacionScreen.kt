package com.ds6p1.ds6p1.modules.admin.sections.organizacion

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.compose.ui.text.font.FontWeight
import com.ds6p1.ds6p1.modules.admin.sections.departamentos.DepartmentContent
import com.ds6p1.ds6p1.modules.admin.sections.cargos.CargosContent

@Composable
fun OrganizacionScreen(
    modifier: Modifier = Modifier,
    onNuevoDepartamento: () -> Unit = {},
    onNuevoCargo: () -> Unit = {},
) {
    var selectedTab by remember { mutableStateOf(0) }
    val tabs = listOf("Departamentos", "Cargos")

    Column(modifier) {
        SegmentedButtonRow(selectedTab, tabs) { selectedTab = it }
        when (selectedTab) {
            0 -> DepartmentContent(onCreate = onNuevoDepartamento)
            1 -> CargosContent(onCreate = onNuevoCargo)
        }
    }
}

@Composable
fun SegmentedButtonRow(selected: Int, labels: List<String>, onTabSelected: (Int) -> Unit) {
    Row(
        Modifier
            .padding(vertical = 16.dp)
    ) {
        labels.forEachIndexed { index, label ->
            val selectedColor = if (selected == index) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.surface
            val contentColor = if (selected == index) MaterialTheme.colorScheme.onPrimary else MaterialTheme.colorScheme.onSurface
            Button(
                onClick = { onTabSelected(index) },
                colors = ButtonDefaults.buttonColors(
                    containerColor = selectedColor,
                    contentColor = contentColor
                ),
                modifier = Modifier.weight(1f),
                shape = MaterialTheme.shapes.medium,
                elevation = ButtonDefaults.buttonElevation(0.dp),
                border = if (selected == index) null else ButtonDefaults.outlinedButtonBorder
            ) {
                Text(
                    text = label,
                    fontWeight = if (selected == index) FontWeight.Bold else FontWeight.Normal
                )
            }
        }
    }
}
