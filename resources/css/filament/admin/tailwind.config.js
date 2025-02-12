import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './vendor/filament/**/*.blade.php',
        './vendor/diogogpinto/filament-auth-ui-enhancer/resources/**/*.blade.php',
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/guava/filament-modal-relation-managers/resources/**/*.blade.php',
        './vendor/cmsmaxinc/filament-error-pages/resources/**/*.blade.php',
        './vendor/kenepa/banner/resources/**/*.php',
    ],
}
