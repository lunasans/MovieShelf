package at.neuhaus.movieshelf.ui.login

import androidx.compose.animation.AnimatedContent
import androidx.compose.foundation.Image
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Login
import androidx.compose.material.icons.filled.Email
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.Pin
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import at.neuhaus.movieshelf.R
import at.neuhaus.movieshelf.data.local.DataStoreManager

@Composable
fun LoginScreen(
    onLoginSuccess: () -> Unit,
    onResetUrl: () -> Unit
) {
    val viewModel: LoginViewModel = viewModel()
    val context = LocalContext.current
    val dataStoreManager = DataStoreManager(context)

    LaunchedEffect(viewModel.loginSuccess) {
        if (viewModel.loginSuccess) {
            onLoginSuccess()
        }
    }

    Scaffold { padding ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding),
            contentAlignment = Alignment.Center
        ) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(32.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                Image(
                    painter = painterResource(id = R.drawable.logo),
                    contentDescription = "MovieShelf Logo",
                    modifier = Modifier
                        .height(100.dp)
                        .padding(bottom = 32.dp)
                )

                Text(
                    text = if (viewModel.is2faRequired) "Zwei-Faktor-Check" else "Anmelden",
                    style = MaterialTheme.typography.headlineMedium,
                    fontWeight = FontWeight.Bold,
                    modifier = Modifier.padding(bottom = 8.dp)
                )
                
                if (!viewModel.is2faRequired) {
                    Text(
                        text = "Du kannst dir auf https://movieshelf.info eine eigene MovieShelf anlegen.",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.outline,
                        textAlign = TextAlign.Center,
                        modifier = Modifier.padding(start = 16.dp, end = 16.dp, bottom = 16.dp)
                    )
                } else {
                    Spacer(Modifier.height(16.dp))
                }

                AnimatedContent(targetState = viewModel.is2faRequired, label = "LoginMode") { is2fa ->
                    if (is2fa) {
                        Column {
                            Text(
                                "Bitte gib den 6-stelligen Code aus deiner Authenticator-App ein.",
                                style = MaterialTheme.typography.bodyMedium,
                                modifier = Modifier.padding(bottom = 16.dp)
                            )
                            OutlinedTextField(
                                value = viewModel.code2fa,
                                onValueChange = { if (it.length <= 6) viewModel.code2fa = it },
                                label = { Text("2FA Code") },
                                modifier = Modifier.fillMaxWidth(),
                                leadingIcon = { Icon(Icons.Default.Pin, contentDescription = null) },
                                keyboardOptions = KeyboardOptions(
                                    keyboardType = KeyboardType.Number,
                                    imeAction = ImeAction.Done
                                ),
                                singleLine = true
                            )
                        }
                    } else {
                        Column {
                            OutlinedTextField(
                                value = viewModel.email,
                                onValueChange = { viewModel.email = it },
                                label = { Text("E-Mail") },
                                modifier = Modifier.fillMaxWidth(),
                                leadingIcon = { Icon(Icons.Default.Email, contentDescription = null) },
                                keyboardOptions = KeyboardOptions(
                                    keyboardType = KeyboardType.Email,
                                    imeAction = ImeAction.Next
                                ),
                                singleLine = true
                            )

                            Spacer(Modifier.height(16.dp))

                            OutlinedTextField(
                                value = viewModel.password,
                                onValueChange = { viewModel.password = it },
                                label = { Text("Passwort") },
                                modifier = Modifier.fillMaxWidth(),
                                leadingIcon = { Icon(Icons.Default.Lock, contentDescription = null) },
                                visualTransformation = PasswordVisualTransformation(),
                                keyboardOptions = KeyboardOptions(
                                    keyboardType = KeyboardType.Password,
                                    imeAction = ImeAction.Done
                                ),
                                singleLine = true
                            )
                        }
                    }
                }

                if (viewModel.error != null) {
                    Text(
                        text = viewModel.error!!,
                        color = MaterialTheme.colorScheme.error,
                        style = MaterialTheme.typography.bodySmall,
                        modifier = Modifier.padding(top = 16.dp)
                    )
                }

                Spacer(Modifier.height(32.dp))

                Button(
                    onClick = {
                        if (viewModel.is2faRequired) {
                            viewModel.onVerify2faClick(dataStoreManager)
                        } else {
                            viewModel.onLoginClick(dataStoreManager)
                        }
                    },
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(50.dp),
                    enabled = !viewModel.isLoading
                ) {
                    if (viewModel.isLoading) {
                        CircularProgressIndicator(modifier = Modifier.size(24.dp), color = MaterialTheme.colorScheme.onPrimary)
                    } else {
                        val icon = if (viewModel.is2faRequired) Icons.Default.Pin else Icons.AutoMirrored.Filled.Login
                        Icon(icon, contentDescription = null)
                        Spacer(Modifier.width(8.dp))
                        Text(if (viewModel.is2faRequired) "Code verifizieren" else "Anmelden")
                    }
                }

                TextButton(
                    onClick = onResetUrl,
                    modifier = Modifier.padding(top = 16.dp)
                ) {
                    Icon(Icons.Default.Refresh, contentDescription = null, modifier = Modifier.size(18.dp))
                    Spacer(Modifier.width(8.dp))
                    Text("Server-URL ändern")
                }
            }
        }
    }
}
