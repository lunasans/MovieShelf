package at.neuhaus.movieshelf.ui.add

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.Download
import androidx.compose.material.icons.filled.Search
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import androidx.lifecycle.viewmodel.compose.viewModel
import at.neuhaus.movieshelf.data.api.RetrofitClient
import at.neuhaus.movieshelf.data.model.TmdbImportRequest
import coil.compose.AsyncImage
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

class AddMovieViewModel : ViewModel() {
    var searchQuery by mutableStateOf("")
    var searchResults by mutableStateOf<List<Map<String, Any>>>(emptyList())
    var isLoading by mutableStateOf(false)
    var isImporting by mutableStateOf(false)
    var error by mutableStateOf<String?>(null)
    var successMessage by mutableStateOf<String?>(null)

    private var searchJob: Job? = null

    fun onSearchQueryChange(newQuery: String) {
        searchQuery = newQuery
        searchJob?.cancel()
        if (newQuery.length < 2) {
            searchResults = emptyList()
            return
        }
        searchJob = viewModelScope.launch {
            delay(500)
            performTmdbSearch(newQuery)
        }
    }

    private suspend fun performTmdbSearch(query: String) {
        isLoading = true
        error = null
        try {
            val response = RetrofitClient.api.searchTmdb(query)
            @Suppress("UNCHECKED_CAST")
            searchResults = response["results"] as? List<Map<String, Any>> ?: emptyList()
        } catch (e: Exception) {
            error = "TMDb-Suche fehlgeschlagen: ${e.message}"
        } finally {
            isLoading = false
        }
    }

    fun importMovie(tmdbId: Int, onComplete: () -> Unit) {
        viewModelScope.launch {
            isImporting = true
            error = null
            try {
                RetrofitClient.api.importFromTmdb(TmdbImportRequest(tmdbId = tmdbId, type = "movie"))
                successMessage = "Film erfolgreich importiert!"
                delay(1500)
                onComplete()
            } catch (e: Exception) {
                error = "Import fehlgeschlagen: ${e.message}"
            } finally {
                isImporting = false
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AddMovieScreen(
    onBack: () -> Unit,
    onMovieImported: () -> Unit
) {
    val viewModel: AddMovieViewModel = viewModel()
    val snackbarHostState = remember { SnackbarHostState() }

    LaunchedEffect(viewModel.error) {
        viewModel.error?.let {
            snackbarHostState.showSnackbar(it)
            viewModel.error = null
        }
    }

    Scaffold(
        snackbarHost = { SnackbarHost(snackbarHostState) },
        topBar = {
            Surface(tonalElevation = 3.dp) {
                Column {
                    TopAppBar(
                        title = { Text("Film hinzufügen") },
                        navigationIcon = {
                            IconButton(onClick = onBack) {
                                Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Zurück")
                            }
                        }
                    )
                    OutlinedTextField(
                        value = viewModel.searchQuery,
                        onValueChange = { viewModel.onSearchQueryChange(it) },
                        placeholder = { Text("TMDb durchsuchen...") },
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
                            .padding(horizontal = 16.dp, vertical = 8.dp),
                        singleLine = true,
                        shape = MaterialTheme.shapes.medium
                    )
                    Spacer(modifier = Modifier.height(8.dp))
                }
            }
        }
    ) { padding ->
        Box(modifier = Modifier.padding(padding).fillMaxSize()) {
            if (viewModel.isLoading) {
                CircularProgressIndicator(modifier = Modifier.align(Alignment.Center))
            } else if (viewModel.searchResults.isEmpty() && viewModel.searchQuery.length >= 2) {
                Text(
                    "Keine Ergebnisse gefunden",
                    modifier = Modifier.align(Alignment.Center),
                    style = MaterialTheme.typography.bodyLarge
                )
            } else {
                LazyColumn(modifier = Modifier.fillMaxSize()) {
                    items(viewModel.searchResults) { item ->
                        TmdbMovieItem(
                            item = item,
                            onImport = {
                                val id = (item["id"] as? Number)?.toInt()
                                if (id != null) {
                                    viewModel.importMovie(id, onMovieImported)
                                }
                            }
                        )
                        HorizontalDivider(modifier = Modifier.padding(horizontal = 16.dp), color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.5f))
                    }
                }
            }

            if (viewModel.isImporting) {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = Color.Black.copy(alpha = 0.5f)
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        Card {
                            Column(
                                modifier = Modifier.padding(24.dp),
                                horizontalAlignment = Alignment.CenterHorizontally
                            ) {
                                CircularProgressIndicator()
                                Spacer(Modifier.height(16.dp))
                                Text("Importiere Film...")
                            }
                        }
                    }
                }
            }

            if (viewModel.successMessage != null) {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = Color.Black.copy(alpha = 0.5f)
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        Card(colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.primaryContainer)) {
                            Text(
                                viewModel.successMessage!!,
                                modifier = Modifier.padding(24.dp),
                                color = MaterialTheme.colorScheme.onPrimaryContainer,
                                fontWeight = FontWeight.Bold
                            )
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun TmdbMovieItem(item: Map<String, Any>, onImport: () -> Unit) {
    val title = item["title"] as? String ?: (item["name"] as? String) ?: "Unbekannt"
    val overview = item["overview"] as? String ?: ""
    val releaseDate = item["release_date"] as? String ?: (item["first_air_date"] as? String) ?: ""
    val posterPath = item["poster_path"] as? String
    val posterUrl = if (posterPath != null) "https://image.tmdb.org/t/p/w200$posterPath" else null

    ListItem(
        headlineContent = { Text(title, fontWeight = FontWeight.Bold) },
        supportingContent = {
            Column {
                if (releaseDate.isNotEmpty()) {
                    Text(releaseDate, style = MaterialTheme.typography.bodySmall)
                }
                Text(
                    overview,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis,
                    style = MaterialTheme.typography.bodyMedium
                )
            }
        },
        leadingContent = {
            Surface(
                modifier = Modifier.size(60.dp, 90.dp),
                shape = MaterialTheme.shapes.extraSmall,
                color = MaterialTheme.colorScheme.surfaceVariant
            ) {
                if (posterUrl != null) {
                    AsyncImage(
                        model = posterUrl,
                        contentDescription = title,
                        modifier = Modifier.fillMaxSize(),
                        contentScale = ContentScale.Crop
                    )
                }
            }
        },
        trailingContent = {
            IconButton(onClick = onImport) {
                Icon(Icons.Default.Download, contentDescription = "Importieren", tint = MaterialTheme.colorScheme.primary)
            }
        },
        modifier = Modifier.clickable { onImport() }
    )
}
