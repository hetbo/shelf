<div class="shelf:bg-white shelf:border-t shelf:border-gray-200 shelf:px-6 shelf:py-3">
    <div class="shelf:flex shelf:items-center shelf:justify-between shelf:text-sm shelf:text-gray-600">
        {{-- Left side - Status info --}}
        <div class="shelf:flex shelf:items-center shelf:space-x-6">
            <div class="shelf:flex shelf:items-center shelf:space-x-2">
                <x-my-hard-drive class="shelf:w-4 shelf:h-4"/>
                <span>Storage: 45.2 GB of 100 GB used</span>
                <div class="shelf:w-32 shelf:h-2 shelf:bg-gray-200 shelf:rounded-full shelf:ml-2">
                    <div class="shelf:w-14 shelf:h-2 shelf:bg-blue-600 shelf:rounded-full"></div>
                </div>
            </div>
            <div class="shelf:border-l shelf:border-gray-200 shelf:h-4"></div>
            <div class="shelf:flex shelf:items-center shelf:space-x-2">
                <x-my-wifi class="shelf:w-4 shelf:h-4 shelf:text-green-600"/>
                <span>Connected</span>
            </div>
        </div>

        {{-- Right side - Selection info --}}
        <div class="shelf:flex shelf:items-center shelf:space-x-4">
            <div class="shelf:flex shelf:items-center shelf:space-x-2">
                <x-my-check-circle class="shelf:w-4 shelf:h-4 shelf:text-blue-600"/>
                <span>1 file selected (2.4 MB)</span>
            </div>
            <div class="shelf:border-l shelf:border-gray-200 shelf:h-4"></div>
            <button class="shelf:text-blue-600 hover:shelf:text-blue-700 shelf:flex shelf:items-center shelf:space-x-1"
                    hx-get="/file-manager/select-all" hx-target="#file-list">
                <span>Select All</span>
            </button>
        </div>
    </div>
</div>

{{-- Modal Container for dialogs --}}
<div id="modal-container"></div>