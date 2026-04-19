import { ipcMain, BrowserWindow } from 'electron'

export function registerOAuthHandlers() {
  let oauthWindow: BrowserWindow | null = null

  ipcMain.handle('oauth:open-browser', async (_event, url: string) => {
    try {
      const parsed = new URL(url)
      if (parsed.protocol !== 'https:' && parsed.protocol !== 'http:') return
    } catch { return }

    if (oauthWindow && !oauthWindow.isDestroyed()) {
      oauthWindow.close()
    }

    oauthWindow = new BrowserWindow({
      width: 520,
      height: 700,
      title: 'MovieShelf Login',
      autoHideMenuBar: true,
      webPreferences: {
        nodeIntegration: false,
        contextIsolation: true,
      },
    })

    oauthWindow.loadURL(url)

    // HTTP-302-Redirect vom Server auf movieshelf:// abfangen
    oauthWindow.webContents.on('will-redirect', (_e, redirectUrl) => {
      if (!redirectUrl.startsWith('movieshelf://oauth/callback')) return
      try {
        const cb      = new URL(redirectUrl)
        const code    = cb.searchParams.get('code')
        const state   = cb.searchParams.get('state')
        const mainWin = BrowserWindow.getAllWindows().find(w => w !== oauthWindow)
        if (code && mainWin) {
          mainWin.webContents.send('oauth:callback', { code, state })
        }
      } catch { /* ignore */ }
      oauthWindow?.close()
      oauthWindow = null
    })

    oauthWindow.on('closed', () => { oauthWindow = null })
  })
}
