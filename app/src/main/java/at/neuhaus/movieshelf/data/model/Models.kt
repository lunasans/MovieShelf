package at.neuhaus.movieshelf.data.model

import com.google.gson.annotations.SerializedName

data class MovieResponse(
    val data: List<Movie>?
)

data class SingleMovieResponse(
    val data: Movie?
)

data class ActorResponse(
    val data: List<Actor>?
)

data class SingleActorResponse(
    val data: Actor?
)

data class StatsResponse(
    val data: Stats?
)

data class Movie(
    val id: Int,
    val title: String?,
    val year: Int?,
    val rating: String?,
    val genre: String?,
    val overview: String?,
    val runtime: Int?,
    val director: String?,
    @SerializedName("cover_url") val coverUrl: String?,
    @SerializedName("backdrop_url") val backdropUrl: String?,
    @SerializedName("trailer_url") val trailerUrl: String?,
    @SerializedName("view_count") val viewCount: Int?,
    @SerializedName("is_watched") val isWatched: Boolean?,
    @SerializedName("actors", alternate = ["cast", "credits"]) val actors: List<Actor>?,
    @SerializedName("tmdb_id") val tmdbId: String?,
    @SerializedName("rating_age") val ratingAge: Int? = null,
    // Felder für Boxsets
    @SerializedName("is_boxset") val isBoxset: Boolean? = false,
    @SerializedName("boxset_parent_id") val boxsetParentId: Int? = null,
    @SerializedName("boxset_children", alternate = ["movies"]) val boxsetChildren: List<Movie>? = null
)

data class Actor(
    val id: Int?,
    @SerializedName("name", alternate = ["full_name", "actor_name", "display_name"]) val name: String?,
    @SerializedName("image_url", alternate = ["profile_path", "profile_url", "photo_url"]) val imageUrl: String?,
    @SerializedName("role", alternate = ["character"]) val role: String?,
    @SerializedName("is_main_role") val isMainRole: Boolean?,
    @SerializedName("movies") val movies: List<Movie>? = null,
    @SerializedName("bio", alternate = ["biography"]) val biography: String? = null,
    @SerializedName("birth_date", alternate = ["birthday"]) val birthDate: String? = null,
    @SerializedName("place_of_birth") val placeOfBirth: String? = null
)

data class Stats(
    @SerializedName("total_films") val totalFilms: Int,
    @SerializedName("total_runtime_minutes") val totalRuntimeMinutes: Long,
    @SerializedName("total_runtime_hours") val totalRuntimeHours: Double,
    @SerializedName("total_runtime_days") val totalRuntimeDays: Double,
    @SerializedName("avg_runtime") val avgRuntime: Double,
    val watched: WatchedStats?,
    val years: YearStats?,
    val collections: List<CollectionStats>?,
    val ratings: List<RatingStats>?,
    val genres: List<GenreStats>?,
    @SerializedName("year_distribution") val yearDistribution: Map<String, Int>?,
    val decades: List<DecadeStats>?
)

data class WatchedStats(
    val count: Int,
    val percentage: Double
)

data class YearStats(
    @SerializedName("avg_year") val avgYear: Double,
    @SerializedName("oldest_year") val oldestYear: Int,
    @SerializedName("newest_year") val newestYear: Int
)

data class CollectionStats(
    @SerializedName("collection_type") val collectionType: String,
    val count: Int,
    val percentage: Double
)

data class RatingStats(
    @SerializedName("rating_age") val ratingAge: Int,
    val count: Int
)

data class GenreStats(
    val genre: String,
    val count: Int
)

data class DecadeStats(
    val decade: Int,
    val count: Int,
    @SerializedName("avg_runtime") val avgRuntime: Double
)

data class TmdbImportRequest(
    @SerializedName("tmdb_id") val tmdbId: Int,
    val type: String = "movie"
)

data class LoginResponse(
    val token: String?,
    val user: User?,
    @SerializedName("requires_2fa") val requires2fa: Boolean? = false,
    @SerializedName("user_id") val userId: Int? = null,
    @SerializedName("device_name") val deviceName: String? = null
)

data class User(
    val id: Int,
    val name: String?,
    val email: String?,
    @SerializedName("two_factor_enabled") val twoFactorEnabled: Boolean? = false,
    @SerializedName("two_factor_confirmed_at") val twoFactorConfirmedAt: String? = null
)

data class UserUpdateResponse(
    val user: User?
)

data class ServerInfo(
    @SerializedName("app_name") val appName: String?,
    val version: String?
)
