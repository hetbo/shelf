// Shelf configuration
export const SHELF_CONFIG = {
    // API endpoints
    endpoints: {
        folders: {
            tree: '/api/shelf/folders/tree',
            root: '/api/shelf/folders/root',
            contents: (id) => `/api/shelf/folders/${id}/contents`,
            create: '/api/shelf/folders',
            update: (id) => `/api/shelf/folders/${id}`,
            delete: (id) => `/api/shelf/folders/${id}`,
        },
        files: {
            upload: '/api/shelf/files/upload',
            download: (id) => `/api/shelf/files/${id}/download`,
            thumbnail: (id) => `/api/shelf/files/${id}/thumbnail`,
            update: (id) => `/api/shelf/files/${id}`,
            delete: (id) => `/api/shelf/files/${id}`,
        }
    },

    // View settings
    view: {
        defaultMode: 'grid', // 'grid' or 'list'
        defaultSort: 'name', // 'name', 'size', 'modified', 'type'
        defaultSortOrder: 'asc', // 'asc' or 'desc'
        gridColumns: 6, // Number of columns in grid view
        thumbnailSize: 64, // Thumbnail size in pixels
    },

    // File settings
    files: {
        maxUploadSize: 100 * 1024 * 1024, // 100MB in bytes
        allowedTypes: [
            // Images
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml',
            'image/webp', 'image/bmp', 'image/tiff',

            // Videos
            'video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv',
            'video/webm', 'video/mkv',

            // Audio
            'audio/mp3', 'audio/mpeg', 'audio/wav', 'audio/flac', 'audio/aac',
            'audio/ogg',

            // Documents
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',

            // Archives
            'application/zip', 'application/rar', 'application/7z',
            'application/tar', 'application/gzip',

            // Text
            'text/plain', 'text/html', 'text/css', 'text/javascript',
            'application/javascript', 'application/json', 'application/xml',
            'text/xml',
        ],
        previewTypes: [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
            'image/svg+xml', 'image/webp'
        ],
    },

    // UI settings
    ui: {
        animations: true,
        confirmDelete: true,
        showHiddenFiles: false,
        autoRefresh: false,
        autoRefreshInterval: 30000, // 30 seconds
        keyboardShortcuts: true,
        contextMenu: true,
    },

    // Validation settings
    validation: {
        maxNameLength: 255,
        invalidFileNameChars: /[<>:"/\\|?*\x00-\x1f]/,
        reservedNames: /^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])$/i,
    },

    // Error messages
    messages: {
        errors: {
            networkError: 'Network error occurred. Please check your connection.',
            unauthorizedAccess: 'You do not have permission to perform this action.',
            fileNotFound: 'The requested file or folder was not found.',
            invalidFileName: 'Invalid file name. Please use a different name.',
            fileTooLarge: 'File is too large. Maximum size allowed is {maxSize}.',
            unsupportedFileType: 'This file type is not supported.',
            uploadFailed: 'Failed to upload files. Please try again.',
            deleteFailed: 'Failed to delete items. Please try again.',
            renameFailed: 'Failed to rename item. Please try again.',
            createFolderFailed: 'Failed to create folder. Please try again.',
        },
        success: {
            filesUploaded: 'Files uploaded successfully.',
            itemsDeleted: 'Items deleted successfully.',
            itemRenamed: 'Item renamed successfully.',
            folderCreated: 'Folder created successfully.',
        },
        confirmations: {
            deleteItems: 'Are you sure you want to delete {count} item(s)?',
            overwriteFile: 'A file with this name already exists. Do you want to overwrite it?',
        }
    },

    // Keyboard shortcuts
    shortcuts: [
        { key: 'f5', ctrl: false, shift: false, alt: false, action: 'refresh', description: 'Refresh' },
        { key: 'delete', ctrl: false, shift: false, alt: false, action: 'delete', description: 'Delete selected items' },
        { key: 'backspace', ctrl: false, shift: false, alt: false, action: 'delete', description: 'Delete selected items' },
        { key: 'a', ctrl: true, shift: false, alt: false, action: 'selectAll', description: 'Select all items' },
        { key: 'escape', ctrl: false, shift: false, alt: false, action: 'clearSelection', description: 'Clear selection' },
        { key: 'f2', ctrl: false, shift: false, alt: false, action: 'rename', description: 'Rename selected item' },
        { key: 'enter', ctrl: false, shift: false, alt: false, action: 'open', description: 'Open selected item' },
        { key: 'n', ctrl: true, shift: false, alt: false, action: 'newFolder', description: 'Create new folder' },
        { key: 'u', ctrl: true, shift: false, alt: false, action: 'upload', description: 'Upload files' },
    ]
};

// Helper function to get config values with defaults
export const getConfig = (path, defaultValue = null) => {
    const pathArray = path.split('.');
    let current = SHELF_CONFIG;

    for (const key of pathArray) {
        if (current && typeof current === 'object' && key in current) {
            current = current[key];
        } else {
            return defaultValue;
        }
    }

    return current;
};

// Helper function to check if file type is allowed
export const isFileTypeAllowed = (mimeType) => {
    const allowedTypes = getConfig('files.allowedTypes', []);
    return allowedTypes.includes(mimeType);
};

// Helper function to check if file size is allowed
export const isFileSizeAllowed = (size) => {
    const maxSize = getConfig('files.maxUploadSize', 0);
    return size <= maxSize;
};

// Helper function to validate file name
export const validateFileName = (name) => {
    if (!name || name.trim().length === 0) {
        return { valid: false, message: 'File name cannot be empty.' };
    }

    const maxLength = getConfig('validation.maxNameLength', 255);
    if (name.length > maxLength) {
        return { valid: false, message: `File name cannot exceed ${maxLength} characters.` };
    }

    const invalidChars = getConfig('validation.invalidFileNameChars');
    if (invalidChars && invalidChars.test(name)) {
        return { valid: false, message: 'File name contains invalid characters.' };
    }

    const reservedNames = getConfig('validation.reservedNames');
    if (reservedNames && reservedNames.test(name.replace(/\..+$/, ''))) {
        return { valid: false, message: 'File name is reserved and cannot be used.' };
    }

    return { valid: true };
};

export default SHELF_CONFIG;