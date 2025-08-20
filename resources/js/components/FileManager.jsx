import React, { useState, useEffect } from 'react';

const FileManager = () => {
    const [files, setFiles] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchFiles();
    }, []);

    const fetchFiles = async () => {
        try {
            const response = await fetch(`${window.shelfApiUrl}/files`, {
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                }
            });
            const data = await response.json();
            setFiles(data);
        } catch (error) {
            console.error('Error fetching files:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleUpload = async (event) => {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        try {
            await fetch(`${window.shelfApiUrl}/upload`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                },
                body: formData
            });
            fetchFiles(); // Refresh file list
        } catch (error) {
            console.error('Error uploading file:', error);
        }
    };

    if (loading) {
        return <div style={{ padding: '20px' }}>Loading...</div>;
    }

    return (
        <div style={{ padding: '20px' }}>
            <h1>File Manager</h1>

            <div style={{ margin: '20px 0' }}>
                <input
                    type="file"
                    onChange={handleUpload}
                    style={{ padding: '10px', border: '1px solid #ddd', borderRadius: '4px' }}
                />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))', gap: '20px' }}>
                {files.map((file, index) => (
                    <div key={index} style={{
                        padding: '15px',
                        background: 'white',
                        borderRadius: '8px',
                        boxShadow: '0 2px 4px rgba(0,0,0,0.1)'
                    }}>
                        <div>{file}</div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default FileManager;