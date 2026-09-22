{{-- Shared TinyMCE rich-text editor for any textarea.rich-editor field.
     Include once per form via @push('scripts'). Image uploads go through the
     existing Media Library quick-upload endpoint used by the media picker modal. --}}
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!document.querySelector('textarea.rich-editor')) return;
        tinymce.init({
            selector: 'textarea.rich-editor',
            license_key: 'gpl',
            height: 420,
            menubar: false,
            branding: false,
            plugins: 'link image lists table code media',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image media table | code | removeformat',
            images_upload_handler: function (blobInfo) {
                return new Promise(function (resolve, reject) {
                    var fd = new FormData();
                    fd.append('file', blobInfo.blob(), blobInfo.filename());
                    fetch('{{ route('admin.media.quick-upload') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': window.AK_CSRF, 'Accept': 'application/json' },
                        body: fd,
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.item && data.item.url) {
                                resolve(data.item.url);
                            } else {
                                reject(data.message || 'Upload failed.');
                            }
                        })
                        .catch(function () { reject('Upload failed.'); });
                });
            },
        });
    });
</script>
