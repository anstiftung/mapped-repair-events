module.exports = {
    languageOptions: {
        ecmaVersion: 2020,
        globals: {
            Chart: true,
            ChartDataLabels: true,
            ClipboardJS: true,
            Jodit: true,
            MappedRepairEvents: true,
            PruneCluster: true,
            PruneClusterForLeaflet: true,
            slidebars: true,
            Swiper: true,
            L: true
        }
    },
    rules: {
        indent: [
            'error',
            4
        ],
        'no-unused-vars': ['off'],
        'no-console': ['off'],
        'linebreak-style': [
            'error',
            'unix'
        ],
        quotes: [
            'error',
            'single'
        ],
        semi: [
            'error',
            'always'
        ]
    }
};