import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    // Proxy API requests to the XAMPP backend
    // Assumes XAMPP serves the PHP project at http://localhost/FinalWeb(HTML)/
    // You might need to adjust 'target' depending on your XAMPP folder name
    proxy: {
      '/api': {
        target: 'http://localhost/FinalWeb(HTML)/api',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, ''),
      },
      '/uploads': {
        target: 'http://localhost/FinalWeb(HTML)/uploads',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/uploads/, ''),
      },
      '/admin-api': { // If we need admin specific calls
        target: 'http://localhost/FinalWeb(HTML)/admin',
        changeOrigin: true,
      }
    }
  }
})
