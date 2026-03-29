# MovieShelf - Android App

Welcome to the **MovieShelf** Android application repository! This is the native Android client for the MovieShelf ecosystem, designed to help you manage your digital movie collection, explore actor details, and keep track of your watched content on the go.

## Features

- **Jetpack Compose UI**: Built entirely with modern, declarative Android UI using Jetpack Compose, offering a responsive and beautiful user experience.
- **Movie Dashboard**: View your collection at a glance, featuring recently added movies and personalized recommendations.
- **Detailed Information**: Access rich details about movies, including synopses, cast information, and metadata.
- **Actor Profiles**: Explore dedicated actor pages with their filmographies and related movies in your collection.
- **Collection Management**: Add new movies to your collection directly from your mobile device.
- **User Authentication**: Secure login and profile management system.
- **REST API Integration**: Connects seamlessly with the MovieShelf backend using Retrofit for fast and reliable data synchronization.

## Tech Stack

- **Language**: [Kotlin](https://kotlinlang.org/)
- **UI Framework**: [Jetpack Compose](https://developer.android.com/jetpack/compose)
- **Networking**: [Retrofit](https://square.github.io/retrofit/) & [OkHttp](https://square.github.io/okhttp/)
- **Local Storage**: [Preferences DataStore](https://developer.android.com/topic/libraries/architecture/datastore)
- **Architecture**: MVVM (Model-View-ViewModel)

## Getting Started

### Prerequisites

- Android Studio (latest stable version recommended)
- Java JDK 17 or higher
- Android SDK (API level 24+)

### Building from Source

1. Clone the repository and checkout the `android-app` branch:
   ```bash
   git clone https://github.com/lunasans/MovieShelf.git
   cd MovieShelf
   git checkout android-app
   ```
2. Open the project in **Android Studio**.
3. Allow Gradle to sync dependencies automatically.
4. Run the project on an Android emulator or a physical device via USB/Wi-Fi debugging.

## Contributing

Feel free to open issues or submit pull requests if you want to contribute to the app's development.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
