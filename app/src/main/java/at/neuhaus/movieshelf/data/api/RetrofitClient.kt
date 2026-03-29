package at.neuhaus.movieshelf.data.api

import android.content.Context
import android.util.Log
import at.neuhaus.movieshelf.data.SessionManager
import com.google.gson.GsonBuilder
import okhttp3.Cache
import okhttp3.HttpUrl.Companion.toHttpUrlOrNull
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.io.File

object RetrofitClient {
    var baseUrl: String = "http://10.0.2.2:8000/"
        private set

    private val authInterceptor = Interceptor { chain ->
        val original = chain.request()
        val requestBuilder = original.newBuilder()
        
        val isApiRequest = original.url.encodedPath.contains("/api/")
        
        if (isApiRequest) {
            requestBuilder.header("Accept", "application/json")
            requestBuilder.header("Content-Type", "application/json")
            
            SessionManager.token?.let {
                requestBuilder.header("Authorization", "Bearer $it")
            }
        }
        
        chain.proceed(requestBuilder.build())
    }

    private val logging = HttpLoggingInterceptor { message ->
        Log.d("API-Log", message)
    }.apply {
        level = HttpLoggingInterceptor.Level.BODY
    }

    private var _httpClient: OkHttpClient? = null
    val httpClient: OkHttpClient
        get() = _httpClient ?: OkHttpClient.Builder()
            .addInterceptor(authInterceptor)
            .addInterceptor(logging)
            .followRedirects(true)
            .build()

    private var _api: MovieShelfApi? = null

    val api: MovieShelfApi
        get() = _api ?: throw IllegalStateException("RetrofitClient not initialized")

    fun initialize(url: String, context: Context? = null): Boolean {
        try {
            val finalUrl = if (url.endsWith("/")) url else "$url/"
            finalUrl.toHttpUrlOrNull() ?: return false
            
            val gson = GsonBuilder()
                .setLenient()
                .create()

            val clientBuilder = OkHttpClient.Builder()
                .addInterceptor(authInterceptor)
                .addInterceptor(logging)
                .followRedirects(true)

            // Cache hinzufügen, wenn Context vorhanden ist
            context?.let {
                val cacheSize = 10L * 1024L * 1024L // 10 MB
                val cacheDir = File(it.cacheDir, "http_cache")
                clientBuilder.cache(Cache(cacheDir, cacheSize))
            }

            _httpClient = clientBuilder.build()
            baseUrl = finalUrl
            
            _api = Retrofit.Builder()
                .baseUrl(baseUrl)
                .addConverterFactory(GsonConverterFactory.create(gson))
                .client(_httpClient!!)
                .build()
                .create(MovieShelfApi::class.java)
            return true
        } catch (e: Exception) {
            Log.e("RetrofitClient", "Init failed", e)
            return false
        }
    }
}
