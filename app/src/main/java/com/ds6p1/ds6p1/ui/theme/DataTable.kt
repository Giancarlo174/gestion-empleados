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
import androidx.compose.ui.unit.sp

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
    val defaultWidth = 120.dp
    val widths = columnWidths ?: List(columns.size) { defaultWidth }
    val totalWidth = widths.fold(0.dp) { acc, w -> acc + w } + if (actions != null) 100.dp else 0.dp

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
                    .shadow(elevation = 1.dp, shape = RoundedCornerShape(10.dp))
                    .background(MaterialTheme.colorScheme.surface)
            ) {
                // Header
                Row(
                    Modifier
                        .background(MaterialTheme.colorScheme.secondary.copy(alpha = 0.18f))
                        .padding(vertical = 10.dp, horizontal = 10.dp)
                ) {
                    columns.forEachIndexed { i, col ->
                        Text(
                            text = col,
                            modifier = Modifier
                                .width(widths[i])
                                .padding(horizontal = 2.dp),
                            style = MaterialTheme.typography.bodySmall.copy(
                                fontWeight = FontWeight.Bold,
                                color = MaterialTheme.colorScheme.primary
                            )
                        )
                    }
                    actions?.let {
                        Box(Modifier.width(100.dp)) {
                            Text("Acciones", style = MaterialTheme.typography.bodySmall)
                        }
                    }
                }

                // Filas
                Column {
                    rows.forEachIndexed { idx, row ->
                        Row(
                            Modifier
                                .fillMaxWidth()
                                .heightIn(min = 40.dp)
                                .background(if (idx % 2 == 0) MaterialTheme.colorScheme.surface else MaterialTheme.colorScheme.secondary.copy(alpha = 0.08f))
                                .border(
                                    width = 0.5.dp,
                                    color = MaterialTheme.colorScheme.outline.copy(alpha = 0.2f)
                                )
                        ) {
                            row.forEachIndexed { i, cell ->
                                Box(
                                    Modifier
                                        .width(widths[i])
                                        .padding(10.dp)
                                ) {
                                    Text(
                                        text = cell,
                                        maxLines = 2,
                                        overflow = TextOverflow.Ellipsis,
                                        style = MaterialTheme.typography.bodySmall,
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