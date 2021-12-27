import commonjs from 'rollup-plugin-commonjs'; // Convert CommonJS modules to ES6
import buble from 'rollup-plugin-buble'; // Transpile/polyfill with reasonable browser support
export default {
    input: 'src/main.js', // Path relative to package.json
    output: {
        name: 'Idaas',
        exports: 'named',
    },
    plugins: [
        commonjs(),
        buble(), // Transpile to ES5
    ],
};
