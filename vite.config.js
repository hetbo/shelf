import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';
import svgr from 'vite-plugin-svgr';


export default defineConfig({
    plugins: [react(), svgr()],
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