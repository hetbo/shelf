import React, { useState, useEffect } from 'react';
import Navbar from './Navbar';
import Breadcrumbs from './Breadcrumbs';
import FolderTree from './FolderTree';
import FileList from './FileList';
import FileDetails from './FileDetails';
import StatusBar from './StatusBar';

const Shelf = () => {
    const [currentFolderId, setCurrentFolderId] = useState(null);
    const [selectedItems, setSelectedItems] = useState([]);
    const [folderContents, setFolderContents] = useState({ folders: [], files: [] });
    const [folderTree, setFolderTree] = useState([]);
    const [breadcrumbs, setBreadcrumbs] = useState([]);
    const [loading, setLoading] = useState(false);
    const [viewMode, setViewMode] = useState('grid'); // grid, list
    const [sortBy, setSortBy] = useState('name');
    const [sortOrder, setSortOrder] = useState('asc');

    // Load initial data
    useEffect(() => {
        loadFolderTree();
        loadFolderContents(null);
    }, []);

    const loadFolderTree = async () => {
        try {
            const response = await fetch('/api/shelf/folders/tree');
            const data = await response.json();
            setFolderTree(data);
        } catch (error) {
            console.error('Failed to load folder tree:', error);
        }
    };

    const loadFolderContents = async (folderId) => {
        setLoading(true);
        try {
            const url = folderId
                ? `/api/shelf/folders/${folderId}/contents`
                : '/api/shelf/folders/root';

            const response = await fetch(url);
            const data = await response.json();

            setFolderContents(data);
            setBreadcrumbs(data.breadcrumbs || []);
            setCurrentFolderId(folderId);
            setSelectedItems([]);
        } catch (error) {
            console.error('Failed to load folder contents:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleFolderSelect = (folderId) => {
        loadFolderContents(folderId);
    };

    const handleItemSelect = (item, isMultiple = false) => {
        if (isMultiple) {
            setSelectedItems(prev => {
                const exists = prev.find(i => i.id === item.id && i.type === item.type);
                if (exists) {
                    return prev.filter(i => !(i.id === item.id && i.type === item.type));
                }
                return [...prev, item];
            });
        } else {
            setSelectedItems([item]);
        }
    };

    const handleItemDoubleClick = (item) => {
        if (item.type === 'folder') {
            handleFolderSelect(item.id);
        }
    };

    const handleBreadcrumbClick = (folderId) => {
        loadFolderContents(folderId);
    };

    const handleRefresh = () => {
        loadFolderContents(currentFolderId);
        loadFolderTree();
    };

    const handleCreateFolder = async (name) => {
        try {
            const response = await fetch('/api/shelf/folders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, parent_id: currentFolderId })
            });

            if (response.ok) {
                handleRefresh();
            }
        } catch (error) {
            console.error('Failed to create folder:', error);
        }
    };

    const handleUpload = async (files) => {
        const formData = new FormData();
        Array.from(files).forEach(file => {
            formData.append('files[]', file);
        });
        formData.append('folder_id', currentFolderId || '');

        try {
            const response = await fetch('/api/shelf/files/upload', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                handleRefresh();
            }
        } catch (error) {
            console.error('Failed to upload files:', error);
        }
    };

    const handleDelete = async (items) => {
        try {
            const promises = items.map(item => {
                const endpoint = item.type === 'folder'
                    ? `/api/shelf/folders/${item.id}`
                    : `/api/shelf/files/${item.id}`;

                return fetch(endpoint, { method: 'DELETE' });
            });

            await Promise.all(promises);
            handleRefresh();
            setSelectedItems([]);
        } catch (error) {
            console.error('Failed to delete items:', error);
        }
    };

    return (
        <div className="shelf:h-screen shelf:flex shelf:flex-col shelf:bg-gray-50 shelf:font-mono">
            <Navbar
                onRefresh={handleRefresh}
                onCreateFolder={handleCreateFolder}
                onUpload={handleUpload}
                onDelete={() => handleDelete(selectedItems)}
                selectedItems={selectedItems}
                viewMode={viewMode}
                onViewModeChange={setViewMode}
                sortBy={sortBy}
                onSortByChange={setSortBy}
                sortOrder={sortOrder}
                onSortOrderChange={setSortOrder}
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

export default Shelf;