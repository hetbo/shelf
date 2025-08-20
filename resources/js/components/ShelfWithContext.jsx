import React, { useEffect } from 'react';
import { ShelfProvider, useShelf } from './ShelfContext';
import Navbar from './Navbar';
import Breadcrumbs from './Breadcrumbs';
import FolderTree from './FolderTree';
import FileList from './FileList';
import FileDetails from './FileDetails';
import StatusBar from './StatusBar';

const ShelfContent = () => {
    const {
        currentFolderId,
        selectedItems,
        folderContents,
        folderTree,
        breadcrumbs,
        loading,
        viewMode,
        sortBy,
        sortOrder,
        error,
        actions
    } = useShelf();

    // Load initial data
    useEffect(() => {
        const initializeShelf = async () => {
            try {
                await Promise.all([
                    actions.loadFolderTree(),
                    actions.loadFolderContents(null)
                ]);
            } catch (error) {
                console.error('Failed to initialize shelf:', error);
            }
        };

        initializeShelf();
    }, []);

    // Keyboard shortcuts
    useEffect(() => {
        const handleKeyDown = (event) => {
            // Only handle shortcuts when not typing in input fields
            if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
                return;
            }

            const ctrl = event.ctrlKey || event.metaKey;

            switch (event.key.toLowerCase()) {
                case 'f5':
                    event.preventDefault();
                    handleRefresh();
                    break;

                case 'delete':
                case 'backspace':
                    if (selectedItems.length > 0) {
                        event.preventDefault();
                        handleDelete(selectedItems);
                    }
                    break;

                case 'a':
                    if (ctrl) {
                        event.preventDefault();
                        const allItems = [
                            ...folderContents.folders.map(f => ({ ...f, type: 'folder' })),
                            ...folderContents.files.map(f => ({ ...f, type: 'file' }))
                        ];
                        actions.setSelectedItems(allItems);
                    }
                    break;

                case 'escape':
                    actions.clearSelectedItems();
                    break;

                default:
                    break;
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [selectedItems, folderContents]);

    const handleFolderSelect = async (folderId) => {
        try {
            await actions.loadFolderContents(folderId);
        } catch (error) {
            console.error('Failed to load folder:', error);
        }
    };

    const handleItemSelect = (item, isMultiple = false) => {
        if (isMultiple) {
            const isSelected = selectedItems.some(i =>
                i.id === item.id && i.type === item.type
            );

            if (isSelected) {
                actions.removeSelectedItem(item);
            } else {
                actions.addSelectedItem(item);
            }
        } else {
            actions.setSelectedItems([item]);
        }
    };

    const handleItemDoubleClick = (item) => {
        if (item.type === 'folder') {
            handleFolderSelect(item.id);
        } else {
            // Handle file double-click (could open file or download)
            actions.downloadFile(item);
        }
    };

    const handleBreadcrumbClick = (folderId) => {
        handleFolderSelect(folderId);
    };

    const handleRefresh = async () => {
        try {
            await Promise.all([
                actions.loadFolderContents(currentFolderId),
                actions.loadFolderTree()
            ]);
        } catch (error) {
            console.error('Failed to refresh:', error);
        }
    };

    const handleCreateFolder = async (name) => {
        try {
            await actions.createFolder(name);
        } catch (error) {
            console.error('Failed to create folder:', error);
        }
    };

    const handleUpload = async (files) => {
        try {
            await actions.uploadFiles(files);
        } catch (error) {
            console.error('Failed to upload files:', error);
        }
    };

    const handleDelete = async (items) => {
        try {
            await actions.deleteItems(items);
        } catch (error) {
            console.error('Failed to delete items:', error);
        }
    };

    return (
        <div className="shelf:h-screen shelf:flex shelf:flex-col shelf:bg-gray-50 shelf:font-mono">
            {/* Error display */}
            {error && (
                <div className="shelf:bg-red-600 shelf:text-white shelf:px-4 shelf:py-2 shelf:text-sm shelf:flex shelf:justify-between shelf:items-center">
                    <span>{error}</span>
                    <button
                        onClick={actions.clearError}
                        className="shelf:text-red-200 hover:shelf:text-white shelf:ml-4"
                    >
                        ×
                    </button>
                </div>
            )}

            <Navbar
                onRefresh={handleRefresh}
                onCreateFolder={handleCreateFolder}
                onUpload={handleUpload}
                onDelete={() => handleDelete(selectedItems)}
                selectedItems={selectedItems}
                viewMode={viewMode}
                onViewModeChange={actions.setViewMode}
                sortBy={sortBy}
                onSortByChange={actions.setSortBy}
                sortOrder={sortOrder}
                onSortOrderChange={actions.setSortOrder}
            />

            <Breadcrumbs
                breadcrumbs={breadcrumbs}
                onBreadcrumbClick={handleBreadcrumbClick}
            />

            <div className="shelf:flex shelf:flex-1 shelf:overflow-hidden">
                <div className="shelf:w-64 shelf:border-r shelf:border-gray-300 shelf:bg-white">
                    <FolderTree
                        folders={folderTree}
                        currentFolderId={currentFolderId}
                        onFolderSelect={handleFolderSelect}
                    />
                </div>

                <div className="shelf:flex-1 shelf:flex shelf:flex-col">
                    <FileList
                        folders={folderContents.folders}
                        files={folderContents.files}
                        selectedItems={selectedItems}
                        onItemSelect={handleItemSelect}
                        onItemDoubleClick={handleItemDoubleClick}
                        viewMode={viewMode}
                        sortBy={sortBy}
                        sortOrder={sortOrder}
                        loading={loading}
                    />
                </div>

                <div className="shelf:w-80 shelf:border-l shelf:border-gray-300 shelf:bg-white">
                    <FileDetails
                        selectedItems={selectedItems}
                        onRefresh={handleRefresh}
                    />
                </div>
            </div>

            <StatusBar
                totalItems={folderContents.folders.length + folderContents.files.length}
                selectedCount={selectedItems.length}
                currentPath={breadcrumbs.map(b => b.name).join('/')}
            />
        </div>
    );
};

// Main component with context provider
const Shelf = () => {
    return (
        <ShelfProvider>
            <ShelfContent />
        </ShelfProvider>
    );
};

export default Shelf;