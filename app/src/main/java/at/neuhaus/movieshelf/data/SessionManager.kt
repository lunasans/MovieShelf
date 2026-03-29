package at.neuhaus.movieshelf.data

import at.neuhaus.movieshelf.data.model.User

object SessionManager {
    var token: String? = null
    var user: User? = null
}
