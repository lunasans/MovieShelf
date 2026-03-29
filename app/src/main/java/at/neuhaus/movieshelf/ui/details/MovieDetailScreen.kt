package at.neuhaus.movieshelf.ui.details

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalDensity
import androidx.compose.ui.text.AnnotatedString
import androidx.compose.ui.text.LinkAnnotation
import androidx.compose.ui.text.SpanStyle
import androidx.compose.ui.text.TextLinkStyles
import androidx.compose.ui.text.buildAnnotatedString
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.fromHtml
import androidx.compose.ui.text.style.TextDecoration
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import androidx.lifecycle.viewmodel.compose.viewModel
import at.neuhaus.movieshelf.data.api.RetrofitClient
import at.neuhaus.movieshelf.data.model.Actor
import at.neuhaus.movieshelf.data.model.Movie
import coil.compose.AsyncImage
import kotlinx.coroutines.launch

class MovieDetailViewModel(private val movieId: Int) : ViewModel() {
    var movie by mutableStateOf<Movie?>(null)
    var isLoading by mutableStateOf(false)
    var error by mutableStateOf<String?>(null)

    init {
        loadMovie()
    }

    fun loadMovie() {
        viewModelScope.launch {
            isLoading = true
            error = null
            try {
                val response = RetrofitClient.api.getMovie(movieId)
                movie = response.data
            } catch (e: Exception) {
                error = "Fehler beim Laden der Details: ${e.message}"
            } finally {
                isLoading = false
            }
        }
    }

    fun toggleWatched() {
        val currentMovie = movie ?: return
        viewModelScope.launch {
            try {
                RetrofitClient.api.toggleWatched(currentMovie.id)
                movie = currentMovie.copy(isWatched = !(currentMovie.isWatched ?: false))
            } catch (e: Exception) {
                error = "Fehler beim Aktualisieren: ${e.message}"
            }
        }
    }

    class Factory(private val movieId: Int) : ViewModelProvider.Factory {
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            @Suppress("UNCHECKED_CAST")
            return MovieDetailViewModel(movieId) as T
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MovieDetailScreen(
    movieId: Int,
    onBack: () -> Unit,
    onActorClick: (Int) -> Unit = {},
    onActorNameClick: (String) -> Unit = {},
    onMovieClick: (Movie) -> Unit
) {
    val viewModel: MovieDetailViewModel = viewModel(factory = MovieDetailViewModel.Factory(movieId))
    val movie = viewModel.movie
    val scrollState = rememberScrollState()
    val density = LocalDensity.current

    val headerHeightPx = with(density) { 350.dp.toPx() }
    val toolbarAlpha = (scrollState.value / (headerHeightPx * 0.8f)).coerceIn(0f, 1f)
    val titleStartScroll = with(density) { 250.dp.toPx() }
    val titleEndScroll = with(density) { 320.dp.toPx() }
    val titleAlpha = ((scrollState.value - titleStartScroll) / (titleEndScroll - titleStartScroll)).coerceIn(0f, 1f)
    val titleTranslationY = with(density) { (15.dp * (1f - titleAlpha)).toPx() }

    val appBarContainerColor by animateColorAsState(
        targetValue = if (toolbarAlpha > 0.9f) MaterialTheme.colorScheme.surface else Color.Transparent,
        animationSpec = tween(300)
    )

    val iconContentColor by animateColorAsState(
        targetValue = if (toolbarAlpha > 0.9f) MaterialTheme.colorScheme.onSurface else Color.White,
        animationSpec = tween(300)
    )

    Scaffold(
        topBar = {
            TopAppBar(
                title = { 
                    Text(
                        text = movie?.title ?: "",
                        maxLines = 1,
                        style = MaterialTheme.typography.titleLarge,
                        modifier = Modifier.graphicsLayer {
                            alpha = titleAlpha
                            translationY = titleTranslationY
                        }
                    )
                },
                navigationIcon = {
                    IconButton(
                        onClick = onBack,
                        colors = IconButtonDefaults.iconButtonColors(
                            containerColor = Color.Black.copy(alpha = 0.4f * (1f - toolbarAlpha)),
                            contentColor = iconContentColor
                        )
                    ) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Zurück")
                    }
                },
                actions = {
                    if (movie != null) {
                        IconButton(
                            onClick = { viewModel.toggleWatched() },
                            colors = IconButtonDefaults.iconButtonColors(
                                containerColor = Color.Black.copy(alpha = 0.4f * (1f - toolbarAlpha)),
                                contentColor = if (movie.isWatched == true) Color(0xFFFFC107) else iconContentColor
                            )
                        ) {
                            Icon(
                                imageVector = if (movie.isWatched == true) Icons.Filled.CheckCircle else Icons.Outlined.CheckCircle,
                                contentDescription = "Gesehen markieren"
                            )
                        }
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = appBarContainerColor)
            )
        }
    ) { padding ->
        if (viewModel.isLoading) {
            Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                CircularProgressIndicator()
            }
        } else if (movie != null) {
            Column(
                modifier = Modifier
                    .fillMaxSize()
                    .verticalScroll(scrollState)
            ) {
                // Backdrop
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(350.dp)
                        .graphicsLayer {
                            translationY = scrollState.value * 0.5f
                            alpha = 1f - (scrollState.value / headerHeightPx).coerceIn(0f, 0.7f)
                        }
                ) {
                    val backdropUrl = movie.backdropUrl ?: movie.coverUrl
                    if (backdropUrl != null) {
                        val fullUrl = if (backdropUrl.startsWith("http")) backdropUrl else "${RetrofitClient.baseUrl}${backdropUrl}"
                        AsyncImage(
                            model = fullUrl,
                            contentDescription = null,
                            modifier = Modifier.fillMaxSize(),
                            contentScale = ContentScale.Crop
                        )
                    }
                    Box(modifier = Modifier.fillMaxSize().background(Brush.verticalGradient(colors = listOf(Color.Black.copy(alpha = 0.4f), Color.Transparent, MaterialTheme.colorScheme.background))))
                }

                Column(modifier = Modifier.background(MaterialTheme.colorScheme.background).padding(horizontal = 16.dp)) {
                    Text(
                        text = movie.title ?: "",
                        style = MaterialTheme.typography.headlineMedium,
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier.graphicsLayer { alpha = (1f - (scrollState.value / titleStartScroll)).coerceIn(0f, 1f) }
                    )
                    
                    Spacer(Modifier.height(24.dp))
                    Text(text = "Handlung", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
                    Spacer(Modifier.height(8.dp))

                    val rawDescription = movie.overview ?: "Keine Beschreibung verfügbar."
                    val primaryColor = MaterialTheme.colorScheme.primary
                    
                    val annotatedString = buildAnnotatedString {
                        val pattern = Regex("\\{!Actor\\}(.*?)\\}")
                        var lastIndex = 0
                        
                        pattern.findAll(rawDescription).forEach { match ->
                            val beforeText = rawDescription.substring(lastIndex, match.range.first)
                            if (beforeText.isNotEmpty()) {
                                append(AnnotatedString.fromHtml(htmlString = beforeText))
                            }
                            
                            val actorName = match.groupValues[1]
                            val start = length
                            append(actorName)
                            val end = length
                            
                            addLink(
                                clickable = LinkAnnotation.Clickable(
                                    tag = "actor",
                                    styles = TextLinkStyles(
                                        style = SpanStyle(
                                            color = primaryColor,
                                            fontWeight = FontWeight.Bold,
                                            textDecoration = TextDecoration.Underline
                                        )
                                    ),
                                    linkInteractionListener = {
                                        val localActor = movie.actors?.find { it.name?.equals(actorName, ignoreCase = true) == true }
                                        if (localActor?.id != null) {
                                            onActorClick(localActor.id)
                                        } else {
                                            onActorNameClick(actorName)
                                        }
                                    }
                                ),
                                start = start,
                                end = end
                            )
                            
                            lastIndex = match.range.last + 1
                        }
                        
                        val afterText = rawDescription.substring(lastIndex)
                        if (afterText.isNotEmpty()) {
                            append(AnnotatedString.fromHtml(htmlString = afterText))
                        }
                    }

                    Text(
                        text = annotatedString,
                        style = MaterialTheme.typography.bodyLarge,
                        color = MaterialTheme.colorScheme.onBackground,
                        modifier = Modifier.fillMaxWidth()
                    )

                    // BOXSET SEKTION
                    if (movie.isBoxset == true && !movie.boxsetChildren.isNullOrEmpty()) {
                        Spacer(Modifier.height(32.dp))
                        Text(text = "Enthaltene Filme", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
                        Spacer(Modifier.height(16.dp))
                        
                        Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                            movie.boxsetChildren.forEach { childMovie ->
                                BoxsetMovieItem(movie = childMovie, onClick = { onMovieClick(childMovie) })
                            }
                        }
                    }

                    // BESETZUNG SEKTION
                    if (!movie.actors.isNullOrEmpty()) {
                        Spacer(Modifier.height(32.dp))
                        Text(text = "Besetzung", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
                        Spacer(Modifier.height(16.dp))
                        
                        Column(
                            verticalArrangement = Arrangement.spacedBy(12.dp),
                            modifier = Modifier.padding(bottom = 32.dp)
                        ) {
                            movie.actors.forEach { actor ->
                                ActorRowItem(actor = actor, onClick = { actor.id?.let { onActorClick(it) } })
                            }
                        }
                    }

                    Spacer(Modifier.height(100.dp))
                }
            }
        }
    }
}

@Composable
fun BoxsetMovieItem(movie: Movie, onClick: () -> Unit) {
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

@Composable
fun ActorRowItem(actor: Actor, onClick: () -> Unit) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clickable(onClick = onClick)
            .padding(vertical = 4.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Surface(
            modifier = Modifier.size(56.dp),
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
            } else {
                Box(contentAlignment = Alignment.Center) {
                    Text(text = actor.name?.take(1) ?: "?", style = MaterialTheme.typography.titleLarge)
                }
            }
        }
        
        Spacer(Modifier.width(16.dp))
        
        Column {
            Text(
                text = actor.name ?: "Unbekannt",
                style = MaterialTheme.typography.titleMedium,
                fontWeight = FontWeight.Bold
            )
            if (!actor.role.isNullOrBlank()) {
                Text(
                    text = actor.role,
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.outline,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis
                )
            }
        }
    }
}
