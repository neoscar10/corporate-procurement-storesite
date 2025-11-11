 <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Media & URLs</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
            
                        {{-- Attached Files --}}
                        <div class="col-12">
                            <div class="text-muted small mb-2">Attached Files</div>
            
                            @if(method_exists($item, 'attachments') && $item->attachments->count())
                                <div class="row g-2">
                                    @foreach($item->attachments as $att)
                                        @php
        $path = $att->path ?? '';
        $url = \Illuminate\Support\Facades\Storage::url($path);
        $name = $att->original_name ?? basename($path);
        $mime = $att->mime ?? null;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $isImage = ($mime && str_starts_with($mime, 'image/'))
            || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                        @endphp

                                        @if($isImage)
                                            <div class="col-6 col-md-4 col-lg-3">
                                                <a href="{{ $url }}" target="_blank" class="text-decoration-none d-block">
                                                    <div class="border rounded-3 p-1 shadow-sm h-100">
                                                        <div class="ratio ratio-4x3">
                                                            <img src="{{ $url }}" alt="{{ $name }}" class="img-fluid rounded-2"
                                                                style="object-fit: cover;">
                                                        </div>
                                                        <div class="small text-truncate mt-2" title="{{ $name }}">
                                                            <i class="mdi mdi-image-multiple-outline me-1"></i>{{ $name }}
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @else
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <a href="{{ $url }}" class="btn btn-sm btn-light w-100 text-start waves-effect waves-light"
                                                    target="_blank">
                                                    <i class="mdi mdi-paperclip me-1"></i>
                                                    {{ \Illuminate\Support\Str::limit($name, 36) }}
                                                </a>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted">No files attached.</div>
                            @endif
                        </div>
            
                        {{-- Product URLs --}}
                        @php
$urls = ($item->kind === 'product' && $item->productSpec)
    ? (is_array($item->productSpec->product_urls ?? null) ? $item->productSpec->product_urls : [])
    : [];
                        @endphp
                        
                        <div class="col-12">
                            @php $ps = $item->productSpec; @endphp
                            <div class="text-muted small mb-2">Product URLs</div>
                            @if($ps && is_array($ps->product_urls) && count($ps->product_urls))
                                <ul class="list-unstyled mb-0">
                                    @foreach($ps->product_urls as $u)
                                        <li class="mb-1">
                                            <a href="{{ $u }}" target="_blank" class="text-decoration-underline">
                                                {{ \Illuminate\Support\Str::limit($u, 70) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-muted">No URLs captured.</div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>