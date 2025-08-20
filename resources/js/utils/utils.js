// File size formatting utility
export const formatFileSize = (bytes) => {
    if (!bytes || bytes === 0) return '0 B';

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const k = 1024;
    const dm = 2;

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + units[i];
};

// Date formatting utility
export const formatDate = (dateString, options = {}) => {
    if (!dateString) return 'Unknown';

    const date = new Date(dateString);
    const defaultOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };

    return date.toLocaleString(undefined, { ...defaultOptions, ...options });
};

// Get file type description from MIME type
export const getFileTypeDescription = (mimeType) => {
    if (!mimeType) return 'Unknown';

    const typeMap = {
        // Images
        'image/jpeg': 'JPEG Image',
        'image/jpg': 'JPG Image',
        'image/png': 'PNG Image',
        'image/gif': 'GIF Image',
        'image/svg+xml': 'SVG Image',
        'image/webp': 'WebP Image',
        'image/bmp': 'BMP Image',
        'image/tiff': 'TIFF Image',

        // Videos
        'video/mp4': 'MP4 Video',
        'video/avi': 'AVI Video',
        'video/mov': 'MOV Video',
        'video/wmv': 'WMV Video',
        'video/flv': 'FLV Video',
        'video/webm': 'WebM Video',
        'video/mkv': 'MKV Video',

        // Audio
        'audio/mp3': 'MP3 Audio',
        'audio/mpeg': 'MPEG Audio',
        'audio/wav': 'WAV Audio',
        'audio/flac': 'FLAC Audio',
        'audio/aac': 'AAC Audio',
        'audio/ogg': 'OGG Audio',

        // Documents
        'application/pdf': 'PDF Document',
        'application/msword': 'Word Document',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'Word Document',
        'application/vnd.ms-excel': 'Excel Spreadsheet',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'Excel Spreadsheet',
        'application/vnd.ms-powerpoint': 'PowerPoint Presentation',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'PowerPoint Presentation',

        // Archives
        'application/zip': 'ZIP Archive',
        'application/rar': 'RAR Archive',
        'application/7z': '7Z Archive',
        'application/tar': 'TAR Archive',
        'application/gzip': 'GZIP Archive',

        // Text
        'text/plain': 'Text File',
        'text/html': 'HTML Document',
        'text/css': 'CSS Stylesheet',
        'text/javascript': 'JavaScript File',
        'application/javascript': 'JavaScript File',
        'application/json': 'JSON File',
        'application/xml': 'XML File',
        'text/xml': 'XML File',

        // Programming
        'text/x-php': 'PHP File',
        'text/x-python': 'Python File',
        'text/x-java': 'Java File',
        'text/x-c': 'C File',
        'text/x-cpp': 'C++ File',
        'text/x-csharp': 'C# File',
        'text/x-ruby': 'Ruby File',
        'text/x-go': 'Go File',
        'text/x-rust': 'Rust File',
    };

    return typeMap[mimeType] || mimeType.split('/')[1].toUpperCase() + ' File';
};

// Check if file is an image
export const isImage = (mimeType) => {
    return mimeType && mimeType.startsWith('image/');
};

// Check if file is a video
export const isVideo = (mimeType) => {
    return mimeType && mimeType.startsWith('video/');
};

// Check if file is audio
export const isAudio = (mimeType) => {
    return mimeType && mimeType.startsWith('audio/');
};

// Check if file is a PDF
export const isPdf = (mimeType) => {
    return mimeType === 'application/pdf';
};

// Get file icon name based on MIME type
export const getFileIconName = (mimeType) => {
    if (!mimeType) return 'DocumentIcon';

    if (isImage(mimeType)) return 'PhotoIcon';
    if (isVideo(mimeType)) return 'VideoCameraIcon';
    if (isAudio(mimeType)) return 'SpeakerWaveIcon';
    if (isPdf(mimeType)) return 'DocumentTextIcon';
    if (mimeType.includes('zip') || mimeType.includes('archive')) return 'ArchiveBoxIcon';
    if (mimeType.includes('text') || mimeType.includes('json') || mimeType.includes('xml')) return 'DocumentTextIcon';
    if (mimeType.includes('javascript') || mimeType.includes('css')) return 'CodeBracketIcon';

    return 'DocumentIcon';
};

// Get file icon color based on MIME type
export const getFileIconColor = (mimeType) => {
    if (!mimeType) return 'shelf:text-gray-500';

    if (isImage(mimeType)) return 'shelf:text-green-500';
    if (isVideo(mimeType)) return 'shelf:text-purple-500';
    if (isAudio(mimeType)) return 'shelf:text-blue-500';
    if (isPdf(mimeType)) return 'shelf:text-red-500';
    if (mimeType.includes('zip') || mimeType.includes('archive')) return 'shelf:text-orange-500';
    if (mimeType.includes('text') || mimeType.includes('json') || mimeType.includes('xml')) return 'shelf:text-gray-600';
    if (mimeType.includes('javascript') || mimeType.includes('css')) return 'shelf:text-yellow-600';

    return 'shelf:text-gray-500';
};

// Sort items utility
export const sortItems = (items, sortBy, sortOrder) => {
    return items.sort((a, b) => {
        // Always show folders first
        if (a.type !== b.type) {
            return a.type === 'folder' ? -1 : 1;
        }

        let aValue, bValue;

        switch (sortBy) {
            case 'size':
                aValue = a.size || 0;
                bValue = b.size || 0;
                break;
            case 'modified':
                aValue = new Date(a.updated_at || 0);
                bValue = new Date(b.updated_at || 0);
                break;
            case 'created':
                aValue = new Date(a.created_at || 0);
                bValue = new Date(b.created_at || 0);
                break;
            case 'type':
                aValue = a.mime_type || '';
                bValue = b.mime_type || '';
                break;
            case 'name':
            default:
                aValue = (a.name || a.filename || '').toLowerCase();
                bValue = (b.name || b.filename || '').toLowerCase();
                break;
        }

        let result = 0;
        if (aValue < bValue) result = -1;
        if (aValue > bValue) result = 1;

        return sortOrder === 'desc' ? -result : result;
    });
};

// Check if item is selected
export const isItemSelected = (item, selectedItems) => {
    return selectedItems.some(selected =>
        selected.id === item.id && selected.type === item.type
    );
};

// Generate breadcrumb path string
export const getBreadcrumbPath = (breadcrumbs) => {
    if (!breadcrumbs || breadcrumbs.length === 0) return '';
    return breadcrumbs.map(b => b.name).join('/');
};

// Validate file name
export const isValidFileName = (name) => {
    if (!name || name.trim().length === 0) return false;

    // Check for invalid characters (Windows + Unix)
    const invalidChars = /[<>:"/\\|?*\x00-\x1f]/;
    if (invalidChars.test(name)) return false;

    // Check for reserved names (Windows)
    const reservedNames = /^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])$/i;
    if (reservedNames.test(name.replace(/\..+$/, ''))) return false;

    // Check length (most filesystems support 255 chars)
    if (name.length > 255) return false;

    return true;
};

// Get file extension
export const getFileExtension = (filename) => {
    if (!filename) return '';
    const lastDot = filename.lastIndexOf('.');
    return lastDot === -1 ? '' : filename.substring(lastDot + 1).toLowerCase();
};

// Debounce function for search/filtering
export const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// Throttle function for scroll events
export const throttle = (func, limit) => {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};

// Handle keyboard shortcuts
export const handleKeyboardShortcut = (event, shortcuts) => {
    const key = event.key.toLowerCase();
    const ctrl = event.ctrlKey || event.metaKey;
    const shift = event.shiftKey;
    const alt = event.altKey;

    for (const shortcut of shortcuts) {
        if (
            shortcut.key === key &&
            shortcut.ctrl === ctrl &&
            shortcut.shift === shift &&
            shortcut.alt === alt
        ) {
            event.preventDefault();
            shortcut.handler();
            return true;
        }
    }

    return false;
};