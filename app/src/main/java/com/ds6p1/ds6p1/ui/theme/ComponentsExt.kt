package com.ds6p1.ds6p1.ui.theme

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp

/**
 * Botón minimalista estilo Apple para acciones principales
 */
@Composable
fun MinimalButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    icon: ImageVector? = null,
    enabled: Boolean = true
) {
    Button(
        onClick = onClick,
        modifier = modifier.height(36.dp),
        enabled = enabled,
        shape = RoundedCornerShape(8.dp),
        contentPadding = PaddingValues(horizontal = 16.dp, vertical = 0.dp),
        colors = ButtonDefaults.buttonColors(
            containerColor = MaterialTheme.colorScheme.primary.copy(alpha = 0.9f)
        )
    ) {
        if (icon != null) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                modifier = Modifier.size(16.dp)
            )
            Spacer(modifier = Modifier.width(8.dp))
        }
        Text(
            text = text,
            style = MaterialTheme.typography.bodyMedium.copy(
                fontWeight = FontWeight.Medium
            )
        )
    }
}

/**
 * Botón de acción con icono "+" y texto corto
 */
@Composable
fun AddButton(
    text: String = "Nuevo",
    onClick: () -> Unit,
    modifier: Modifier = Modifier
) {
    MinimalButton(
        text = text,
        onClick = onClick,
        modifier = modifier,
        icon = Icons.Default.Add
    )
}

/**
 * Botón de texto minimalista para diálogos
 */
@Composable
fun MinimalTextButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier
) {
    TextButton(
        onClick = onClick,
        contentPadding = PaddingValues(horizontal = 16.dp, vertical = 6.dp),
        modifier = modifier.height(32.dp)
    ) {
        Text(
            text = text,
            style = MaterialTheme.typography.labelLarge,
            color = MaterialTheme.colorScheme.primary
        )
    }
}
