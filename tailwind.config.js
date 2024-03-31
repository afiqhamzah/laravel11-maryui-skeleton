/** @type {import('tailwindcss').Config} */
export default {
    content: [
        // You will probably also need these lines
        "./resources/**/**/*.blade.php",
        "./resources/**/**/*.js",
        "./app/View/Components/**/**/*.php",
        "./app/Livewire/**/**/*.php",

        // Add mary
        "./vendor/robsontenorio/mary/src/View/Components/**/*.php",

        // Add laravel pagination
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {},
    },

    // Add daisyUI
    plugins: [require("daisyui")],

    // daisyUI config (optional - here are the default values)
    daisyui: {
        themes: ["light", "dark", "corporate", "emerald"], // false: only light + dark | true: all themes | array: specific themes like this ["light", "dark", "cupcake"]
        darkTheme: "corporate", // name of one of the included themes for dark mode
    },
}
