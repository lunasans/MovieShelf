package at.neuhaus.movieshelf.ui.dashboard

import android.util.Log
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.LibraryBooks
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.RectangleShape
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import at.neuhaus.movieshelf.R
import at.neuhaus.movieshelf.data.api.RetrofitClient
import at.neuhaus.movieshelf.data.model.Movie
import at.neuhaus.movieshelf.ui.util.resolveImageUrl
import coil.compose.AsyncImage
import coil.compose.AsyncImagePainter

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DashboardScreen(
    viewModel: DashboardViewModel = viewModel(),
    onMovieClick: (Movie) -> Unit,
    onLogout: () -> Unit,
    onAddMovie: () -> Unit,
    onAboutClick: () -> Unit,
    onProfileClick: () -> Unit
) {
    Scaffold(
        topBar = {
            Surface(tonalElevation = 2.dp) {
                Column {
                    CenterAlignedTopAppBar(
                        navigationIcon = {
                            IconButton(onClick = onAboutClick) {
                                Icon(Icons.Default.Info, contentDescription = "Über MovieShelf")
                            }
                        },
                        title = {
                            Image(
                                painter = painterResource(id = R.drawable.logo),
                                contentDescription = "MovieShelf",
                                modifier = Modifier.height(32.dp)
                            )
                        },
                        actions = {
                            IconButton(onClick = onProfileClick) {
                                Icon(Icons.Default.Person, contentDescription = "Profil")
                            }
                            IconButton(onClick = onLogout) {
                                Icon(Icons.AutoMirrored.Filled.Logout, contentDescription = "Abmelden")
                            }
                        },
                        colors = TopAppBarDefaults.centerAlignedTopAppBarColors(
                            containerColor = Color.Transparent
                        )
                    )
                    
                    OutlinedTextField(
                        value = viewModel.searchQuery,
                        onValueChange = { viewModel.onSearchQueryChange(it) },
                        placeholder = { Text("Filme suchen...") },
                        leadingIcon = { Icon(Icons.Default.Search, contentDescription = null) },
                        trailingIcon = {
                            if (viewModel.searchQuery.isNotEmpty()) {
                                IconButton(onClick = { viewModel.onSearchQueryChange("") }) {
                                    Icon(Icons.Default.Close, contentDescription = "Löschen")
                                }
                            }
                        },
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(horizontal = 16.dp)
                            .padding(bottom = 8.dp),
                        singleLine = true,
                        shape = MaterialTheme.shapes.medium,
                        colors = OutlinedTextFieldDefaults.colors(
                            unfocusedContainerColor = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.3f),
                            focusedContainerColor = MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.3f),
                            unfocusedBorderColor = Color.Transparent
                        )
                    )

                    SecondaryTabRow(
                        selectedTabIndex = viewModel.selectedTab,
                        containerColor = Color.Transparent,
                        divider = {}
                    ) {
                        Tab(
                            selected = viewModel.selectedTab == 0,
                            onClick = { viewModel.onTabSelected(0) },
                            text = { Text("Neu", style = MaterialTheme.typography.labelLarge) },
                            icon = { Icon(Icons.Default.NewReleases, contentDescription = null, modifier = Modifier.size(18.dp)) }
                        )
                        Tab(
                            selected = viewModel.selectedTab == 1,
                            onClick = { viewModel.onTabSelected(1) },
                            text = { Text("Alle", style = MaterialTheme.typography.labelLarge) },
                            icon = { Icon(Icons.Default.Movie, contentDescription = null, modifier = Modifier.size(18.dp)) }
                        )
                    }
                }
            }
        },
        floatingActionButton = {
            FloatingActionButton(onClick = onAddMovie) {
                Icon(Icons.Default.Add, contentDescription = "Film hinzufügen")
            }
        }
    ) { padding ->
        PullToRefreshBox(
            isRefreshing = viewModel.isRefreshing,
            onRefresh = { viewModel.loadMovies(refresh = true) },
            modifier = Modifier.padding(padding)
        ) {
            if (viewModel.isLoading) {
                Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator()
                }
            } else if (viewModel.error != null) {
                Column(Modifier.fillMaxSize(), horizontalAlignment = Alignment.CenterHorizontally, verticalArrangement = Arrangement.Center) {
                    Text(viewModel.error!!, color = MaterialTheme.colorScheme.error)
                    Button(onClick = { viewModel.loadMovies() }) { Text("Erneut versuchen") }
                }
            } else {
                LazyVerticalGrid(
                    columns = GridCells.Adaptive(150.dp),
                    contentPadding = PaddingValues(8.dp),
                    modifier = Modifier.fillMaxSize()
                ) {
                    items(viewModel.movies) { movie ->
                        MovieItem(movie = movie, onClick = { onMovieClick(movie) })
                    }
                }
            }
        }
    }
}

@Composable
fun MovieItem(movie: Movie, onClick: () -> Unit) {
    val context = LocalContext.current
    Card(
        modifier = Modifier
            .padding(4.dp)
            .fillMaxWidth()
            .height(250.dp),
        onClick = onClick
    ) {
        Column {
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .weight(1f)
                    .background(MaterialTheme.colorScheme.secondaryContainer),
                contentAlignment = Alignment.Center
            ) {
                if (movie.coverUrl != null) {
                    val model: Any? = remember(movie.coverUrl, RetrofitClient.baseUrl) {
                        resolveImageUrl(context, movie.coverUrl)
                    }

                    var imageError by remember { mutableStateOf(false) }
                    var imageLoading by remember { mutableStateOf(true) }

                    AsyncImage(
                        model = model,
                        contentDescription = movie.title,
                        modifier = Modifier.fillMaxSize(),
                        contentScale = ContentScale.Crop,
                        onState = { state ->
                            imageLoading = state is AsyncImagePainter.State.Loading
                            imageError = state is AsyncImagePainter.State.Error
                            if (state is AsyncImagePainter.State.Error) {
                                Log.e("CoilError", "Fehler beim Laden von ${movie.title}: ${state.result.throwable.message} (URL: $model)")
                            }
                        }
                    )
                    
                    if (imageLoading) {
                        CircularProgressIndicator(modifier = Modifier.size(24.dp), strokeWidth = 2.dp)
                    }
                    
                    if (imageError) {
                        Icon(Icons.Default.Warning, contentDescription = null, tint = MaterialTheme.colorScheme.error)
                    }
                } else {
                    Text(text = movie.title?.take(1) ?: "?", style = MaterialTheme.typography.displayLarge)
                }

                // Boxset Badge
                if (movie.isBoxset == true) {
                    Surface(
                        modifier = Modifier.align(Alignment.TopEnd).padding(8.dp),
                        color = MaterialTheme.colorScheme.primary,
                        shape = MaterialTheme.shapes.extraSmall,
                        tonalElevation = 4.dp
                    ) {
                        Row(
                            modifier = Modifier.padding(horizontal = 4.dp, vertical = 2.dp),
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Icon(Icons.AutoMirrored.Filled.LibraryBooks, contentDescription = null, modifier = Modifier.size(12.dp), tint = MaterialTheme.colorScheme.onPrimary)
                            Spacer(Modifier.width(2.dp))
                            Text("BOX", style = MaterialTheme.typography.labelSmall, fontWeight = FontWeight.Bold, color = MaterialTheme.colorScheme.onPrimary)
                        }
                    }
                }

                // Tag Banderole (top-right diagonal ribbon, wie v2-saas)
                if (!movie.tag.isNullOrBlank()) {
                    val firstTag = movie.tag.split(",").first().trim()
                    val (ribbonColor, ribbonLabel) = movieTagStyle(firstTag)
                    Box(
                        modifier = Modifier
                            .align(Alignment.TopEnd)
                            .size(56.dp)
                            .clip(RectangleShape)
                    ) {
                        Box(
                            modifier = Modifier
                                .width(90.dp)
                                .background(ribbonColor)
                                .align(Alignment.Center)
                                .graphicsLayer {
                                    rotationZ = 45f
                                    translationX = 14.dp.toPx()
                                    translationY = (-14).dp.toPx()
                                }
                                .padding(vertical = 3.dp),
                            contentAlignment = Alignment.Center
                        ) {
                            Text(
                                text = ribbonLabel,
                                color = Color.White,
                                fontSize = 7.sp,
                                fontWeight = FontWeight.Black,
                                letterSpacing = 0.5.sp
                            )
                        }
                    }
                }
            }
            
            Column(Modifier.padding(8.dp)) {
                Text(
                    text = movie.title ?: "Unbekannter Titel",
                    style = MaterialTheme.typography.titleSmall,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis
                )
                
                val subText = if (movie.isBoxset == true) {
                    val count = movie.boxsetChildren?.size ?: 0
                    if (count > 0) "$count Filme" else "Sammlung"
                } else {
                    movie.year?.toString() ?: ""
                }
                
                Text(
                    text = subText,
                    style = MaterialTheme.typography.bodySmall
                )
            }
        }
    }
}

fun movieTagStyle(tag: String): Pair<Color, String> = when (tag.lowercase()) {
    "dvd"                 -> Color(0xFF7C3E0A) to "DVD"
    "bluray", "blu-ray"   -> Color(0xFF1A3A6E) to "BLU-RAY"
    "4k", "4k uhd"        -> Color(0xFF0A4F5E) to "4K UHD"
    "streaming", "stream" -> Color(0xFF14532D) to "STREAM"
    "digital"             -> Color(0xFF3B1D6E) to "DIGITAL"
    "vhs"                 -> Color(0xFF44403C) to "VHS"
    "leihe"               -> Color(0xFF78350F) to "LEIHE"
    else                  -> Color(0xFF1F2937) to tag.uppercase()
}
