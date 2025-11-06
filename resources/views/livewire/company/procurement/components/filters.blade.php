<div class="card">
    <div class="card-body">
        <div class="row gy-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" placeholder="Title, code..." wire:model.debounce.400ms="search">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" wire:model="status">
                    <option value="all">All</option>
                    <option value="draft">Draft</option>
                    <option value="pending_approval">Pending approval</option>
                    <option value="approved">Approved</option>
                    <option value="published">Published</option>
                    <option value="rejected">Rejected</option>
                    <option value="closed">Closed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select class="form-select" wire:model="type">
                    <option value="all">All</option>
                    <option value="rfi">RFI</option>
                    <option value="req">REQ</option>
                    <option value="po">PO</option>
                    <option value="rfp">RFP</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Per page</label>
                <select class="form-select" wire:model="perPage">
                    <option>10</option>
                    <option>20</option>
                    <option>50</option>
                </select>
            </div>
        </div>
    </div>
</div>