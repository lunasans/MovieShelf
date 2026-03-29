package at.neuhaus.movieshelf.ui.actors

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import at.neuhaus.movieshelf.data.api.RetrofitClient
import at.neuhaus.movieshelf.data.model.Actor
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

class ActorListViewModel : ViewModel() {
    var actors by mutableStateOf<List<Actor>>(emptyList())
    var isLoading by mutableStateOf(false)
    var isRefreshing by mutableStateOf(false)
    var error by mutableStateOf<String?>(null)

    var searchQuery by mutableStateOf("")
    private var searchJob: Job? = null

    init {
        loadActors()
    }

    fun loadActors(refresh: Boolean = false) {
        viewModelScope.launch {
            if (refresh) isRefreshing = true else isLoading = true
            error = null
            try {
                val response = RetrofitClient.api.getActors(page = 1, perPage = 100)
                actors = response.data ?: emptyList()
            } catch (e: Exception) {
                error = "Fehler beim Laden der Schauspieler: ${e.message}"
            } finally {
                isLoading = false
                isRefreshing = false
            }
        }
    }

    fun onSearchQueryChange(newQuery: String) {
        searchQuery = newQuery
        searchJob?.cancel()
        searchJob = viewModelScope.launch {
            delay(500)
            if (newQuery.isBlank()) {
                loadActors()
            } else {
                performSearch(newQuery)
            }
        }
    }

    private suspend fun performSearch(query: String) {
        isLoading = true
        error = null
        try {
            val response = RetrofitClient.api.searchActors(query)
            actors = response.data ?: emptyList()
        } catch (e: Exception) {
            error = "Suche fehlgeschlagen: ${e.message}"
        } finally {
            isLoading = false
        }
    }
}
