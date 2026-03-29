package at.neuhaus.movieshelf.data.local

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map

val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "settings")

class DataStoreManager(private val context: Context) {

    companion object {
        val SERVER_URL_KEY = stringPreferencesKey("server_url")
        val AUTH_TOKEN_KEY = stringPreferencesKey("auth_token")
    }

    val serverUrl: Flow<String?> = context.dataStore.data.map { preferences ->
        preferences[SERVER_URL_KEY]
    }

    val authToken: Flow<String?> = context.dataStore.data.map { preferences ->
        preferences[AUTH_TOKEN_KEY]
    }

    suspend fun saveServerUrl(url: String) {
        context.dataStore.edit { preferences ->
            preferences[SERVER_URL_KEY] = url
        }
    }

    suspend fun saveAuthToken(token: String?) {
        context.dataStore.edit { preferences ->
            if (token == null) {
                preferences.remove(AUTH_TOKEN_KEY)
            } else {
                preferences[AUTH_TOKEN_KEY] = token
            }
        }
    }
}
