package at.neuhaus.movieshelf.ui.actors

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import at.neuhaus.movieshelf.data.api.RetrofitClient
import at.neuhaus.movieshelf.data.model.Movie
import at.neuhaus.movieshelf.ui.dashboard.MovieItem
import coil.compose.AsyncImage

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ActorDetailScreen(
    actorId: Int,
    onBack: () -> Unit,
    onMovieClick: (Movie) -> Unit
) {
    val viewModel: ActorDetailViewModel = viewModel(factory = ActorDetailViewModel.Factory(actorId))
    val actor = viewModel.actor

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text(actor?.name ?: "Schauspieler") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Zurück")
                    }
                }
            )
        }
    ) { padding ->
        if (viewModel.isLoading) {
            Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                CircularProgressIndicator()
            }
        } else if (actor != null) {
            Column(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(padding)
                    .verticalScroll(rememberScrollState())
                    .padding(16.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                // Profilbild
                Surface(
                    modifier = Modifier.size(150.dp),
                    shape = CircleShape,
                    color = MaterialTheme.colorScheme.surfaceVariant
                ) {
                    if (actor.imageUrl != null) {
                        val fullUrl = if (actor.imageUrl.startsWith("http")) actor.imageUrl else "${RetrofitClient.baseUrl}${actor.imageUrl}"
                        AsyncImage(
                            model = fullUrl,
                            contentDescription = actor.name,
                            modifier = Modifier.fillMaxSize(),
                            contentScale = ContentScale.Crop
                        )
                    }
                }

                Spacer(Modifier.height(16.dp))

                Text(
                    text = actor.name ?: "Unbekannt",
                    style = MaterialTheme.typography.headlineMedium,
                    fontWeight = FontWeight.Bold
                )

                if (!actor.birthDate.isNullOrBlank()) {
                    Text(
                        text = "Geboren: ${actor.birthDate}${if (actor.placeOfBirth != null) " in ${actor.placeOfBirth}" else ""}",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.outline
                    )
                }

                Spacer(Modifier.height(24.dp))

                if (!actor.biography.isNullOrBlank()) {
                    Text(
                        text = "Biografie",
                        style = MaterialTheme.typography.titleMedium,
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier.fillMaxWidth()
                    )
                    Spacer(Modifier.height(8.dp))
                    Text(
                        text = actor.biography,
                        style = MaterialTheme.typography.bodyMedium,
                        textAlign = TextAlign.Justify
                    )
                    Spacer(Modifier.height(24.dp))
                }

                if (!actor.movies.isNullOrEmpty()) {
                    Text(
                        text = "Bekannt für",
                        style = MaterialTheme.typography.titleMedium,
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier.fillMaxWidth()
                    )
                    Spacer(Modifier.height(16.dp))
                    
                    // Wir nutzen hier eine Column statt Grid, da wir uns in einem vertikalen Scrollview befinden
                    actor.movies.forEach { movie ->
                        MovieRowItem(movie = movie, onClick = { onMovieClick(movie) })
                        Spacer(Modifier.height(8.dp))
                    }
                }
            }
        }
    }
}

@Composable
fun MovieRowItem(movie: Movie, onClick: () -> Unit) {
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .height(100.dp)
            .clickable(onClick = onClick),
        shape = MaterialTheme.shapes.medium
    ) {
        Row(modifier = Modifier.fillMaxSize()) {
            if (movie.coverUrl != null) {
                val imageUrl = if (movie.coverUrl.startsWith("http")) movie.coverUrl else "${RetrofitClient.baseUrl}${movie.coverUrl}"
                AsyncImage(
                    model = imageUrl,
                    contentDescription = movie.title,
                    modifier = Modifier.width(70.dp).fillMaxHeight(),
                    contentScale = ContentScale.Crop
                )
            }
            Column(
                modifier = Modifier
                    .padding(12.dp)
                    .fillMaxSize(),
                verticalArrangement = Arrangement.Center
            ) {
                Text(
                    text = movie.title ?: "Unbekannt",
                    style = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.Bold,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis
                )
                Text(
                    text = movie.year?.toString() ?: "",
                    style = MaterialTheme.typography.bodySmall
                )
            }
        }
    }
}
