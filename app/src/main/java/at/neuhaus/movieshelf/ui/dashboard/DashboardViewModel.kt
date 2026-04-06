package at.neuhaus.movieshelf.ui.dashboard

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import at.neuhaus.movieshelf.data.SessionManager
import at.neuhaus.movieshelf.data.api.RetrofitClient
import at.neuhaus.movieshelf.data.model.Movie
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

class DashboardViewModel : ViewModel() {
    var movies by mutableStateOf<List<Movie>>(emptyList())
    var isLoading by mutableStateOf(false)
    var isRefreshing by mutableStateOf(false)
    var error by mutableStateOf<String?>(null)
    
    var searchQuery by mutableStateOf("")
    var selectedTab by mutableIntStateOf(0) // 0 = Neu, 1 = Alle
    private var searchJob: Job? = null

    init {
        loadMovies()
    }

    fun loadMovies(refresh: Boolean = false) {
        viewModelScope.launch {
            if (refresh) isRefreshing = true else isLoading = true
            error = null
            try {
                if (SessionManager.isDemo) {
                    delay(500)
                    movies = getDemoMovies()
                } else {
                    val response = RetrofitClient.api.getMovies(
                        page = 1,
                        perPage = if (selectedTab == 1) 1000 else 20
                    )
                    
                    val resultList = response.data ?: emptyList()
                    val filteredList = resultList.filter { it.boxsetParentId == null }
                    
                    movies = if (selectedTab == 0) {
                        filteredList.sortedByDescending { it.id }
                    } else {
                        filteredList.sortedBy { it.title?.lowercase() ?: "" }
                    }
                }
            } catch (e: Exception) {
                error = "Fehler beim Laden der Filme: ${e.message}"
            } finally {
                isLoading = false
                isRefreshing = false
            }
        }
    }

    private fun getDemoMovies(): List<Movie> {
        return listOf(
            Movie(
                id = 1,
                title = "Inception",
                year = 2010,
                rating = "8.8",
                genre = "Sci-Fi",
                overview = "Ein Dieb, der Geheimnisse aus dem Unterbewusstsein stiehlt.",
                coverUrl = "res:inception_cover",
                backdropUrl = "res:inception_backdrop",
                runtime = 148,
                director = "Christopher Nolan",
                actors = emptyList(),
                viewCount = 5,
                isWatched = true,
                tmdbId = "27205",
                trailerUrl = "https://www.youtube.com/watch?v=YoHD9XEInc0"
            ),
            Movie(
                id = 2,
                title = "The Dark Knight",
                year = 2008,
                rating = "9.0",
                genre = "Action",
                overview = "Batman kämpft gegen den Joker in Gotham City.",
                coverUrl = "res:dark_knight_cover",
                backdropUrl = "res:dark_knight_backdrop",
                runtime = 152,
                director = "Christopher Nolan",
                actors = emptyList(),
                viewCount = 10,
                isWatched = true,
                tmdbId = "155",
                trailerUrl = "https://www.youtube.com/watch?v=EXeTwQWaywY"
            ),
        )
    }

    fun onTabSelected(index: Int) {
        selectedTab = index
        loadMovies()
    }

    fun onSearchQueryChange(newQuery: String) {
        searchQuery = newQuery
        searchJob?.cancel()
        searchJob = viewModelScope.launch {
            delay(500)
            if (newQuery.isBlank()) {
                loadMovies()
            } else {
                performSearch(newQuery)
            }
        }
    }

    private suspend fun performSearch(query: String) {
        isLoading = true
        error = null
        try {
            if (SessionManager.isDemo) {
                delay(300)
                movies = getDemoMovies().filter { it.title?.contains(query, ignoreCase = true) == true }
            } else {
                val response = RetrofitClient.api.searchMovies(query)
                val resultList = response.data ?: emptyList()
                movies = resultList.sortedBy { it.title?.lowercase() ?: "" }
            }
        } catch (e: Exception) {
            error = "Suche fehlgeschlagen: ${e.message}"
        } finally {
            isLoading = false
        }
    }
}
