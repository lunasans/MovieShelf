package at.neuhaus.movieshelf.ui.actors

import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import at.neuhaus.movieshelf.data.SessionManager
import at.neuhaus.movieshelf.data.api.RetrofitClient
import at.neuhaus.movieshelf.data.model.Actor
import at.neuhaus.movieshelf.data.model.Movie
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

class ActorDetailViewModel(private val actorId: Int) : ViewModel() {
    var actor by mutableStateOf<Actor?>(null)
    var isLoading by mutableStateOf(false)
    var error by mutableStateOf<String?>(null)

    init {
        loadActor()
    }

    fun loadActor() {
        viewModelScope.launch {
            isLoading = true
            error = null
            try {
                if (SessionManager.isDemo) {
                    delay(500)
                    actor = getDemoActors().find { it.id == actorId }
                    if (actor == null) error = "Schauspieler nicht gefunden"
                } else {
                    val response = RetrofitClient.api.getActor(actorId)
                    actor = response.data
                }
            } catch (e: Exception) {
                error = "Fehler beim Laden des Schauspielers: ${e.message}"
            } finally {
                isLoading = false
            }
        }
    }

    private fun getDemoActors(): List<Actor> {
        return listOf(
            Actor(
                id = 1,
                name = "Leonardo DiCaprio",
                biography = "Leonardo Wilhelm DiCaprio ist ein US-amerikanischer Schauspieler, Filmproduzent und Oscar-Preisträger.",
                birthDate = "11.11.1974",
                placeOfBirth = "Los Angeles, Kalifornien, USA",
                imageUrl = "res:actor_dicaprio",
                movies = listOf(
                    Movie(id = 1, title = "Inception", year = 2010, coverUrl = "res:inception_cover")
                )
            ),
            Actor(
                id = 2,
                name = "Christian Bale",
                biography = "Christian Charles Philip Bale ist ein britisch-amerikanischer Schauspieler.",
                birthDate = "30.01.1974",
                placeOfBirth = "Haverfordwest, Pembrokeshire, Wales",
                imageUrl = "res:actor_bale",
                movies = listOf(
                    Movie(id = 2, title = "The Dark Knight", year = 2008, coverUrl = "res:dark_knight_cover")
                )
            ),
            Actor(
                id = 3,
                name = "Matthew McConaughey",
                biography = "Matthew David McConaughey ist ein US-amerikanischer Schauspieler.",
                birthDate = "04.11.1969",
                placeOfBirth = "Uvalde, Texas, USA",
                imageUrl = "res:actor_mcconaughey",
                movies = listOf(
                    Movie(id = 3, title = "Interstellar", year = 2014, coverUrl = "res:interstellar_cover")
                )
            )
        )
    }

    class Factory(private val actorId: Int) : ViewModelProvider.Factory {
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            @Suppress("UNCHECKED_CAST")
            return ActorDetailViewModel(actorId) as T
        }
    }
}
