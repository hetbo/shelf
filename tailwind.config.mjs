/** @type {import('tailwindcss').Config} */
export default {
    // Your chosen prefix. Every class in your Blade files must use this.
    prefix: 'shelf-',

    // THIS IS THE CRITICAL SECTION.
    // This tells Tailwind to look for any .blade.php or .js file
    // inside your package's `resources` folder and all its subdirectories.
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.jsx'
    ],

    theme: {
        extend: {},
    },
    plugins: [],
}