{{-- resources/views/file-manager/partials/file-details.blade.php --}}
<div class="shelf:w-80 shelf:bg-gray-50 shelf:border-l shelf:border-gray-200 shelf:overflow-y-auto" id="file-details">
    <div class="shelf:p-6">
        {{-- File Preview --}}
        <div class="shelf:mb-6">
            <div class="shelf:bg-white shelf:border shelf:border-gray-200 shelf:rounded shelf:p-4 shelf:text-center">
                <x-my-file-spreadsheet class="shelf:w-16 shelf:h-16 shelf:text-green-600 shelf:mx-auto shelf:mb-3"/>
                <h3 class="shelf:font-medium shelf:text-gray-900">budget.xlsx</h3>
                <p class="shelf:text-sm shelf:text-gray-500 shelf:mt-1">Excel Spreadsheet</p>
            </div>
        </div>

        {{-- File Actions --}}
        <div class="shelf:mb-6">
            <div class="shelf:grid shelf:grid-cols-2 shelf:gap-2">
                <button class="shelf:px-3 shelf:py-2 shelf:bg-blue-600 shelf:text-white shelf:text-sm shelf:rounded hover:shelf:bg-blue-700 shelf:flex shelf:items-center shelf:justify-center shelf:space-x-2"
                        hx-get="/file-manager/download/budget.xlsx">
                    <x-my-download class="shelf:w-4 shelf:h-4"/>
                    <span>Download</span>
                </button>
                <button class="shelf:px-3 shelf:py-2 shelf:bg-white shelf:border shelf:border-gray-300 shelf:text-gray-700 shelf:text-sm shelf:rounded hover:shelf:bg-gray-50 shelf:flex shelf:items-center shelf:justify-center shelf:space-x-2"
                        hx-get="/file-manager/preview/budget.xlsx" hx-target="#modal-container">
                    <x-my-eye class="shelf:w-4 shelf:h-4"/>
                    <span>Preview</span>
                </button>
                <button class="shelf:px-3 shelf:py-2 shelf:bg-white shelf:border shelf:border-gray-300 shelf:text-gray-700 shelf:text-sm shelf:rounded hover:shelf:bg-gray-50 shelf:flex shelf:items-center shelf:justify-center shelf:space-x-2"
                        hx-get="/file-manager/rename/budget.xlsx" hx-target="#modal-container">
                    <x-my-edit class="shelf:w-4 shelf:h-4"/>
                    <span>Rename</span>
                </button>
                <button class="shelf:px-3 shelf:py-2 shelf:bg-white shelf:border shelf:border-gray-300 shelf:text-red-600 shelf:text-sm shelf:rounded hover:shelf:bg-red-50 shelf:flex shelf:items-center shelf:justify-center shelf:space-x-2"
                        hx-delete="/file-manager/delete/budget.xlsx"
                        hx-confirm="Are you sure you want to delete this file?">
                    <x-my-trash class="shelf:w-4 shelf:h-4"/>
                    <span>Delete</span>
                </button>
            </div>
        </div>

        {{-- File Information --}}
        <div class="shelf:space-y-4">
            <div>
                <h4 class="shelf:text-sm shelf:font-medium shelf:text-gray-700 shelf:mb-3">Details</h4>
                <div class="shelf:space-y-3 shelf:text-sm">
                    <div class="shelf:flex shelf:justify-between">
                        <span class="shelf:text-gray-600">Size</span>
                        <span class="shelf:text-gray-900">2.4 MB</span>
                    </div>
                    <div class="shelf:flex shelf:justify-between">
                        <span class="shelf:text-gray-600">Type</span>
                        <span class="shelf:text-gray-900">Excel Spreadsheet</span>
                    </div>
                    <div class="shelf:flex shelf:justify-between">
                        <span class="shelf:text-gray-600">Modified</span>
                        <span class="shelf:text-gray-900">2 hours ago</span>
                    </div>
                    <div class="shelf:flex shelf:justify-between">
                        <span class="shelf:text-gray-600">Created</span>
                        <span class="shelf:text-gray-900">Mar 15, 2024</span>
                    </div>
                    <div class="shelf:flex shelf:justify-between">
                        <span class="shelf:text-gray-600">Owner</span>
                        <span class="shelf:text-gray-900">John Doe</span>
                    </div>
                </div>
            </div>

            {{-- File Path --}}
            <div>
                <h4 class="shelf:text-sm shelf:font-medium shelf:text-gray-700 shelf:mb-2">Location</h4>
                <div class="shelf:text-sm shelf:text-gray-600 shelf:bg-white shelf:border shelf:border-gray-200 shelf:rounded shelf:px-3 shelf:py-2">
                    /Documents/Projects/budget.xlsx
                </div>
            </div>

            {{-- Sharing --}}
            <div>
                <h4 class="shelf:text-sm shelf:font-medium shelf:text-gray-700 shelf:mb-3">Sharing</h4>
                <button class="shelf:w-full shelf:px-3 shelf:py-2 shelf:bg-white shelf:border shelf:border-gray-300 shelf:text-gray-700 shelf:text-sm shelf:rounded hover:shelf:bg-gray-50 shelf:flex shelf:items-center shelf:justify-center shelf:space-x-2"
                        hx-get="/file-manager/share/budget.xlsx" hx-target="#modal-container">
                    <x-my-share class="shelf:w-4 shelf:h-4"/>
                    <span>Share File</span>
                </button>
            </div>

            {{-- Recent Activity --}}
            <div>
                <h4 class="shelf:text-sm shelf:font-medium shelf:text-gray-700 shelf:mb-3">Recent Activity</h4>
                <div class="shelf:space-y-3">
                    <div class="shelf:flex shelf:items-start shelf:space-x-3">
                        <div class="shelf:w-6 shelf:h-6 shelf:bg-blue-100 shelf:rounded-full shelf:flex shelf:items-center shelf:justify-center">
                            <x-my-edit class="shelf:w-3 shelf:h-3 shelf:text-blue-600"/>
                        </div>
                        <div class="shelf:flex-1 shelf:text-sm">
                            <p class="shelf:text-gray-900">File modified</p>
                            <p class="shelf:text-gray-600">2 hours ago</p>
                        </div>
                    </div>

                    <div class="shelf:flex shelf:items-start shelf:space-x-3">
                        <div class="shelf:w-6 shelf:h-6 shelf:bg-green-100 shelf:rounded-full shelf:flex shelf:items-center shelf:justify-center">
                            <x-my-upload class="shelf:w-3 shelf:h-3 shelf:text-green-600"/>
                        </div>
                        <div class="shelf:flex-1 shelf:text-sm">
                            <p class="shelf:text-gray-900">File uploaded</p>
                            <p class="shelf:text-gray-600">Mar 15, 2024</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>