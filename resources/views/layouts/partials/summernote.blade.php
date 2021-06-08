@push('css')

    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.min.css" rel="stylesheet">
@endpush

@push('js')

    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.min.js"></script>

    <script>
        $(function () {
            $('#id-field-content').summernote({
                minHeight: 200,
                prettifyHtml: true,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['codeview']],
                ],

                onCreateLink: function (originalLink) {
                    if (originalLink.includes('unsubscribe_url')) {
                        return '@{{unsubscribe_url}}';
                    }

                    if (originalLink.includes('webview_url')) {
                        return '@{{webview_url}}';
                    }

                    return /^([A-Za-z][A-Za-z0-9+-.]*\:|#|\/)/.test(originalLink)
                        ? originalLink : 'http://' + originalLink;
                }
            });
        });
    </script>
@endpush
