package at.neuhaus.movieshelf.data

import at.neuhaus.movieshelf.data.model.User

object SessionManager {
    var token: String? = null
    var user: User? = null
    
    val isDemo: Boolean
        get() = token == "demo_token_123456789"
}
