import { app, BrowserWindow, ipcMain, shell, protocol, net } from 'electron'
import { join, sep, extname } from 'path'
import { createWriteStream, existsSync, unlinkSync } from 'fs'
import { tmpdir } from 'os'
import { createHash } from 'crypto'
import axios from 'axios'
import { setupDatabase, getDb } from './database'
import { registerMovieHandlers } from './handlers/movies'
import { registerSettingsHandlers } from './handlers/settings'
import { registerMediaHandlers } from './handlers/media'
import { registerListHandlers } from './handlers/lists'
import { registerStatsHandlers } from './handlers/stats'
import { registerOAuthHandlers } from './handlers/oauth'

const isDev = !app.isPackaged || process.env.NODE_ENV === 'development'

let mainWindow: BrowserWindow | null = null

// ── OAuth Deep-Link Handler ───────────────────────────────────────────────────

function handleDeepLink(url: string) {
  if (!url.startsWith('movieshelf://oauth/callback')) return
  try {
    const parsed = new URL(url)
    const code   = parsed.searchParams.get('code')
    const state  = parsed.searchParams.get('state')
    if (code) mainWindow?.webContents.send('oauth:callback', { code, state })
  } catch { /* ignore malformed URLs */ }
}

// Single-instance lock so deep links on Windows/Linux reach the running app
const gotSingleInstanceLock = app.requestSingleInstanceLock()
if (!gotSingleInstanceLock) {
  app.quit()
}

app.on('second-instance', (_event, argv) => {
  const deepLink = argv.find(a => a.startsWith('movieshelf://'))
  if (deepLink) handleDeepLink(deepLink)
  if (mainWindow) { mainWindow.show(); mainWindow.focus() }
})

// macOS deep link
app.on('open-url', (event, url) => {
  event.preventDefault()
  handleDeepLink(url)
})

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1400,
    height: 900,
    minWidth: 1024,
    minHeight: 600,
    frame: false,
    titleBarStyle: 'hidden',
    backgroundColor: '#0a0a0f',
    webPreferences: {
      preload: join(__dirname, 'preload.js'),
      contextIsolation: true,
      nodeIntegration: false,
      webviewTag: false,
    },
    icon: join(__dirname, '../public/icon.png'),
  })

  if (isDev) {
    mainWindow.loadURL('http://localhost:5173')
    mainWindow.webContents.openDevTools()
  } else {
    mainWindow.loadFile(join(__dirname, '../dist/index.html'))
  }

  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    if (url.startsWith('https://') || url.startsWith('http://')) {
      shell.openExternal(url)
    }
    return { action: 'deny' }
  })

  // Prompt for sync before closing
  mainWindow.on('close', (e) => {
    if ((app as any).isQuitting) return

    e.preventDefault()

    const db = getDb()
    
    // Check for dirty records
    const result = db.prepare(`
      SELECT COUNT(*) as count FROM movies 
      WHERE remote_id IS NULL 
         OR updated_at > synced_at 
         OR is_deleted = 1
    `).get() as { count: number }

    const dirtyCount = result ? result.count : 0

    if (dirtyCount > 0) {
      const { dialog } = require('electron')
      dialog.showMessageBox(mainWindow!, {
        type: 'question',
        buttons: ['Jetzt Synchronisieren', 'Trotzdem Beenden', 'Abbrechen'],
        defaultId: 0,
        cancelId: 2,
        title: 'Nicht synchronisierte Änderungen',
        message: `Du hast ${dirtyCount} Filme noch nicht synchronisiert.`,
        detail: 'Möchtest du jetzt synchronisieren, bevor du das Programm schließt?'
      }).then((result: { response: number }) => {
        if (result.response === 0) {
          mainWindow!.webContents.send('navigate-to', '/sync')
        } else if (result.response === 1) {
          (app as any).isQuitting = true
          app.quit()
        }
      })
    } else {
      (app as any).isQuitting = true
      app.quit()
    }
  })
}

app.whenReady().then(() => {
  // Register custom protocol for OAuth deep links
  app.setAsDefaultProtocolClient('movieshelf')

  setupDatabase()
  registerMovieHandlers()
  registerSettingsHandlers()
  registerMediaHandlers()
  registerListHandlers()
  registerStatsHandlers()
  registerOAuthHandlers()

  // Register local resource protocol
  protocol.handle('movie-resource', (request) => {
    const coversDir = join(app.getPath('userData'), 'covers')
    const raw = request.url.slice('movie-resource://'.length)
    // Strip any path separators to prevent directory traversal
    const fileName = raw.replace(/[/\\]/g, '')
    const filePath = join(coversDir, fileName)
    // Ensure the resolved path stays inside the covers directory
    if (!filePath.startsWith(coversDir + sep)) {
      return new Response('Forbidden', { status: 403 })
    }
    return net.fetch('file://' + filePath)
  })

  createWindow()

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) createWindow()
  })
})

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit()
})

// Window controls
ipcMain.on('window:minimize', () => mainWindow?.minimize())
ipcMain.on('window:maximize', () => {
  if (mainWindow?.isMaximized()) {
    mainWindow.unmaximize()
  } else {
    mainWindow?.maximize()
  }
})
ipcMain.on('window:close', () => {
  if (mainWindow) {
    mainWindow.close()
  }
})

ipcMain.handle('trailer:open', (_event, url: string) => {
  // Only allow YouTube watch URLs
  let parsedUrl: URL
  try { parsedUrl = new URL(url) } catch { return }
  if (parsedUrl.protocol !== 'https:') return
  if (!/^(www\.)?youtube\.com$/.test(parsedUrl.hostname)) return
  if (!parsedUrl.pathname.startsWith('/watch')) return

  const win = new BrowserWindow({
    width: 1280,
    height: 720,
    backgroundColor: '#000000',
    autoHideMenuBar: true,
    webPreferences: {
      contextIsolation: true,
      nodeIntegration: false,
    },
  })

  win.webContents.setUserAgent(
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
  )
  win.loadURL(url)

  win.webContents.on('before-input-event', (_e, input) => {
    if (input.key === 'Escape') win.close()
  })
})

ipcMain.handle('get-is-dev', () => isDev)
ipcMain.handle('app:get-version', () => app.getVersion())

const ALLOWED_UPDATE_EXTENSIONS = new Set(['.exe', '.dmg', '.appimage', '.deb', '.rpm', '.zip'])

ipcMain.handle('update:install', async (_event, url: string, sha256?: string) => {
  // Validate URL: must be HTTPS with an allowed installer extension
  let parsedUrl: URL
  try { parsedUrl = new URL(url) } catch {
    return { success: false, error: 'Ungültige URL.' }
  }
  if (parsedUrl.protocol !== 'https:') {
    return { success: false, error: 'Nur HTTPS-URLs erlaubt.' }
  }
  if (!ALLOWED_UPDATE_EXTENSIONS.has(extname(parsedUrl.pathname).toLowerCase())) {
    return { success: false, error: 'Ungültiges Dateiformat.' }
  }

  const fileName = parsedUrl.pathname.split('/').pop() || 'update.exe'
  const destPath = join(tmpdir(), fileName)

  // Remove stale file if present
  if (existsSync(destPath)) unlinkSync(destPath)

  try {
    const response = await axios({ url, method: 'GET', responseType: 'stream' })
    const total: number = parseInt(response.headers['content-length'] ?? '0', 10)
    let received = 0

    await new Promise<void>((resolve, reject) => {
      const writer = createWriteStream(destPath)
      response.data.on('data', (chunk: Buffer) => {
        received += chunk.length
        if (total > 0 && mainWindow) {
          mainWindow.webContents.send('update:progress', Math.round((received / total) * 100))
        }
      })
      response.data.pipe(writer)
      writer.on('finish', resolve)
      writer.on('error', reject)
    })

    // SHA256 verification
    if (sha256) {
      const fileHash = await new Promise<string>((resolve, reject) => {
        const hash = createHash('sha256')
        const { createReadStream } = require('fs')
        const stream = createReadStream(destPath)
        stream.on('data', (d: Buffer) => hash.update(d))
        stream.on('end', () => resolve(hash.digest('hex')))
        stream.on('error', reject)
      })
      if (fileHash.toLowerCase() !== sha256.toLowerCase()) {
        unlinkSync(destPath)
        return { success: false, error: 'SHA256-Prüfung fehlgeschlagen — Datei wurde gelöscht.' }
      }
    }

    shell.openPath(destPath)
    setTimeout(() => app.quit(), 1500)
    return { success: true }
  } catch (e: any) {
    if (existsSync(destPath)) unlinkSync(destPath)
    return { success: false, error: e.message }
  }
})
