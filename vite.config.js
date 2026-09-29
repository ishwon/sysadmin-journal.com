import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { writeFileSync, rmSync, existsSync } from 'node:fs';

/**
 * Mirrors what laravel-vite-plugin did for us: emit a hashed manifest into
 * public/build for production, and drop a `public/hot` file (containing the
 * dev-server URL) while `vite` is running so the Rust server can point the
 * browser at the dev server instead of the built assets.
 */
function hotFile() {
    const path = 'public/hot';
    return {
        name: 'sysadmin-journal-hot-file',
        configureServer(server) {
            if (existsSync(path)) rmSync(path);
            server.httpServer?.once('listening', () => {
                const address = server.httpServer.address();
                const protocol = server.config.server.https ? 'https' : 'http';
                const host = typeof address === 'object' && address.address !== '::' && address.address !== '0.0.0.0'
                    ? address.address
                    : 'localhost';
                writeFileSync(path, `${protocol}://${host}:${address.port}`);
            });
            const clean = () => { if (existsSync(path)) rmSync(path); };
            process.on('exit', clean);
            process.on('SIGINT', () => process.exit());
            process.on('SIGTERM', () => process.exit());
        },
    };
}

export default defineConfig(({ command }) => ({
    plugins: [tailwindcss(), hotFile()],
    publicDir: false,
    base: command === 'serve' ? '/' : '/build/',
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: ['resources/css/app.css', 'resources/js/app.js'],
        },
    },
    server: {
        origin: 'http://localhost:5173',
        cors: true,
    },
}));
