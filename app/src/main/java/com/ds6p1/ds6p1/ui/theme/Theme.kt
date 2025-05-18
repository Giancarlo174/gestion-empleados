package com.ds6p1.ds6p1.ui.theme

import android.app.Activity
import android.os.Build
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.dynamicDarkColorScheme
import androidx.compose.material3.dynamicLightColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.ui.graphics.Color

private val DarkColorScheme = darkColorScheme(
    primary = Color(0xFF3A5BA0),
    onPrimary = Color.White,
    secondary = Color(0xFFB0BEC5),
    onSecondary = Color(0xFF232931),
    tertiary = Color(0xFF00B894),
    onTertiary = Color.White,
    error = Color(0xFFFF7675),
    onError = Color.White,
    background = Color(0xFF232931),
    surface = Color(0xFF2D3436),
    outline = Color(0xFFB2BEC3)
)

private val LightColorScheme = lightColorScheme(
    primary = Color(0xFF3A5BA0),
    onPrimary = Color.White,
    secondary = Color(0xFFB0BEC5),
    onSecondary = Color(0xFF232931),
    tertiary = Color(0xFF00B894),
    onTertiary = Color.White,
    error = Color(0xFFFF7675),
    onError = Color.White,
    background = Color(0xFFF7F9FB),
    surface = Color.White,
    outline = Color(0xFFB2BEC3)
)

@Composable
fun Ds6p1Theme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    // Dynamic color is available on Android 12+
    dynamicColor: Boolean = false, // Fijamos a false para mantener la paleta minimalista
    content: @Composable () -> Unit
) {
    val colorScheme = if (darkTheme) DarkColorScheme else LightColorScheme

    MaterialTheme(
        colorScheme = colorScheme,
        typography = Typography,
        content = content
    )
}

@Composable
fun containerElevation(elevated: Boolean): Dp {
    return if (elevated) 2.dp else 0.dp
}

@Composable
fun containerShape(rounded: Boolean): RoundedCornerShape {
    return if (rounded) {
        RoundedCornerShape(16.dp)
    } else {
        RoundedCornerShape(0.dp)
    }
}