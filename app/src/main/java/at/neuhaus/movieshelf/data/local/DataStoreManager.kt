package at.neuhaus.movieshelf.data.local

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map

val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "settings")

class DataStoreManager(private val context: Context) {

    companion object {
        val SERVER_URL_KEY    = stringPreferencesKey("server_url")
        val AUTH_TOKEN_KEY    = stringPreferencesKey("auth_token")
        val OAUTH_STATE_KEY   = stringPreferencesKey("oauth_state")
        val OAUTH_VERIFIER_KEY = stringPreferencesKey("oauth_verifier")
    }

    val serverUrl: Flow<String?> = context.dataStore.data.map { it[SERVER_URL_KEY] }
    val authToken: Flow<String?> = context.dataStore.data.map { it[AUTH_TOKEN_KEY] }

    suspend fun saveServerUrl(url: String) {
        context.dataStore.edit { it[SERVER_URL_KEY] = url }
    }

    suspend fun saveAuthToken(token: String?) {
        context.dataStore.edit { prefs ->
            if (token == null) prefs.remove(AUTH_TOKEN_KEY) else prefs[AUTH_TOKEN_KEY] = token
        }
    }

    suspend fun saveOAuthState(state: String, verifier: String) {
        context.dataStore.edit { prefs ->
            prefs[OAUTH_STATE_KEY]    = state
            prefs[OAUTH_VERIFIER_KEY] = verifier
        }
    }

    suspend fun loadOAuthState(): Pair<String?, String?> {
        val prefs = context.dataStore.data.first()
        return prefs[OAUTH_STATE_KEY] to prefs[OAUTH_VERIFIER_KEY]
    }

    suspend fun clearOAuthState() {
        context.dataStore.edit { prefs ->
            prefs.remove(OAUTH_STATE_KEY)
            prefs.remove(OAUTH_VERIFIER_KEY)
        }
    }
}
