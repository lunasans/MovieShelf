package at.neuhaus.movieshelf.ui.dashboard

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
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import androidx.lifecycle.viewmodel.compose.viewModel
import at.neuhaus.movieshelf.R
import at.neuhaus.movieshelf.data.api.RetrofitClient
import at.neuhaus.movieshelf.data.model.Movie
import coil.compose.AsyncImage
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
                val response = RetrofitClient.api.getMovies(
                    page = 1,
                    perPage = if (selectedTab == 1) 1000 else 20
                )
                
                val resultList = response.data ?: emptyList()
                
                // Filtere Filme, die Teil eines Boxsets sind (boxsetParentId != null)
                val filteredList = resultList.filter { it.boxsetParentId == null }
                
                movies = if (selectedTab == 0) {
                    filteredList.sortedByDescending { it.id }
                } else {
                    filteredList.sortedBy { it.title?.lowercase() ?: "" }
                }
            } catch (e: Exception) {
                error = "Fehler beim Laden der Filme: ${e.message}"
            } finally {
                isLoading = false
                isRefreshing = false
            }
        }
    }

    fun onTabSelected(index: Int) {
        selectedTab = index
        loadMovies()
    }

    fun onSearchQueryChange(newQuery: String) {
        searchQuery = newQuery
        searchJob?.cancel()
        searchJob = viewModelScope.launch {
            delay(500) // Debounce
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
            val response = RetrofitClient.api.searchMovies(query)
            val resultList = response.data ?: emptyList()
            movies = resultList.sortedBy { it.title?.lowercase() ?: "" }
        } catch (e: Exception) {
            error = "Suche fehlgeschlagen: ${e.message}"
        } finally {
            isLoading = false
        }
    }
}

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
                    val imageUrl = if (movie.coverUrl.startsWith("http")) {
                        movie.coverUrl
                    } else {
                        RetrofitClient.baseUrl.removeSuffix("/") + "/" + movie.coverUrl.removePrefix("/")
                    }

                    var imageError by remember { mutableStateOf(false) }
                    var imageLoading by remember { mutableStateOf(true) }

                    AsyncImage(
                        model = imageUrl,
                        contentDescription = movie.title,
                        modifier = Modifier.fillMaxSize(),
                        contentScale = ContentScale.Crop,
                        onState = { state ->
                            imageLoading = state is coil.compose.AsyncImagePainter.State.Loading
                            imageError = state is coil.compose.AsyncImagePainter.State.Error
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
