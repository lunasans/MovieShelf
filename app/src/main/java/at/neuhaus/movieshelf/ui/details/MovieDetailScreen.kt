package at.neuhaus.movieshelf.ui.details

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material.icons.outlined.*
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalDensity
import androidx.compose.ui.platform.LocalUriHandler
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
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import at.neuhaus.movieshelf.data.model.Actor
import at.neuhaus.movieshelf.data.model.Movie
import at.neuhaus.movieshelf.ui.util.resolveImageUrl
import coil.compose.AsyncImage

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
    val uriHandler = LocalUriHandler.current
    val context = LocalContext.current

    val headerHeightPx = with(density) { 350.dp.toPx() }
    val toolbarAlpha = (scrollState.value / (headerHeightPx * 0.8f)).coerceIn(0f, 1f)
    val titleStartScroll = with(density) { 250.dp.toPx() }
    val titleEndScroll = with(density) { 320.dp.toPx() }
    val titleAlpha = ((scrollState.value - titleStartScroll) / (titleEndScroll - titleStartScroll)).coerceIn(0f, 1f)
    val titleTranslationY = with(density) { (15.dp * (1f - titleAlpha)).toPx() }

    val surfaceColor = MaterialTheme.colorScheme.surface
    val appBarContainerColor = surfaceColor.copy(alpha = toolbarAlpha)

    val iconContentColor by animateColorAsState(
        targetValue = if (toolbarAlpha > 0.7f) MaterialTheme.colorScheme.onSurface else Color.White,
        animationSpec = tween(300)
    )

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background,
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
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = appBarContainerColor,
                    scrolledContainerColor = appBarContainerColor,
                    navigationIconContentColor = iconContentColor,
                    titleContentColor = MaterialTheme.colorScheme.onSurface,
                    actionIconContentColor = iconContentColor
                )
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
                MovieBackdropHeader(
                    movie = movie,
                    scrollState = scrollState,
                    headerHeightPx = headerHeightPx,
                    onTrailerClick = { url -> uriHandler.openUri(url) }
                )

                Column(modifier = Modifier.background(MaterialTheme.colorScheme.background).padding(horizontal = 16.dp)) {
                    MovieHeader(
                        movie = movie,
                        scrollState = scrollState,
                        titleStartScroll = titleStartScroll
                    )

                    Spacer(Modifier.height(24.dp))
                    Text(text = "Handlung", style = MaterialTheme.typography.titleLarge, fontWeight = FontWeight.Bold)
                    Spacer(Modifier.height(8.dp))

                    MovieDescription(
                        movie = movie,
                        onActorClick = onActorClick,
                        onActorNameClick = onActorNameClick
                    )

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
private fun MovieBackdropHeader(
    movie: Movie,
    scrollState: androidx.compose.foundation.ScrollState,
    headerHeightPx: Float,
    onTrailerClick: (String) -> Unit
) {
    val context = LocalContext.current
    val backdropUrl = movie.backdropUrl ?: movie.coverUrl

    Box(
        modifier = Modifier
            .fillMaxWidth()
            .height(350.dp)
            .graphicsLayer {
                translationY = scrollState.value * 0.5f
                alpha = 1f - (scrollState.value / headerHeightPx).coerceIn(0f, 0.7f)
            }
    ) {
        if (backdropUrl != null) {
            val model: Any? = remember(backdropUrl) { resolveImageUrl(context, backdropUrl) }
            AsyncImage(
                model = model,
                contentDescription = null,
                modifier = Modifier.fillMaxSize(),
                contentScale = ContentScale.Crop
            )
        }
        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(
                    Brush.verticalGradient(
                        colors = listOf(Color.Black.copy(alpha = 0.4f), Color.Transparent, MaterialTheme.colorScheme.background)
                    )
                )
        )

        if (!movie.trailerUrl.isNullOrBlank()) {
            FilledIconButton(
                onClick = { onTrailerClick(movie.trailerUrl!!) },
                modifier = Modifier
                    .align(Alignment.Center)
                    .size(64.dp),
                colors = IconButtonDefaults.filledIconButtonColors(
                    containerColor = Color.White.copy(alpha = 0.8f),
                    contentColor = Color.Black
                )
            ) {
                Icon(
                    imageVector = Icons.Default.PlayArrow,
                    contentDescription = "Trailer abspielen",
                    modifier = Modifier.size(40.dp)
                )
            }
        }
    }
}

@Composable
private fun MovieHeader(
    movie: Movie,
    scrollState: androidx.compose.foundation.ScrollState,
    titleStartScroll: Float
) {
    val fadeModifier = Modifier.graphicsLayer {
        alpha = (1f - (scrollState.value / titleStartScroll)).coerceIn(0f, 1f)
    }

    Text(
        text = movie.title ?: "",
        style = MaterialTheme.typography.headlineMedium,
        fontWeight = FontWeight.Bold,
        modifier = fadeModifier
    )

    Spacer(Modifier.height(8.dp))

    Row(
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(16.dp),
        modifier = Modifier.fillMaxWidth().then(fadeModifier)
    ) {
        movie.ratingAge?.let { age -> FskBadge(age = age) }
        movie.year?.let { MetadataItem(icon = Icons.Default.CalendarToday, text = it.toString()) }
        movie.runtime?.let { MetadataItem(icon = Icons.Default.AccessTime, text = "$it Min.") }
        if (!movie.rating.isNullOrBlank()) {
            MetadataItem(icon = Icons.Default.Star, text = "${movie.rating}/10", iconColor = Color(0xFFFFC107))
        }
    }

    if (!movie.director.isNullOrBlank()) {
        Spacer(Modifier.height(8.dp))
        MetadataItem(
            icon = Icons.Default.MovieCreation,
            text = "Regie: ${movie.director}",
            modifier = fadeModifier
        )
    }
}

@Composable
private fun MovieDescription(
    movie: Movie,
    onActorClick: (Int) -> Unit,
    onActorNameClick: (String) -> Unit
) {
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
}

@Composable
fun FskBadge(age: Int) {
    val (color, textColor) = when {
        age <= 0 -> Color.White to Color.Black
        age <= 6 -> Color(0xFFFFEB3B) to Color.Black
        age <= 12 -> Color(0xFF4CAF50) to Color.White
        age <= 16 -> Color(0xFF2196F3) to Color.White
        age >= 18 -> Color(0xFFF44336) to Color.White
        else -> Color.Gray to Color.White
    }

    Surface(
        color = color,
        shape = RoundedCornerShape(4.dp),
        modifier = Modifier.size(width = 36.dp, height = 24.dp).border(width = 1.dp, color = Color.Black.copy(alpha = 0.1f), shape = RoundedCornerShape(4.dp))
    ) {
        Box(contentAlignment = Alignment.Center) {
            Text(
                text = age.toString(),
                color = textColor,
                fontSize = 12.sp,
                fontWeight = FontWeight.Bold
            )
        }
    }
}

@Composable
fun MetadataItem(
    icon: ImageVector,
    text: String,
    modifier: Modifier = Modifier,
    iconColor: Color = MaterialTheme.colorScheme.outline
) {
    Row(
        verticalAlignment = Alignment.CenterVertically,
        modifier = modifier
    ) {
        Icon(
            imageVector = icon,
            contentDescription = null,
            modifier = Modifier.size(16.dp),
            tint = iconColor
        )
        Spacer(Modifier.width(4.dp))
        Text(
            text = text,
            style = MaterialTheme.typography.bodyMedium,
            color = MaterialTheme.colorScheme.outline
        )
    }
}

@Composable
fun BoxsetMovieItem(movie: Movie, onClick: () -> Unit) {
    val context = LocalContext.current
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .height(100.dp)
            .clickable(onClick = onClick),
        shape = MaterialTheme.shapes.medium
    ) {
        Row(modifier = Modifier.fillMaxSize()) {
            if (movie.coverUrl != null) {
                val model: Any? = remember(movie.coverUrl) { resolveImageUrl(context, movie.coverUrl) }
                AsyncImage(
                    model = model,
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
    val context = LocalContext.current
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
                val model: Any? = remember(actor.imageUrl) { resolveImageUrl(context, actor.imageUrl) }
                AsyncImage(
                    model = model,
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
