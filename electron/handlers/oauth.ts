import { ipcMain, shell } from 'electron'

export function registerOAuthHandlers() {
  // Öffnet die Authorize-URL im System-Browser
  ipcMain.handle('oauth:open-browser', async (_event, url: string) => {
    try {
      const parsed = new URL(url)
      if (parsed.protocol !== 'https:' && parsed.protocol !== 'http:') return
      await shell.openExternal(url)
    } catch { /* ignore */ }
  })
}
