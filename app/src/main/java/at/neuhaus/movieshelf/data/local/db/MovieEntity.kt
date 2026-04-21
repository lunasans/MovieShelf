package at.neuhaus.movieshelf.data.local.db

import androidx.room.Entity
import androidx.room.PrimaryKey
import at.neuhaus.movieshelf.data.model.Actor
import at.neuhaus.movieshelf.data.model.Movie
import com.google.gson.Gson
import com.google.gson.reflect.TypeToken

@Entity(tableName = "movies")
data class MovieEntity(
    @PrimaryKey val id: Int,
    val title: String?,
    val year: Int?,
    val rating: String?,
    val genre: String?,
    val overview: String?,
    val runtime: Int?,
    val director: String?,
    val coverUrl: String?,
    val backdropUrl: String?,
    val trailerUrl: String?,
    val viewCount: Int?,
    val isWatched: Boolean?,
    val tmdbId: String?,
    val ratingAge: Int?,
    val tag: String?,
    val isBoxset: Boolean?,
    val boxsetParentId: Int?,
    val inCollection: Boolean?,
    val actorsJson: String?,
    val boxsetChildrenJson: String?,
    val cachedAt: Long = System.currentTimeMillis()
) {
    fun toMovie(): Movie {
        val gson = Gson()
        val actorType = object : TypeToken<List<Actor>>() {}.type
        val movieType = object : TypeToken<List<Movie>>() {}.type
        return Movie(
            id = id,
            title = title,
            year = year,
            rating = rating,
            genre = genre,
            overview = overview,
            runtime = runtime,
            director = director,
            coverUrl = coverUrl,
            backdropUrl = backdropUrl,
            trailerUrl = trailerUrl,
            viewCount = viewCount,
            isWatched = isWatched,
            tmdbId = tmdbId,
            ratingAge = ratingAge,
            tag = tag,
            isBoxset = isBoxset,
            boxsetParentId = boxsetParentId,
            inCollection = inCollection,
            actors = if (actorsJson != null) gson.fromJson(actorsJson, actorType) else null,
            boxsetChildren = if (boxsetChildrenJson != null) gson.fromJson(boxsetChildrenJson, movieType) else null
        )
    }

    companion object {
        fun fromMovie(movie: Movie): MovieEntity {
            val gson = Gson()
            return MovieEntity(
                id = movie.id,
                title = movie.title,
                year = movie.year,
                rating = movie.rating,
                genre = movie.genre,
                overview = movie.overview,
                runtime = movie.runtime,
                director = movie.director,
                coverUrl = movie.coverUrl,
                backdropUrl = movie.backdropUrl,
                trailerUrl = movie.trailerUrl,
                viewCount = movie.viewCount,
                isWatched = movie.isWatched,
                tmdbId = movie.tmdbId,
                ratingAge = movie.ratingAge,
                tag = movie.tag,
                isBoxset = movie.isBoxset,
                boxsetParentId = movie.boxsetParentId,
                inCollection = movie.inCollection,
                actorsJson = if (!movie.actors.isNullOrEmpty()) gson.toJson(movie.actors) else null,
                boxsetChildrenJson = if (!movie.boxsetChildren.isNullOrEmpty()) gson.toJson(movie.boxsetChildren) else null
            )
        }
    }
}
