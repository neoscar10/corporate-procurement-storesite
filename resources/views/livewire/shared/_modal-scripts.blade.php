@once
    @push('scripts')
        <script>
            // Listen for Livewire DOM events and toggle Bootstrap modals
            window.addEventListener('open-modal', (e) => {
                const id = e.detail?.id;
                if (!id) return;
                const el = document.getElementById(id);
                if (!el) return;
                bootstrap.Modal.getOrCreateInstance(el).show();
            });

            window.addEventListener('close-modal', (e) => {
                const id = e.detail?.id;
                if (!id) return;
                const el = document.getElementById(id);
                if (!el) return;
                const inst = bootstrap.Modal.getInstance(el);
                if (inst) inst.hide();
            });
        </script>
    @endpush
@endonce