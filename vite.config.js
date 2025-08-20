import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
    plugins: [react()],
    define: {
        'process.env.NODE_ENV': JSON.stringify('production'),
    },
    build: {
        lib: {
            entry: resolve(__dirname, 'resources/js/index.jsx'),
            name: 'shelf',
            fileName: 'shelf',
            formats: ['umd']
        },
        rollupOptions: {
            output: {
            }
        },
        outDir: 'dist'
    }
});