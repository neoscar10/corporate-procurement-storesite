<x-ui.modal id="itemWizard" :show="$show" size="lg" wire:key="item-wizard-{{ $requestId }}">
    <x-slot:name>open-item-wizard</x-slot:name>
    {{-- <x-slot:name>open-item-wizard</x-slot:name> --}}
    <x-slot:title>Add {{ ucfirst($kind) }}</x-slot:title>

    <div class="pb-2">
        <div class="progress" style="height:6px;">
            <div class="progress-bar" role="progressbar" style="width: {{ $step * 33.34 }}%"></div>
        </div>
    </div>

    @if($step === 1)
        @php $isService = ($kind === 'service'); @endphp

        <div class="row g-3" wire:key="step1-{{ $kind }}">
            {{-- Row 1: Core --}}
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" wire:model.defer="name">
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Priority</label>
                <select class="form-select" wire:model.defer="priority">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                @error('priority')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

           

            @if($kind === 'product')
              <div class="col-md-3">
                  <label class="form-label">Unit</label>
                  <input type="text" class="form-control" wire:model.defer="unit" placeholder="e.g., pcs, hr">
                  @error('unit')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" min="1" step="1" class="form-control" wire:model.defer="quantity">
                    @error('quantity')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            @endif

            {{-- Row 2: Dates & Budget (+ service extras snap into place) --}}
            <div class="{{ $isService ? 'col-md-3' : 'col-md-4' }}">
                <label class="form-label">Date Required</label>
                <input type="date" class="form-control" wire:model.defer="date_required">
                @error('date_required')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="{{ $isService ? 'col-md-3' : 'col-md-4' }}">
                <label class="form-label">Budget Amount (₹)</label>
                <div class="input-group">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" class="form-control" wire:model.defer="budget_amount">
                </div>
                @error('budget_amount')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            @if($isService)
                {{-- <div class="col-md-3">
                    <label class="form-label">Budget Mode</label>
                    <select class="form-select" wire:model.defer="service_budget_mode">
                        <option value="">—</option>
                        <option value="per_hour">Per Hour</option>
                        <option value="fixed">Fixed</option>
                    </select>
                    @error('service_budget_mode')<div class="text-danger small">{{ $message }}</div>@enderror
                </div> --}}

                <div class="col-md-3">
                    <label class="form-label">Payment Type</label>
                    <select class="form-select" wire:model.defer="service_payment_type">
                        <option value="">—</option>
                        <option value="per_hour">Per Hour</option>
                        <option value="fixed">Fixed</option>
                    </select>
                    @error('service_payment_type')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            @endif

            {{-- Row 3: Description full width for clean reading --}}
            <div class="col-12">
                <label class="form-label">Short Description</label>
                <textarea class="form-control" rows="5" wire:model.defer="short_description"></textarea>
                @error('short_description')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-end">
            <button class="btn btn-primary waves-effect waves-light" wire:click="saveDetail" wire:loading.attr="disabled"
                wire:target="saveDetail">
                <span wire:loading.remove wire:target="saveDetail">Save & Continue</span>
                <span wire:loading wire:target="saveDetail"><x-ui.spinner size="sm" text="Saving..." /></span>
            </button>
        </div>

    @elseif($step === 2)
        @if($kind === 'product')
            <div class="row g-3" wire:key="spec-product">
                <div class="col-md-4"><label class="form-label">Brand</label><input class="form-control"
                        wire:model.defer="brand"></div>
                <div class="col-md-4"><label class="form-label">Model</label><input class="form-control"
                        wire:model.defer="model"></div>
                <div class="col-md-4"><label class="form-label">Quality Level</label><input class="form-control"
                        wire:model.defer="quality_level"></div>
                <div class="col-md-6"><label class="form-label">Packaging Requirement</label><input class="form-control"
                        wire:model.defer="packaging_requirement"></div>
                <div class="col-md-6 d-flex align-items-center pt-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model.defer="inspection_required" id="inspect">
                        <label class="form-check-label" for="inspect">Inspection required</label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Technical Specs</label>
                    @foreach($technical_specs as $i => $row)
                        <div class="row g-2 align-items-center mb-1">
                            <div class="col-md-5"><input class="form-control" placeholder="Key"
                                    wire:model.defer="technical_specs.{{ $i }}.key"></div>
                            <div class="col-md-5"><input class="form-control" placeholder="Value"
                                    wire:model.defer="technical_specs.{{ $i }}.value"></div>
                            <div class="col-md-2">
                                <button class="btn btn-light btn-sm w-100"
                                    wire:click.prevent="removeTechRow({{ $i }})">Remove</button>
                            </div>
                        </div>
                    @endforeach
                    <button class="btn btn-soft-primary btn-sm mt-1" wire:click.prevent="addTechRow">+ Add Row</button>
                </div>
            </div>
        @else
            <div class="row g-3" wire:key="spec-service">
                <div class="col-12">
                    <label class="form-label">Scope of Work</label>
                    <textarea class="form-control" rows="3" wire:model.defer="scope_of_work"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Deliverables</label>
                    @foreach($deliverables as $i => $d)
                        <div class="row g-2 align-items-center mb-1">
                            <div class="col-md-3"><input class="form-control" placeholder="Milestone"
                                    wire:model.defer="deliverables.{{ $i }}.milestone"></div>
                            <div class="col-md-5"><input class="form-control" placeholder="Criteria"
                                    wire:model.defer="deliverables.{{ $i }}.criteria"></div>
                            <div class="col-md-3"><input type="date" class="form-control"
                                    wire:model.defer="deliverables.{{ $i }}.due_date"></div>
                            <div class="col-md-1">
                                <button class="btn btn-light btn-sm w-100"
                                    wire:click.prevent="removeDeliverable({{ $i }})">×</button>
                            </div>
                        </div>
                    @endforeach
                    <button class="btn btn-soft-primary btn-sm mt-1" wire:click.prevent="addDeliverable">+ Add Row</button>
                </div>
                <div class="col-12">
                    <label class="form-label">Key Personnels</label>
                    @foreach($key_personnels as $i => $p)
                        <div class="row g-2 align-items-center mb-1">
                            <div class="col-md-4"><input class="form-control" placeholder="Role"
                                    wire:model.defer="key_personnels.{{ $i }}.role"></div>
                            <div class="col-md-3"><input type="number" min="1" class="form-control" placeholder="Count"
                                    wire:model.defer="key_personnels.{{ $i }}.count"></div>
                            <div class="col-md-4"><input class="form-control" placeholder="Qualification"
                                    wire:model.defer="key_personnels.{{ $i }}.qualification"></div>
                            <div class="col-md-1">
                                <button class="btn btn-light btn-sm w-100" wire:click.prevent="removePersonnel({{ $i }})">×</button>
                            </div>
                        </div>
                    @endforeach
                    <button class="btn btn-soft-primary btn-sm mt-1" wire:click.prevent="addPersonnel">+ Add Row</button>
                </div>
            </div>
        @endif
        <div class="mt-3 d-flex justify-content-between">
            <button class="btn btn-light" wire:click="$set('step', 1)">Back</button>
            <button class="btn btn-primary waves-effect waves-light" wire:click="saveSpecs">Save & Continue</button>
        </div>
    @elseif($step === 3)
  <div class="mb-2">
    <label class="form-label fw-semibold">Attachments</label>

    {{-- helper text --}}
    <div class="alert alert-soft-info py-2 px-3 mb-3 d-flex align-items-center" role="alert">
      <i class="mdi mdi-information-outline me-2 fs-5"></i>
      <div class="small">
        Add technical drawings, bill of materials, specifications, photos, or any supporting documents.
      </div>
    </div>

    {{-- Dropzone --}}
    <div id="dropzoneWizardFiles"
         class="border border-2 border-dashed rounded-3 bg-light-subtle p-4 text-center position-relative"
         style="cursor:pointer; transition: border-color .2s, background-color .2s;">
      <div class="d-flex flex-column align-items-center">
        <i class="mdi mdi-cloud-upload-outline fs-1 mb-2 text-primary"></i>
        <div class="fw-semibold">Drag & Drop files here</div>
        <div class="text-muted small">or click to browse</div>
        <div class="text-muted small mt-1">Max 10MB per file</div>
      </div>

      {{-- Invisible input bound to Livewire --}}
      <input id="wizardFilesInput" type="file" class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
             wire:model="files" multiple style="cursor:pointer;" />

      {{-- Uploading overlay (while Livewire is ingesting files) --}}
      <div class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center bg-white bg-opacity-75"
           wire:loading.class.remove="d-none" wire:target="files">
        <div class="text-center">
          <x-ui.spinner class="mb-2" />
          <div class="small text-muted">Uploading…</div>
          {{-- JS below updates #wizardUploadProgress width --}}
          <div class="progress mt-2" style="height:6px; width:240px; margin:0 auto;">
            <div id="wizardUploadProgress" class="progress-bar" role="progressbar" style="width: 0%"></div>
          </div>
        </div>
      </div>
    </div>

    {{-- Validation note --}}
    @error('files.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror

    {{-- Existing attachments (already saved on the item) --}}
@if(!empty($existing_files))
  <div class="mt-3">
    <label class="form-label fw-semibold">Existing attachments</label>
    <div class="row g-3">
      @foreach($existing_files as $f)
        @php
          $ext   = $f['ext'] ?? '';
          $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'], true);
          $sizeK = !empty($f['size_bytes']) ? number_format(($f['size_bytes'] / 1024), 1) : null;
        @endphp

        <div class="col-md-4" wire:key="existing-{{ $loop->index }}">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-center">
                @if($isImg)
                  <img src="{{ $f['url'] }}" alt="file" class="rounded me-3"
                       style="width:56px;height:56px;object-fit:cover;">
                @else
                  <div class="me-3 d-flex align-items-center justify-content-center rounded bg-light"
                       style="width:56px;height:56px;">
                    @switch($ext)
                      @case('pdf')   <i class="mdi mdi-file-pdf-box fs-2 text-danger"></i> @break
                      @case('doc') @case('docx') <i class="mdi mdi-file-word-box fs-2 text-primary"></i> @break
                      @case('xls') @case('xlsx') <i class="mdi mdi-file-excel-box fs-2 text-success"></i> @break
                      @default       <i class="mdi mdi-file-outline fs-2 text-secondary"></i>
                    @endswitch
                  </div>
                @endif

                <div class="flex-grow-1 text-truncate">
                  <div class="text-truncate small fw-semibold" title="{{ $f['name'] }}">{{ $f['name'] }}</div>
                  <div class="text-muted small">
                    {{ strtoupper($ext ?: 'FILE') }}@if($sizeK) • {{ $sizeK }}KB @endif
                  </div>
                </div>

                <a href="{{ $f['url'] }}" target="_blank"
                   class="btn btn-sm btn-light material-shadow-none"
                   title="Open">
                  <i class="mdi mdi-eye-outline"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endif


    {{-- Previews list --}}
    @if(!empty($files))
      <div class="row g-3 mt-3">
        @foreach($files as $i => $file)
          @php
            $name = method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'selected-file';
            $ext  = method_exists($file, 'getClientOriginalExtension') ? strtolower($file->getClientOriginalExtension()) : '';
            $isImg = false;
            try { $isImg = method_exists($file,'getMimeType') ? str_starts_with($file->getMimeType(), 'image/') : false; } catch (\Throwable $e) {}
          @endphp

          <div class="col-md-4" wire:key="preview-{{ $i }}">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  @if($isImg)
                    {{-- Live image preview --}}
                    <img src="{{ $file->temporaryUrl() }}" alt="preview" class="rounded me-3" style="width:56px;height:56px;object-fit:cover;">
                  @else
                    {{-- Icon preview by type --}}
                    <div class="me-3 d-flex align-items-center justify-content-center rounded bg-light"
                         style="width:56px;height:56px;">
                      @switch($ext)
                        @case('pdf')  <i class="mdi mdi-file-pdf-box fs-2 text-danger"></i> @break
                        @case('doc') @case('docx') <i class="mdi mdi-file-word-box fs-2 text-primary"></i> @break
                        @case('xls') @case('xlsx') <i class="mdi mdi-file-excel-box fs-2 text-success"></i> @break
                        @default      <i class="mdi mdi-file-outline fs-2 text-secondary"></i>
                      @endswitch
                    </div>
                  @endif

                  <div class="flex-grow-1 text-truncate">
                    <div class="text-truncate small fw-semibold" title="{{ $name }}">{{ $name }}</div>
                    <div class="text-muted small">
                      @php
                        $sizeKB = method_exists($file,'getSize') ? round($file->getSize()/1024,1) : null;
                      @endphp
                      {{ $ext ? strtoupper($ext) : 'FILE' }}{{ $sizeKB ? ' • '.$sizeKB.'KB' : '' }}
                    </div>
                  </div>

                  <button class="btn btn-sm btn-light material-shadow-none"
                          title="Remove"
                          wire:click="removeSelectedFile({{ $i }})">
                    <i class="mdi mdi-close"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    {{-- Product URLs --}}
    <div class="col-12 mt-2">
        <label class="form-label">Product URLs</label>
        @foreach($product_urls as $i => $url)
            <div class="row g-2 align-items-center mb-1" wire:key="url-{{ $i }}">
                <div class="col-md-10">
                    <input type="url" class="form-control" placeholder="https://example.com/product"
                          wire:model.defer="product_urls.{{ $i }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-light btn-sm w-100"
                            wire:click.prevent="removeUrlRow({{ $i }})">Remove</button>
                </div>
            </div>
        @endforeach

        <button class="btn btn-soft-primary btn-sm mt-1"
                wire:click.prevent="addUrlRow">+ Add URL</button>

        @error('product_urls.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    </div>



  </div>

  {{-- Footer actions --}}
  <div class="mt-3 d-flex justify-content-between">
    <button class="btn btn-light" wire:click="$set('step', 2)">Back</button>

    <button class="btn btn-success text-light waves-effect waves-light"
            wire:click="saveAttachments"
            {{-- disable while saving OR while files are uploading --}}
            wire:loading.attr="disabled"
            wire:target="saveAttachments,files">
      <span wire:loading.remove wire:target="saveAttachments">Finish</span>
      <span wire:loading wire:target="saveAttachments">
        <x-ui.spinner size="sm" text="Saving..." />
      </span>
    </button>
  </div>

  {{-- Drag & drop + progress JS (no Alpine required) --}}
  <script>
    (function() {
      const dz    = document.getElementById('dropzoneWizardFiles');
      const input = document.getElementById('wizardFilesInput');
      if (!dz || !input) return;

      // Hover styles
      const enter = (e) => { e.preventDefault(); dz.classList.add('border-primary','bg-white'); };
      const leave = (e) => { e.preventDefault(); dz.classList.remove('border-primary','bg-white'); };

      ['dragenter','dragover'].forEach(ev => dz.addEventListener(ev, enter));
      ['dragleave','dragend','drop'].forEach(ev => dz.addEventListener(ev, leave));

      dz.addEventListener('click', () => input.click());

      // Handle drop → feed files into input
      dz.addEventListener('drop', (e) => {
        e.preventDefault();
        const files = e.dataTransfer?.files;
        if (!files || !files.length) return;

        try {
          // Modern browsers allow assigning FileList directly:
          input.files = files;
        } catch (_) {
          // Fallback: DataTransfer shim
          const dt = new DataTransfer();
          for (let i = 0; i < files.length; i++) dt.items.add(files[i]);
          input.files = dt.files;
        }

        input.dispatchEvent(new Event('change', { bubbles: true }));
      });

      // Livewire upload progress → update progress bar
      window.addEventListener('livewire-upload-progress', (event) => {
        const el = document.getElementById('wizardUploadProgress');
        if (el) el.style.width = (event.detail.progress || 0) + '%';
      });
    })();
  </script>
@endif


    <x-slot:footer>
        <button class="btn btn-ghost-secondary material-shadow-none" data-bs-dismiss="modal"
            wire:click="close">Close</button>
    </x-slot:footer>
</x-ui.modal>

<script>
    window.addEventListener('open-item-wizard-js', () => {
        const el = document.getElementById('itemWizard');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });
    window.addEventListener('hide-item-wizard-js', () => {
        const el = document.getElementById('itemWizard');
        if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
    });
</script>