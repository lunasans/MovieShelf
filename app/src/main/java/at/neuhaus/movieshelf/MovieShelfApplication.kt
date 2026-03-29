package at.neuhaus.movieshelf

import android.app.Application
import at.neuhaus.movieshelf.data.api.RetrofitClient
import coil.ImageLoader
import coil.ImageLoaderFactory
import coil.disk.DiskCache
import coil.memory.MemoryCache
import coil.util.DebugLogger

class MovieShelfApplication : Application(), ImageLoaderFactory {
    override fun newImageLoader(): ImageLoader {
        return ImageLoader.Builder(this)
            .okHttpClient {
                RetrofitClient.httpClient
            }
            .memoryCache {
                MemoryCache.Builder(this)
                    .maxSizePercent(0.25) // Nutze 25% des verfügbaren RAMs
                    .build()
            }
            .diskCache {
                DiskCache.Builder()
                    .directory(this.cacheDir.resolve("image_cache"))
                    .maxSizePercent(0.02) // Nutze ca. 2% des Speichers oder fest 50MB
                    .maxSizeBytes(50L * 1024 * 1024) // 50 MB Limit
                    .build()
            }
            .crossfade(true)
            .logger(DebugLogger())
            .build()
    }
}
