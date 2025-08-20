import React, { createContext, useContext, useReducer, useCallback } from 'react';

// Initial state
const initialState = {
    currentFolderId: null,
    selectedItems: [],
    folderContents: { folders: [], files: [] },
    folderTree: [],
    breadcrumbs: [],
    loading: false,
    viewMode: 'grid',
    sortBy: 'name',
    sortOrder: 'asc',
    error: null
};

// Action types
const ActionTypes = {
    SET_LOADING: 'SET_LOADING',
    SET_CURRENT_FOLDER: 'SET_CURRENT_FOLDER',
    SET_FOLDER_CONTENTS: 'SET_FOLDER_CONTENTS',
    SET_FOLDER_TREE: 'SET_FOLDER_TREE',
    SET_BREADCRUMBS: 'SET_BREADCRUMBS',
    SET_SELECTED_ITEMS: 'SET_SELECTED_ITEMS',
    ADD_SELECTED_ITEM: 'ADD_SELECTED_ITEM',
    REMOVE_SELECTED_ITEM: 'REMOVE_SELECTED_ITEM',
    CLEAR_SELECTED_ITEMS: 'CLEAR_SELECTED_ITEMS',
    SET_VIEW_MODE: 'SET_VIEW_MODE',
    SET_SORT_BY: 'SET_SORT_BY',
    SET_SORT_ORDER: 'SET_SORT_ORDER',
    SET_ERROR: 'SET_ERROR',
    CLEAR_ERROR: 'CLEAR_ERROR'
};

// Reducer
const shelfReducer = (state, action) => {
    switch (action.type) {
        case ActionTypes.SET_LOADING:
            return { ...state, loading: action.payload };

        case ActionTypes.SET_CURRENT_FOLDER:
            return { ...state, currentFolderId: action.payload };

        case ActionTypes.SET_FOLDER_CONTENTS:
            return { ...state, folderContents: action.payload };

        case ActionTypes.SET_FOLDER_TREE:
            return { ...state, folderTree: action.payload };

        case ActionTypes.SET_BREADCRUMBS:
            return { ...state, breadcrumbs: action.payload };

        case ActionTypes.SET_SELECTED_ITEMS:
            return { ...state, selectedItems: action.payload };

        case ActionTypes.ADD_SELECTED_ITEM:
            return {
                ...state,
                selectedItems: [...state.selectedItems, action.payload]
            };

        case ActionTypes.REMOVE_SELECTED_ITEM:
            return {
                ...state,
                selectedItems: state.selectedItems.filter(item =>
                    !(item.id === action.payload.id && item.type === action.payload.type)
                )
            };

        case ActionTypes.CLEAR_SELECTED_ITEMS:
            return { ...state, selectedItems: [] };

        case ActionTypes.SET_VIEW_MODE:
            return { ...state, viewMode: action.payload };

        case ActionTypes.SET_SORT_BY:
            return { ...state, sortBy: action.payload };

        case ActionTypes.SET_SORT_ORDER:
            return { ...state, sortOrder: action.payload };

        case ActionTypes.SET_ERROR:
            return { ...state, error: action.payload };

        case ActionTypes.CLEAR_ERROR:
            return { ...state, error: null };

        default:
            return state;
    }
};

// Create context
const ShelfContext = createContext();

// Context provider component
export const ShelfProvider = ({ children }) => {
    const [state, dispatch] = useReducer(shelfReducer, initialState);

    // API helpers
    const apiCall = useCallback(async (url, options = {}) => {
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            dispatch({ type: ActionTypes.SET_ERROR, payload: error.message });
            throw error;
        }
    }, []);

    // Actions
    const actions = {
        // Loading
        setLoading: (loading) => dispatch({ type: ActionTypes.SET_LOADING, payload: loading }),

        // Folder navigation
        setCurrentFolder: (folderId) => dispatch({ type: ActionTypes.SET_CURRENT_FOLDER, payload: folderId }),

        // Data management
        setFolderContents: (contents) => dispatch({ type: ActionTypes.SET_FOLDER_CONTENTS, payload: contents }),
        setFolderTree: (tree) => dispatch({ type: ActionTypes.SET_FOLDER_TREE, payload: tree }),
        setBreadcrumbs: (breadcrumbs) => dispatch({ type: ActionTypes.SET_BREADCRUMBS, payload: breadcrumbs }),

        // Selection management
        setSelectedItems: (items) => dispatch({ type: ActionTypes.SET_SELECTED_ITEMS, payload: items }),
        addSelectedItem: (item) => dispatch({ type: ActionTypes.ADD_SELECTED_ITEM, payload: item }),
        removeSelectedItem: (item) => dispatch({ type: ActionTypes.REMOVE_SELECTED_ITEM, payload: item }),
        clearSelectedItems: () => dispatch({ type: ActionTypes.CLEAR_SELECTED_ITEMS }),

        // View management
        setViewMode: (mode) => dispatch({ type: ActionTypes.SET_VIEW_MODE, payload: mode }),
        setSortBy: (sortBy) => dispatch({ type: ActionTypes.SET_SORT_BY, payload: sortBy }),
        setSortOrder: (order) => dispatch({ type: ActionTypes.SET_SORT_ORDER, payload: order }),

        // Error management
        setError: (error) => dispatch({ type: ActionTypes.SET_ERROR, payload: error }),
        clearError: () => dispatch({ type: ActionTypes.CLEAR_ERROR }),

        // API actions
        loadFolderTree: async () => {
            try {
                const data = await apiCall('/api/shelf/folders/tree');
                actions.setFolderTree(data);
                return data;
            } catch (error) {
                console.error('Failed to load folder tree:', error);
                throw error;
            }
        },

        loadFolderContents: async (folderId) => {
            actions.setLoading(true);
            try {
                const url = folderId
                    ? `/api/shelf/folders/${folderId}/contents`
                    : '/api/shelf/folders/root';

                const data = await apiCall(url);

                actions.setFolderContents(data);
                actions.setBreadcrumbs(data.breadcrumbs || []);
                actions.setCurrentFolder(folderId);
                actions.clearSelectedItems();

                return data;
            } catch (error) {
                console.error('Failed to load folder contents:', error);
                throw error;
            } finally {
                actions.setLoading(false);
            }
        },

        createFolder: async (name, parentId = null) => {
            try {
                await apiCall('/api/shelf/folders', {
                    method: 'POST',
                    body: JSON.stringify({
                        name,
                        parent_id: parentId || state.currentFolderId
                    })
                });

                // Refresh current view and folder tree
                await actions.loadFolderContents(state.currentFolderId);
                await actions.loadFolderTree();
            } catch (error) {
                console.error('Failed to create folder:', error);
                throw error;
            }
        },

        uploadFiles: async (files, folderId = null) => {
            try {
                const formData = new FormData();
                Array.from(files).forEach(file => {
                    formData.append('files[]', file);
                });
                formData.append('folder_id', folderId || state.currentFolderId || '');

                await fetch('/api/shelf/files/upload', {
                    method: 'POST',
                    body: formData
                });

                // Refresh current view
                await actions.loadFolderContents(state.currentFolderId);
            } catch (error) {
                console.error('Failed to upload files:', error);
                throw error;
            }
        },

        deleteItems: async (items) => {
            try {
                const promises = items.map(item => {
                    const endpoint = item.type === 'folder'
                        ? `/api/shelf/folders/${item.id}`
                        : `/api/shelf/files/${item.id}`;

                    return apiCall(endpoint, { method: 'DELETE' });
                });

                await Promise.all(promises);

                // Refresh current view and folder tree
                await actions.loadFolderContents(state.currentFolderId);
                await actions.loadFolderTree();
                actions.clearSelectedItems();
            } catch (error) {
                console.error('Failed to delete items:', error);
                throw error;
            }
        },

        renameItem: async (item, newName) => {
            try {
                const endpoint = item.type === 'folder'
                    ? `/api/shelf/folders/${item.id}`
                    : `/api/shelf/files/${item.id}`;

                const payload = item.type === 'folder'
                    ? { name: newName }
                    : { filename: newName };

                await apiCall(endpoint, {
                    method: 'PUT',
                    body: JSON.stringify(payload)
                });

                // Refresh current view and folder tree
                await actions.loadFolderContents(state.currentFolderId);
                await actions.loadFolderTree();
            } catch (error) {
                console.error('Failed to rename item:', error);
                throw error;
            }
        },

        downloadFile: async (file) => {
            try {
                const response = await fetch(`/api/shelf/files/${file.id}/download`);
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = file.filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                }
            } catch (error) {
                console.error('Failed to download file:', error);
                throw error;
            }
        }
    };

    const value = {
        ...state,
        actions
    };

    return (
        <ShelfContext.Provider value={value}>
            {children}
        </ShelfContext.Provider>
    );
};

// Custom hook to use the context
export const useShelf = () => {
    const context = useContext(ShelfContext);
    if (!context) {
        throw new Error('useShelf must be used within a ShelfProvider');
    }
    return context;
};

export default ShelfContext;