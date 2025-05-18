// src/main/java/com/ds6p1/ds6p1/ui/theme/DataTable.kt
package com.ds6p1.ds6p1.ui.theme

import androidx.compose.foundation.ExperimentalFoundationApi
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.combinedClickable
import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.foundation.rememberScrollState
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.RectangleShape
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp

@OptIn(ExperimentalFoundationApi::class)
@Composable
fun DataTable(
    columns: List<String>,
    rows: List<List<String>>,
    actions: (@Composable RowScope.(rowIndex: Int) -> Unit)? = null,
    headerColor: Color = Color(0xFFEEEEEE),
    headerTextColor: Color = MaterialTheme.colorScheme.onSurface,
    rowColors: List<Color> = listOf(Color.Transparent, Color(0xFFF7F7F7)),
    onRowClick: (Int) -> Unit = {},
    onRowLongClick: (Int) -> Unit = {},
    modifier: Modifier = Modifier,
    minRowHeight: Dp = 48.dp,
    actionColumnWidth: Dp = 90.dp,
    showStripes: Boolean = true,
    borderColor: Color = MaterialTheme.colorScheme.outline.copy(alpha = 0.2f),
    cellPadding: PaddingValues = PaddingValues(10.dp),
    maxLines: Int = 2
) {
    // Estado compartido de scroll horizontal
    val scrollState = rememberScrollState()

    // Ancho fijo por columna
    val defaultColWidth = 120.dp
    val widths = List(columns.size) { defaultColWidth }
    val totalColsWidth = widths.fold(0.dp) { acc, w -> acc + w }
    val totalWidth = totalColsWidth + if (actions != null) actionColumnWidth else 0.dp

    Surface(
        modifier = modifier,
        shape = MaterialTheme.shapes.medium,
        color = MaterialTheme.colorScheme.surface,
        shadowElevation = 2.dp
    ) {
        // Necesitamos una columna que tome toda la altura para que la LazyColumn funcione con weight
        Column(modifier = Modifier.fillMaxSize()) {
            // Contenedor que permite el scroll horizontal de todo el contenido
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .horizontalScroll(scrollState)
            ) {
                // Aquí definimos el ancho total del “canvas”
                Column(
                    modifier = Modifier
                        .width(totalWidth)
                        .fillMaxHeight()
                ) {
                    // Header
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(minRowHeight)
                            .background(headerColor),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        columns.forEachIndexed { i, col ->
                            Box(
                                modifier = Modifier
                                    .width(widths[i])
                                    .fillMaxHeight()
                                    .padding(cellPadding),
                                contentAlignment = Alignment.Center
                            ) {
                                Text(
                                    text = col,
                                    fontWeight = FontWeight.Bold,
                                    color = headerTextColor,
                                    textAlign = TextAlign.Center,
                                    maxLines = 1,
                                    overflow = TextOverflow.Ellipsis
                                )
                            }
                        }
                        actions?.let {
                            Box(
                                modifier = Modifier
                                    .width(actionColumnWidth)
                                    .fillMaxHeight()
                                    .padding(cellPadding),
                                contentAlignment = Alignment.Center
                            ) {
                                Text(
                                    text = "Acciones",
                                    fontWeight = FontWeight.Bold,
                                    color = headerTextColor,
                                    textAlign = TextAlign.Center,
                                    maxLines = 1
                                )
                            }
                        }
                    }

                    // Body
                    LazyColumn(
                        modifier = Modifier
                            .fillMaxWidth()
                            .weight(1f)
                    ) {
                        itemsIndexed(rows) { idx, row ->
                            val bg = if (showStripes) rowColors[idx % rowColors.size] else rowColors.first()
                            Row(
                                modifier = Modifier
                                    .fillMaxWidth()
                                    .heightIn(min = minRowHeight)
                                    .background(bg)
                                    .border(0.5.dp, borderColor, RectangleShape)
                                    .combinedClickable(
                                        onClick = { onRowClick(idx) },
                                        onLongClick = { onRowLongClick(idx) }
                                    ),
                                verticalAlignment = Alignment.CenterVertically
                            ) {
                                row.forEachIndexed { i, cell ->
                                    Box(
                                        modifier = Modifier
                                            .width(widths[i])
                                            .fillMaxHeight()
                                            .padding(cellPadding),
                                        contentAlignment = Alignment.CenterStart
                                    ) {
                                        Text(
                                            text = cell,
                                            style = MaterialTheme.typography.bodyMedium,
                                            maxLines = maxLines,
                                            overflow = TextOverflow.Ellipsis
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
}

@Composable
fun DefaultTableActions(
    onView: (() -> Unit)? = null,
    onEdit: (() -> Unit)? = null,
    onDelete: (() -> Unit)? = null,
    modifier: Modifier = Modifier
) {
    Row(
        modifier = modifier.width(90.dp),
        horizontalArrangement = Arrangement.SpaceEvenly,
        verticalAlignment = Alignment.CenterVertically
    ) {
        onView?.let {
            IconButton(onClick = it, modifier = Modifier.size(24.dp)) {
                Icon(Icons.Default.Visibility, contentDescription = "View", tint = MaterialTheme.colorScheme.primary)
            }
        }
        onEdit?.let {
            IconButton(onClick = it, modifier = Modifier.size(24.dp)) {
                Icon(Icons.Default.Edit, contentDescription = "Edit", tint = MaterialTheme.colorScheme.primary)
            }
        }
        onDelete?.let {
            IconButton(onClick = it, modifier = Modifier.size(24.dp)) {
                Icon(Icons.Default.Delete, contentDescription = "Delete", tint = MaterialTheme.colorScheme.error)
            }
        }
    }
}
