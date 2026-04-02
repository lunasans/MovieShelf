package at.neuhaus.movieshelf.ui.stats

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import at.neuhaus.movieshelf.data.api.RetrofitClient
import at.neuhaus.movieshelf.data.model.Stats
import kotlinx.coroutines.launch

class StatsViewModel : ViewModel() {
    var stats by mutableStateOf<Stats?>(null)
    var isLoading by mutableStateOf(false)
    var error by mutableStateOf<String?>(null)

    init {
        loadStats()
    }

    fun loadStats() {
        viewModelScope.launch {
            isLoading = true
            error = null
            try {
                // Wir erhalten jetzt direkt das Stats-Objekt vom API-Call
                stats = RetrofitClient.api.getStats()
            } catch (e: Exception) {
                error = "Fehler beim Laden der Statistik: ${e.message}"
            } finally {
                isLoading = false
            }
        }
    }
}
