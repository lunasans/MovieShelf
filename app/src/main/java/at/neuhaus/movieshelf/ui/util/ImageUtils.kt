package at.neuhaus.movieshelf.ui.util

import android.content.Context
import android.net.Uri
import at.neuhaus.movieshelf.data.api.RetrofitClient

/**
 * Löst eine Bild-URL in ein Coil-kompatibles Modell auf.
 * Unterstützt:
 *   - "res:<name>" → android.resource:// URI (kompatibel mit allen API-Levels)
 *   - "http(s)://..." → direkte URL
 *   - relativer Pfad → wird an baseUrl angehängt
 */
fun resolveImageUrl(context: Context, url: String): Any? {
    val trimmed = url.trim()
    return when {
        trimmed.startsWith("res:") -> {
            val resName = trimmed.substringAfter("res:")
            val resId = context.resources.getIdentifier(resName, "drawable", context.packageName)
            if (resId != 0) Uri.parse("android.resource://${context.packageName}/$resId") else null
        }
        trimmed.startsWith("http") -> trimmed
        else -> RetrofitClient.baseUrl.removeSuffix("/") + "/" + trimmed.removePrefix("/")
    }
}
