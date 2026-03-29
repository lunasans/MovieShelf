package at.neuhaus.movieshelf

import android.os.Bundle
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import at.neuhaus.movieshelf.data.SessionManager
import at.neuhaus.movieshelf.data.api.RetrofitClient
import at.neuhaus.movieshelf.data.local.DataStoreManager
import at.neuhaus.movieshelf.data.model.Movie
import at.neuhaus.movieshelf.ui.about.AboutScreen
import at.neuhaus.movieshelf.ui.actors.ActorDetailScreen
import at.neuhaus.movieshelf.ui.add.AddMovieScreen
import at.neuhaus.movieshelf.ui.dashboard.DashboardScreen
import at.neuhaus.movieshelf.ui.details.MovieDetailScreen
import at.neuhaus.movieshelf.ui.login.LoginScreen
import at.neuhaus.movieshelf.ui.profile.ProfileScreen
import at.neuhaus.movieshelf.ui.setup.SetupScreen
import at.neuhaus.movieshelf.ui.theme.MovieShelfTheme
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            MovieShelfTheme {
                MovieShelfApp()
            }
        }
    }
}

@Composable
fun MovieShelfApp() {
    val context = LocalContext.current
    val dataStoreManager = remember { DataStoreManager(context) }
    val serverUrl by dataStoreManager.serverUrl.collectAsState(initial = null)
    val navController = rememberNavController()
    val scope = rememberCoroutineScope()
    
    var isInitialized by remember { mutableStateOf(false) }
    var initializationError by remember { mutableStateOf(false) }
    var startDestination by remember { mutableStateOf("login") }
    var isLoadingAuth by remember { mutableStateOf(true) }

    LaunchedEffect(serverUrl) {
        if (serverUrl.isNullOrBlank()) {
            isInitialized = false
            isLoadingAuth = false
            return@LaunchedEffect
        }
        
        // Context wird hier übergeben, um den Cache zu aktivieren
        val success = RetrofitClient.initialize(serverUrl!!, context)
        if (success) {
            val savedToken = dataStoreManager.authToken.first()
            if (!savedToken.isNullOrBlank()) {
                SessionManager.token = savedToken
                startDestination = "dashboard"
            } else {
                startDestination = "login"
            }
            isInitialized = true
            initializationError = false
        } else {
            isInitialized = false
            initializationError = true
        }
        isLoadingAuth = false
    }

    if (serverUrl.isNullOrBlank() || initializationError) {
        SetupScreen(
            dataStoreManager = dataStoreManager,
            onSetupComplete = { initializationError = false }
        )
    } else if (!isInitialized || isLoadingAuth) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            CircularProgressIndicator()
        }
    } else {
        NavHost(navController = navController, startDestination = startDestination) {
            composable("login") {
                LoginScreen(
                    onLoginSuccess = {
                        navController.navigate("dashboard") {
                            popUpTo("login") { inclusive = true }
                        }
                    },
                    onResetUrl = {
                        scope.launch {
                            dataStoreManager.saveServerUrl("")
                            dataStoreManager.saveAuthToken(null)
                        }
                    }
                )
            }
            composable("dashboard") {
                DashboardScreen(
                    onMovieClick = { movie: Movie ->
                        navController.navigate("movie_details/${movie.id}")
                    },
                    onLogout = {
                        scope.launch {
                            dataStoreManager.saveAuthToken(null)
                            SessionManager.token = null
                            navController.navigate("login") {
                                popUpTo("dashboard") { inclusive = true }
                            }
                        }
                    },
                    onAddMovie = { navController.navigate("add_movie") },
                    onAboutClick = { navController.navigate("about") },
                    onProfileClick = { navController.navigate("profile") }
                )
            }
            composable("profile") {
                ProfileScreen(onBack = { navController.popBackStack() })
            }
            composable("movie_details/{movieId}") { backStackEntry ->
                val movieId = backStackEntry.arguments?.getString("movieId")?.toIntOrNull()
                if (movieId != null) {
                    MovieDetailScreen(
                        movieId = movieId,
                        onBack = { navController.popBackStack() },
                        onMovieClick = { movie: Movie ->
                            navController.navigate("movie_details/${movie.id}")
                        },
                        onActorClick = { actorId ->
                            navController.navigate("actor_details/$actorId")
                        },
                        onActorNameClick = { actorName ->
                            scope.launch {
                                try {
                                    val response = RetrofitClient.api.searchActors(actorName)
                                    val foundActor = response.data?.firstOrNull()
                                    if (foundActor?.id != null) {
                                        navController.navigate("actor_details/${foundActor.id}")
                                    } else {
                                        Toast.makeText(context, "Schauspieler \"$actorName\" nicht gefunden", Toast.LENGTH_SHORT).show()
                                    }
                                } catch (e: Exception) {
                                    Toast.makeText(context, "Fehler bei der Suche", Toast.LENGTH_SHORT).show()
                                }
                            }
                        }
                    )
                }
            }
            composable("actor_details/{actorId}") { backStackEntry ->
                val actorId = backStackEntry.arguments?.getString("actorId")?.toIntOrNull()
                if (actorId != null) {
                    ActorDetailScreen(
                        actorId = actorId,
                        onBack = { navController.popBackStack() },
                        onMovieClick = { movie: Movie ->
                            navController.navigate("movie_details/${movie.id}")
                        }
                    )
                }
            }
            composable("add_movie") {
                AddMovieScreen(
                    onBack = { navController.popBackStack() },
                    onMovieImported = { navController.popBackStack() }
                )
            }
            composable("about") {
                AboutScreen(onBack = { navController.popBackStack() })
            }
        }
    }
}
