import React, { useState } from 'react';
import Icon from '../icons/Icon.jsx';

const FileDetails = ({ selectedItems, onRefresh }) => {
    const [showRenameModal, setShowRenameModal] = useState(false);
    const [newName, setNewName] = useState('');
    const [renamingItem, setRenamingItem] = useState(null);

    const formatSize = (bytes) => {
        if (!bytes) return 'Unknown';
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        let size = bytes;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }

        return `${size.toFixed(2)} ${units[unitIndex]}`;
    };

    const formatDate = (dateString) => {
        if (!dateString) return 'Unknown';
        return new Date(dateString).toLocaleString();
    };

    const getFileTypeDescription = (mimeType) => {
        if (!mimeType) return 'Unknown';

        const typeMap = {
            'image/jpeg': 'JPEG Image',
            'image/png': 'PNG Image',
            'image/gif': 'GIF Image',
            'image/svg+xml': 'SVG Image',
            'video/mp4': 'MP4 Video',
            'video/avi': 'AVI Video',
            'audio/mp3': 'MP3 Audio',
            'audio/wav': 'WAV Audio',
            'application/pdf': 'PDF Document',
            'application/zip': 'ZIP Archive',
            'text/plain': 'Text File',
            'text/html': 'HTML Document',
            'text/css': 'CSS Stylesheet',
            'application/javascript': 'JavaScript File',
        };

        return typeMap[mimeType] || mimeType;
    };

    const handleRename = (item) => {
        setRenamingItem(item);
        setNewName(item.type === 'folder' ? item.name : item.filename);
        setShowRenameModal(true);
    };

    const handleRenameSubmit = async () => {
        if (!renamingItem || !newName.trim()) return;

        try {
            const endpoint = renamingItem.type === 'folder'
                ? `/api/shelf/folders/${renamingItem.id}`
                : `/api/shelf/files/${renamingItem.id}`;

            const payload = renamingItem.type === 'folder'
                ? { name: newName.trim() }
                : { filename: newName.trim() };

            const response = await fetch(endpoint, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                onRefresh();
                setShowRenameModal(false);
                setRenamingItem(null);
                setNewName('');
            }
        } catch (error) {
            console.error('Failed to rename item:', error);
        }
    };

    const handleDownload = async (file) => {
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
        }
    };

    if (selectedItems.length === 0) {
        return (
            <div className="shelf:h-full shelf:flex shelf:flex-col shelf:bg-gray-50">
                <div className="shelf:p-4 shelf:border-b shelf:border-gray-200">
                    <h3 className="shelf:text-sm shelf:font-medium shelf:text-gray-900">Details</h3>
                </div>
                <div className="shelf:flex-1 shelf:flex shelf:items-center shelf:justify-center">
                    <div className="shelf:text-center shelf:text-gray-500">
                        <Icon name="InformationCircleIcon" className="shelf:w-12 shelf:h-12 shelf:mx-auto shelf:mb-2 shelf:text-gray-300" />
                        <p className="shelf:text-sm">Select an item to view details</p>
                    </div>
                </div>
            </div>
        );
    }

    if (selectedItems.length > 1) {
        const totalSize = selectedItems
            .filter(item => item.type === 'file' && item.size)
            .reduce((total, file) => total + file.size, 0);

        const fileCount = selectedItems.filter(item => item.type === 'file').length;
        const folderCount = selectedItems.filter(item => item.type === 'folder').length;

        return (
            <div className="shelf:h-full shelf:flex shelf:flex-col shelf:bg-white">
                <div className="shelf:p-4 shelf:border-b shelf:border-gray-200">
                    <h3 className="shelf:text-sm shelf:font-medium shelf:text-gray-900">Multiple Items Selected</h3>
                </div>

                <div className="shelf:flex-1 shelf:p-4">
                    <div className="shelf:space-y-4">
                        <div>
                            <label className="shelf:text-xs shelf:font-medium shelf:text-gray-500 shelf:uppercase shelf:tracking-wider">
                                Selection
                            </label>
                            <div className="shelf:mt-1 shelf:text-sm shelf:text-gray-900">
                                {folderCount > 0 && `${folderCount} folder${folderCount > 1 ? 's' : ''}`}
                                {folderCount > 0 && fileCount > 0 && ', '}
                                {fileCount > 0 && `${fileCount} file${fileCount > 1 ? 's' : ''}`}
                            </div>
                        </div>

                        {totalSize > 0 && (
                            <div>
                                <label className="shelf:text-xs shelf:font-medium shelf:text-gray-500 shelf:uppercase shelf:tracking-wider">
                                    Total Size
                                </label>
                                <div className="shelf:mt-1 shelf:text-sm shelf:text-gray-900">
                                    {formatSize(totalSize)}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        );
    }

    const item = selectedItems[0];

    return (
        <div className="shelf:h-full shelf:flex shelf:flex-col shelf:bg-white">
            <div className="shelf:p-4 shelf:border-b shelf:border-gray-200">
                <h3 className="shelf:text-sm shelf:font-medium shelf:text-gray-900">Details</h3>
            </div>

            <div className="shelf:flex-1 shelf:p-4 shelf:overflow-y-auto">
                <div className="shelf:space-y-6">
                    {/* Preview */}
                    <div className="shelf:text-center">
                        <div className="shelf:w-16 shelf:h-16 shelf:mx-auto shelf:mb-3 shelf:flex shelf:items-center shelf:justify-center shelf:bg-gray-100 shelf:rounded-lg">
                            {item.type === 'folder' ? (
                                <Icon name="FolderIcon" className="shelf:w-8 shelf:h-8 shelf:text-blue-500" />
                            ) : item.mime_type?.startsWith('image/') ? (
                                <img
                                    src={`/api/shelf/files/${item.id}/thumbnail`}
                                    alt={item.filename}
                                    className="shelf:w-full shelf:h-full shelf:object-cover shelf:rounded-lg"
                                    onError={(e) => {
                                        e.target.style.display = 'none';
                                        e.target.nextSibling.style.display = 'flex';
                                    }}
                                />
                            ) : (
                                <Icon name="DocumentIcon" className="shelf:w-8 shelf:h-8 shelf:text-gray-500" />
                            )}
                            <div className="shelf:w-full shelf:h-full shelf:items-center shelf:justify-center shelf:hidden">
                                <Icon name="DocumentIcon" className="shelf:w-8 shelf:h-8 shelf:text-gray-500" />
                            </div>
                        </div>

                        <div className="shelf:text-sm shelf:font-medium shelf:text-gray-900 shelf:break-words">
                            {item.type === 'folder' ? item.name : item.filename}
                        </div>
                    </div>

                    {/* Properties */}
                    <div className="shelf:space-y-4">
                        <div>
                            <label className="shelf:text-xs shelf:font-medium shelf:text-gray-500 shelf:uppercase shelf:tracking-wider">
                                Type
                            </label>
                            <div className="shelf:mt-1 shelf:text-sm shelf:text-gray-900">
                                {item.type === 'folder' ? 'Folder' : getFileTypeDescription(item.mime_type)}
                            </div>
                        </div>

                        {item.type === 'file' && (
                            <div>
                                <label className="shelf:text-xs shelf:font-medium shelf:text-gray-500 shelf:uppercase shelf:tracking-wider">
                                    Size
                                </label>
                                <div className="shelf:mt-1 shelf:text-sm shelf:text-gray-900">
                                    {formatSize(item.size)}
                                </div>
                            </div>
                        )}

                        <div>
                            <label className="shelf:text-xs shelf:font-medium shelf:text-gray-500 shelf:uppercase shelf:tracking-wider">
                                Created
                            </label>
                            <div className="shelf:mt-1 shelf:text-sm shelf:text-gray-900">
                                {formatDate(item.created_at)}
                            </div>
                        </div>

                        <div>
                            <label className="shelf:text-xs shelf:font-medium shelf:text-gray-500 shelf:uppercase shelf:tracking-wider">
                                Modified
                            </label>
                            <div className="shelf:mt-1 shelf:text-sm shelf:text-gray-900">
                                {formatDate(item.updated_at)}
                            </div>
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="shelf:border-t shelf:border-gray-200 shelf:pt-4">
                        <label className="shelf:text-xs shelf:font-medium shelf:text-gray-500 shelf:uppercase shelf:tracking-wider shelf:block shelf:mb-3">
                            Actions
                        </label>

                        <div className="shelf:space-y-2">
                            <button
                                onClick={() => handleRename(item)}
                                className="shelf:w-full shelf:flex shelf:items-center shelf:px-3 shelf:py-2 shelf:text-sm shelf:text-gray-700 shelf:bg-gray-50 shelf:rounded shelf:hover:bg-gray-100 shelf:transition-colors"
                            >
                                <Icon name="PencilIcon" className="shelf:w-4 shelf:h-4 shelf:mr-2" />
                                Rename
                            </button>

                            {item.type === 'file' && (
                                <button
                                    onClick={() => handleDownload(item)}
                                    className="shelf:w-full shelf:flex shelf:items-center shelf:px-3 shelf:py-2 shelf:text-sm shelf:text-gray-700 shelf:bg-gray-50 shelf:rounded shelf:hover:bg-gray-100 shelf:transition-colors"
                                >
                                    <Icon name="ArrowDownTrayIcon" className="shelf:w-4 shelf:h-4 shelf:mr-2" />
                                    Download
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Rename Modal */}
            {showRenameModal && (
                <div className="shelf:fixed shelf:inset-0 shelf:bg-black shelf:bg-opacity-50 shelf:flex shelf:items-center shelf:justify-center shelf:z-50">
                    <div className="shelf:bg-white shelf:rounded shelf:p-6 shelf:w-96 shelf:text-gray-900">
                        <h3 className="shelf:text-lg shelf:font-medium shelf:mb-4">
                            Rename {renamingItem?.type === 'folder' ? 'Folder' : 'File'}
                        </h3>
                        <div>
                            <input
                                type="text"
                                value={newName}
                                onChange={(e) => setNewName(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        handleRenameSubmit();
                                    } else if (e.key === 'Escape') {
                                        setShowRenameModal(false);
                                        setRenamingItem(null);
                                        setNewName('');
                                    }
                                }}
                                className="shelf:w-full shelf:px-3 shelf:py-2 shelf:border shelf:border-gray-300 shelf:rounded shelf:focus:outline-none shelf:focus:ring-2 shelf:focus:ring-blue-500"
                                autoFocus
                            />
                            <div className="shelf:flex shelf:justify-end shelf:space-x-2 shelf:mt-4">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setShowRenameModal(false);
                                        setRenamingItem(null);
                                        setNewName('');
                                    }}
                                    className="shelf:px-4 shelf:py-2 shelf:text-gray-600 shelf:hover:text-gray-800 shelf:transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    onClick={handleRenameSubmit}
                                    disabled={!newName.trim()}
                                    className="shelf:px-4 shelf:py-2 shelf:bg-blue-600 shelf:text-white shelf:rounded shelf:hover:bg-blue-700 shelf:disabled:opacity-50 shelf:disabled:cursor-not-allowed shelf:transition-colors"
                                >
                                    Rename
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default FileDetails;