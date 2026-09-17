import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  build: {
    outDir: 'public/assets',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        hotui: './app/Views/hotui/css/app.css',
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name].[ext]',
      },
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './app'),
    },
  },
  css: {
    postcss: './postcss.config.js',
  },
});
