// resources/js/filament-notification-sounds.js

class FilamentNotificationSounds {
    constructor() {
        this.enabled = this.getSoundPreference();
        this.volume = this.getVolumePreference();
        this.sounds = {};
        this.lastNotificationTime = 0;
        this.cooldownPeriod = 1000; // 1 second cooldown
        
        this.init();
    }

    init() {
        this.loadSounds();
        this.observeFilamentNotifications();
        this.addSoundControls();
    }

    // Load notification sounds
    loadSounds() {
        const soundFiles = {
            success: '/sounds/success.mp3',
            warning: '/sounds/warning.mp3',
            danger: '/sounds/error.mp3',
            info: '/sounds/info.mp3',
            default: '/sounds/notification.mp3'
        };

        // Create audio objects
        Object.keys(soundFiles).forEach(type => {
            const audio = new Audio();
            audio.preload = 'auto';
            audio.volume = this.volume;
            audio.src = soundFiles[type];
            
            // Handle loading errors silently
            audio.addEventListener('error', () => {
                console.warn(`Sound file not found: ${soundFiles[type]}`);
            });
            
            this.sounds[type] = audio;
        });

        // Create fallback beep sound
        this.createBeepSound();
    }

    // Create simple beep sound as fallback
    createBeepSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            
            this.beepSound = {
                play: (frequency = 800) => {
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    oscillator.frequency.setValueAtTime(frequency, audioContext.currentTime);
                    gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                    gainNode.gain.linearRampToValueAtTime(this.volume * 0.2, audioContext.currentTime + 0.01);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.2);
                }
            };
        } catch (error) {
            this.beepSound = { play: () => {} };
        }
    }

    // Watch for new Filament notifications
    observeFilamentNotifications() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        this.checkForNotifications(node);
                    }
                });
            });
        });

        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Check if added node is a Filament notification
    checkForNotifications(element) {
        // Filament notification selectors
        const notificationSelectors = [
            '.fi-no-notification',
            '[data-filament-notifications-container] > div',
            '.filament-notifications-container > div',
            '[role="alert"]'
        ];

        // Check if the element itself is a notification
        for (const selector of notificationSelectors) {
            if (element.matches && element.matches(selector)) {
                this.handleNotification(element);
                return;
            }
        }

        // Check for notifications within the element
        notificationSelectors.forEach(selector => {
            const notifications = element.querySelectorAll?.(selector) || [];
            notifications.forEach(notification => {
                this.handleNotification(notification);
            });
        });
    }

    // Handle detected notification
    handleNotification(notificationElement) {
        // Determine notification type from Filament classes
        let type = 'default';
        
        const classMap = {
            'fi-color-success': 'success',
            'fi-color-warning': 'warning', 
            'fi-color-danger': 'danger',
            'fi-color-info': 'info'
        };

        // Check notification element classes
        for (const [className, notificationType] of Object.entries(classMap)) {
            if (notificationElement.classList.contains(className) || 
                notificationElement.querySelector(`.${className}`)) {
                type = notificationType;
                break;
            }
        }

        // Also check for background colors (newer Filament versions)
        const computedStyle = window.getComputedStyle(notificationElement);
        const backgroundColor = computedStyle.backgroundColor;
        
        if (backgroundColor.includes('34, 197, 94') || backgroundColor.includes('green')) {
            type = 'success';
        } else if (backgroundColor.includes('245, 158, 11') || backgroundColor.includes('yellow')) {
            type = 'warning';
        } else if (backgroundColor.includes('239, 68, 68') || backgroundColor.includes('red')) {
            type = 'danger';
        } else if (backgroundColor.includes('59, 130, 246') || backgroundColor.includes('blue')) {
            type = 'info';
        }

        this.playSound(type);
    }

    // Play notification sound
    playSound(type = 'default') {
        if (!this.enabled) return;

        // Implement cooldown
        const now = Date.now();
        if (now - this.lastNotificationTime < this.cooldownPeriod) {
            return;
        }
        this.lastNotificationTime = now;

        // Try to play the sound file
        const sound = this.sounds[type] || this.sounds.default;
        
        if (sound && sound.readyState >= 2) {
            sound.currentTime = 0;
            sound.volume = this.volume;
            
            const playPromise = sound.play();
            if (playPromise !== undefined) {
                playPromise.catch(() => {
                    // Fallback to beep sound
                    this.playBeep(type);
                });
            }
        } else {
            // Fallback to beep sound
            this.playBeep(type);
        }
    }

    // Play beep sound with different frequencies for different types
    playBeep(type) {
        const frequencies = {
            success: 600,
            warning: 800,
            danger: 1000,
            info: 400,
            default: 600
        };

        this.beepSound.play(frequencies[type] || frequencies.default);
    }

    // Add sound controls to Filament interface
    addSoundControls() {
        setTimeout(() => {
            const navbar = document.querySelector('.fi-topbar') || 
                          document.querySelector('.fi-main-topbar') ||
                          document.querySelector('[data-slot="topbar"]') ||
                          document.querySelector('nav');
            
            if (navbar && !document.getElementById('sound-controls')) {
                const controls = this.createSoundControls();
                navbar.appendChild(controls);
            }
        }, 2000); // Wait for Filament to fully load
    }

    // Create sound control UI
    createSoundControls() {
        const container = document.createElement('div');
        container.id = 'sound-controls';
        container.className = 'flex items-center gap-2 ml-auto';
        
        container.innerHTML = `
            <div class="relative" x-data="{ open: false }">
                <button 
                    @click="open = !open"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    title="Sound Settings"
                >
                    <svg class="w-5 h-5 ${this.enabled ? 'text-green-600' : 'text-gray-400'}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                    </svg>
                </button>
                
                <div 
                    x-show="open" 
                    @click.away="open = false"
                    x-transition
                    class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 z-50"
                    style="display: none;"
                >
                    <h3 class="font-medium text-gray-900 dark:text-white mb-3">Notification Sounds</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Enable Sounds</span>
                            <input 
                                type="checkbox" 
                                id="sound-toggle"
                                ${this.enabled ? 'checked' : ''}
                                class="rounded border-gray-300 text-blue-600"
                            >
                        </label>
                        
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Volume</label>
                            <input 
                                type="range" 
                                id="volume-slider"
                                min="0" 
                                max="1" 
                                step="0.1" 
                                value="${this.volume}"
                                class="w-full"
                            >
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2">
                            <button 
                                onclick="window.filamentSounds.testSound('success')"
                                class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700"
                            >
                                Success
                            </button>
                            <button 
                                onclick="window.filamentSounds.testSound('warning')"
                                class="px-2 py-1 text-xs bg-yellow-600 text-white rounded hover:bg-yellow-700"
                            >
                                Warning
                            </button>
                            <button 
                                onclick="window.filamentSounds.testSound('danger')"
                                class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700"
                            >
                                Error
                            </button>
                            <button 
                                onclick="window.filamentSounds.testSound('info')"
                                class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
                            >
                                Info
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Attach event listeners after a brief delay
        setTimeout(() => {
            this.attachEventListeners(container);
        }, 100);

        return container;
    }

    // Attach event listeners to controls
    attachEventListeners(container) {
        const toggle = container.querySelector('#sound-toggle');
        const volumeSlider = container.querySelector('#volume-slider');

        if (toggle) {
            toggle.addEventListener('change', (e) => {
                this.enabled = e.target.checked;
                this.saveSoundPreference();
                this.updateControlIcon();
            });
        }

        if (volumeSlider) {
            volumeSlider.addEventListener('input', (e) => {
                this.volume = parseFloat(e.target.value);
                this.updateVolume();
                this.saveVolumePreference();
            });
        }
    }

    // Update control icon color
    updateControlIcon() {
        const icon = document.querySelector('#sound-controls svg');
        if (icon) {
            icon.className = icon.className.replace(/text-(green|gray)-\d+/, 
                this.enabled ? 'text-green-600' : 'text-gray-400');
        }
    }

    // Update volume for all sounds
    updateVolume() {
        Object.values(this.sounds).forEach(sound => {
            if (sound && sound.volume !== undefined) {
                sound.volume = this.volume;
            }
        });
    }

    // Test sound method
    testSound(type) {
        this.playSound(type);
    }

    // Preference management
    saveSoundPreference() {
        localStorage.setItem('filament-sound-enabled', this.enabled.toString());
    }

    saveVolumePreference() {
        localStorage.setItem('filament-sound-volume', this.volume.toString());
    }

    getSoundPreference() {
        const saved = localStorage.getItem('filament-sound-enabled');
        return saved !== null ? saved === 'true' : true;
    }

    getVolumePreference() {
        const saved = localStorage.getItem('filament-sound-volume');
        return saved !== null ? parseFloat(saved) : 0.7;
    }

    // Public methods
    enable() {
        this.enabled = true;
        this.saveSoundPreference();
        this.updateControlIcon();
    }

    disable() {
        this.enabled = false;
        this.saveSoundPreference();
        this.updateControlIcon();
    }

    setVolume(volume) {
        this.volume = Math.max(0, Math.min(1, volume));
        this.updateVolume();
        this.saveVolumePreference();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.filamentSounds = new FilamentNotificationSounds();
});

// Also initialize if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.filamentSounds = new FilamentNotificationSounds();
    });
} else {
    window.filamentSounds = new FilamentNotificationSounds();
}