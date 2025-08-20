import React, { useState } from 'react';
import Icon from '../icons/Icon.jsx';

const Navbar = ({
                    onRefresh,
                    onCreateFolder,
                    onUpload,
                    onDelete,
                    selectedItems,
                    viewMode,
                    onViewModeChange,
                    sortBy,
                    onSortByChange,
                    sortOrder,
                    onSortOrderChange
                }) => {
    const [showCreateFolder, setShowCreateFolder] = useState(false);
    const [folderName, setFolderName] = useState('');

    const handleCreateFolderSubmit = () => {
        if (folderName.trim()) {
            onCreateFolder(folderName.trim());
            setFolderName('');
            setShowCreateFolder(false);
        }
    };

    const handleFileUpload = (e) => {
        const files = e.target.files;
        if (files && files.length > 0) {
            onUpload(files);
        }
        e.target.value = ''; // Reset input
    };

    return (
        <div className="shelf:bg-gray-800 shelf:text-white shelf:px-4 shelf:py-2 shelf:flex shelf:items-center shelf:justify-between shelf:border-b shelf:border-gray-700">
            <div className="shelf:flex shelf:items-center shelf:space-x-3">
                <div className="shelf:flex shelf:items-center shelf:space-x-2">
                    <button
                        onClick={onRefresh}
                        className="shelf:p-2 shelf:rounded shelf:hover:bg-gray-700 shelf:transition-colors"
                        title="Refresh"
                    >
                        <Icon name="RefreshIcon" className="shelf:w-4 shelf:h-4" />
                    </button>

                    <div className="shelf:w-px shelf:h-6 shelf:bg-gray-600" />

                    <button
                        onClick={() => setShowCreateFolder(true)}
                        className="shelf:p-2 shelf:rounded shelf:hover:bg-gray-700 shelf:transition-colors"
                        title="New Folder"
                    >
                        <Icon name="FolderPlusIcon" className="shelf:w-4 shelf:h-4" />
                    </button>

                    <label className="shelf:p-2 shelf:rounded shelf:hover:bg-gray-700 shelf:transition-colors shelf:cursor-pointer">
                        <input
                            type="file"
                            multiple
                            onChange={handleFileUpload}
                            className="shelf:hidden"
                        />
                        <Icon name="UploadIcon" className="shelf:w-4 shelf:h-4" />
                    </label>

                    {selectedItems.length > 0 && (
                        <>
                            <div className="shelf:w-px shelf:h-6 shelf:bg-gray-600" />
                            <button
                                onClick={onDelete}
                                className="shelf:p-2 shelf:rounded shelf:hover:bg-red-600 shelf:transition-colors shelf:text-red-400 hover:shelf:text-white"
                                title="Delete Selected"
                            >
                                <Icon name="TrashIcon" className="shelf:w-4 shelf:h-4" />
                            </button>
                        </>
                    )}
                </div>
            </div>

            <div className="shelf:flex shelf:items-center shelf:space-x-3">
                <div className="shelf:flex shelf:items-center shelf:space-x-2">
                    <span className="shelf:text-xs shelf:text-gray-400">Sort:</span>
                    <select
                        value={sortBy}
                        onChange={(e) => onSortByChange(e.target.value)}
                        className="shelf:bg-gray-700 shelf:text-white shelf:text-xs shelf:px-2 shelf:py-1 shelf:rounded shelf:border shelf:border-gray-600"
                    >
                        <option value="name">Name</option>
                        <option value="size">Size</option>
                        <option value="modified">Modified</option>
                        <option value="type">Type</option>
                    </select>

                    <button
                        onClick={() => onSortOrderChange(sortOrder === 'asc' ? 'desc' : 'asc')}
                        className="shelf:p-1 shelf:rounded shelf:hover:bg-gray-700 shelf:transition-colors"
                        title={`Sort ${sortOrder === 'asc' ? 'Descending' : 'Ascending'}`}
                    >
                        <Icon
                            name={sortOrder === 'asc' ? 'ArrowUpIcon' : 'ArrowDownIcon'}
                            className="shelf:w-3 shelf:h-3"
                        />
                    </button>
                </div>

                <div className="shelf:w-px shelf:h-6 shelf:bg-gray-600" />

                <div className="shelf:flex shelf:items-center shelf:space-x-1">
                    <button
                        onClick={() => onViewModeChange('grid')}
                        className={`shelf:p-2 shelf:rounded shelf:transition-colors ${
                            viewMode === 'grid'
                                ? 'shelf:bg-blue-600 shelf:text-white'
                                : 'shelf:hover:bg-gray-700'
                        }`}
                        title="Grid View"
                    >
                        <Icon name="GridIcon" className="shelf:w-4 shelf:h-4" />
                    </button>

                    <button
                        onClick={() => onViewModeChange('list')}
                        className={`shelf:p-2 shelf:rounded shelf:transition-colors ${
                            viewMode === 'list'
                                ? 'shelf:bg-blue-600 shelf:text-white'
                                : 'shelf:hover:bg-gray-700'
                        }`}
                        title="List View"
                    >
                        <Icon name="ListIcon" className="shelf:w-4 shelf:h-4" />
                    </button>
                </div>
            </div>

            {/* Create Folder Modal */}
            {showCreateFolder && (
                <div className="shelf:fixed shelf:inset-0 shelf:bg-black shelf:bg-opacity-50 shelf:flex shelf:items-center shelf:justify-center shelf:z-50">
                    <div className="shelf:bg-white shelf:rounded shelf:p-6 shelf:w-96 shelf:text-gray-900">
                        <h3 className="shelf:text-lg shelf:font-medium shelf:mb-4">Create New Folder</h3>
                        <div>
                            <input
                                type="text"
                                value={folderName}
                                onChange={(e) => setFolderName(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        handleCreateFolderSubmit();
                                    } else if (e.key === 'Escape') {
                                        setShowCreateFolder(false);
                                        setFolderName('');
                                    }
                                }}
                                placeholder="Folder name"
                                className="shelf:w-full shelf:px-3 shelf:py-2 shelf:border shelf:border-gray-300 shelf:rounded shelf:focus:outline-none shelf:focus:ring-2 shelf:focus:ring-blue-500"
                                autoFocus
                            />
                            <div className="shelf:flex shelf:justify-end shelf:space-x-2 shelf:mt-4">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setShowCreateFolder(false);
                                        setFolderName('');
                                    }}
                                    className="shelf:px-4 shelf:py-2 shelf:text-gray-600 shelf:hover:text-gray-800 shelf:transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    onClick={handleCreateFolderSubmit}
                                    disabled={!folderName.trim()}
                                    className="shelf:px-4 shelf:py-2 shelf:bg-blue-600 shelf:text-white shelf:rounded shelf:hover:bg-blue-700 shelf:disabled:opacity-50 shelf:disabled:cursor-not-allowed shelf:transition-colors"
                                >
                                    Create
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Navbar;