# Retrofit don't strip methods for serialization
-keepattributes Signature, InnerClasses, EnclosingMethod
-keepattributes RuntimeVisibleAnnotations, RuntimeVisibleParameterAnnotations
-keepattributes AnnotationDefault

# Keep Retrofit interface methods
-keep class at.neuhaus.movieshelf.data.api.** { *; }

# Keep Data Models from being obfuscated (required for GSON/Serialization)
-keep class at.neuhaus.movieshelf.data.model.** { *; }

# Gson specific rules
-keep class com.google.gson.** { *; }
-keep class sun.misc.Unsafe { *; }

# OkHttp specific
-dontwarn okhttp3.**
-dontwarn okio.**
-dontwarn javax.annotation.**
-dontwarn org.conscrypt.**
