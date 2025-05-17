package com.ds6p1.ds6p1.ui.theme

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.Dp

@Composable
fun DataTable(
    columns: List<String>,
    rows: List<List<String>>,
    actions: (@Composable RowScope.(rowIndex: Int) -> Unit)? = null,
    columnWidths: List<Dp>? = null,
    modifier: Modifier = Modifier
) {
    val horizontalScroll = rememberScrollState()
    val verticalScroll = rememberScrollState()
    val defaultWidth = 150.dp
    val widths = columnWidths ?: List(columns.size) { defaultWidth }
    val totalWidth = widths.fold(0.dp) { acc, w -> acc + w } + if (actions != null) 140.dp else 0.dp

    Box(
        modifier = modifier
            .fillMaxSize()
            .verticalScroll(verticalScroll)
    ) {
        Box(
            Modifier
                .horizontalScroll(horizontalScroll)
                .widthIn(min = totalWidth)
        ) {
            Column(
                Modifier
                    .shadow(elevation = 1.dp, shape = RoundedCornerShape(12.dp))
                    .background(MaterialTheme.colorScheme.surface)
            ) {
                // Header fijo horizontalmente
                Row(
                    Modifier
                        .background(MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.7f))
                        .padding(vertical = 14.dp, horizontal = 16.dp)
                ) {
                    columns.forEachIndexed { i, col ->
                        Text(
                            text = col,
                            modifier = Modifier
                                .width(widths[i])
                                .padding(horizontal = 4.dp),
                            style = MaterialTheme.typography.titleSmall.copy(
                                fontWeight = FontWeight.SemiBold
                            )
                        )
                    }
                    actions?.let {
                        Box(Modifier.width(140.dp)) {
                            Text("Acciones")
                        }
                    }
                }

                // Filas con scroll vertical independiente
                Column {
                    rows.forEachIndexed { idx, row ->
                        Row(
                            Modifier
                                .fillMaxWidth()
                                .heightIn(min = 54.dp)
                                .background(if (idx % 2 == 0) MaterialTheme.colorScheme.surface
                                else MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.2f))
                        ) {
                            row.forEachIndexed { i, cell ->
                                Box(
                                    Modifier
                                        .width(widths[i])
                                        .padding(16.dp)
                                ) {
                                    Text(
                                        text = cell,
                                        maxLines = 2,
                                        overflow = TextOverflow.Visible
                                    )
                                }
                            }
                            actions?.invoke(this, idx)
                        }
                    }
                }
            }
        }
    }
}