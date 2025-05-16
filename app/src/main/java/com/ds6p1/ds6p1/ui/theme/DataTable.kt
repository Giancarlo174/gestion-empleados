package com.ds6p1.ds6p1.ui.theme

import androidx.compose.foundation.background
import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.Dp

@Composable
fun DataTable(
    columns: List<String>,
    rows: List<List<String>>,
    actions: (@Composable RowScope.(rowIndex: Int) -> Unit)? = null,
    columnWidths: List<Dp>? = null, // Puedes pasar anchos custom si quieres
    modifier: Modifier = Modifier
) {
    val hScroll = rememberScrollState()
    val defaultWidth = 120.dp

    // Usa los anchos dados o uno por columna por defecto
    val widths = columnWidths ?: List(columns.size) { defaultWidth }

    // Calcula el ancho total mínimo para el scroll
    val totalWidth = widths.fold(0.dp) { acc, w -> acc + w } + if (actions != null) 90.dp else 0.dp

    Box(
        modifier
            .fillMaxWidth()
            .horizontalScroll(hScroll)
            .widthIn(min = totalWidth)
    ) {
        Column {
            // Encabezado
            Row(
                Modifier
                    .background(MaterialTheme.colorScheme.primaryContainer)
                    .padding(vertical = 12.dp, horizontal = 16.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                columns.forEachIndexed { i, col ->
                    Text(
                        col,
                        modifier = Modifier.width(widths[i]),
                        style = MaterialTheme.typography.labelLarge.copy(fontWeight = FontWeight.SemiBold),
                        color = MaterialTheme.colorScheme.onPrimaryContainer,
                        maxLines = 1
                    )
                }
                if (actions != null) {
                    Text(
                        "Acciones",
                        modifier = Modifier.width(90.dp),
                        style = MaterialTheme.typography.labelLarge.copy(fontWeight = FontWeight.SemiBold),
                        color = MaterialTheme.colorScheme.onPrimaryContainer,
                        maxLines = 1
                    )
                }
            }
            Divider(
                color = MaterialTheme.colorScheme.outline.copy(alpha = 0.10f),
                thickness = 1.dp
            )
            // Filas
            rows.forEachIndexed { idx, row ->
                Row(
                    Modifier
                        .fillMaxWidth()
                        .heightIn(min = 48.dp)
                        .background(if (idx % 2 == 0) MaterialTheme.colorScheme.background else MaterialTheme.colorScheme.surface),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    row.forEachIndexed { i, cell ->
                        Text(
                            cell,
                            modifier = Modifier
                                .width(widths[i])
                                .padding(end = 6.dp),
                            style = MaterialTheme.typography.bodyMedium,
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                    }
                    if (actions != null) {
                        actions(this, idx)
                    }
                }
                Divider(
                    color = MaterialTheme.colorScheme.outline.copy(alpha = 0.10f),
                    thickness = 1.dp
                )
            }
        }
    }
}
