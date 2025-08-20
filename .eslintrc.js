module.exports = {
    root: true,               // Stops ESLint from looking in parent folders
    env: {
        browser: true,
        es2021: true,
        node: true,
    },
    parser: '@babel/eslint-parser',
    parserOptions: {
        requireConfigFile: false,  // Allow babel-eslint without .babelrc
        ecmaVersion: 13,
        sourceType: 'module',
        ecmaFeatures: {
            jsx: true,
        },
    },
    extends: [
        'eslint:recommended',
        'plugin:react/recommended',
        'plugin:react-hooks/recommended',
    ],
    plugins: ['react', 'react-hooks'],
    rules: {
        'react/prop-types': 'off',  // optional: if you don't use PropTypes
        'no-unused-vars': 'warn',
        'semi': ['error', 'always'],
        'quotes': ['error', 'single'],
    },
    settings: {
        react: {
            version: 'detect',
        },
    },
};
