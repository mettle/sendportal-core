<div id="editor-container" style="height: 100vh"></div>

@push('js')

    <script src="//editor.unlayer.com/embed.js"></script>
    <script>
        unlayer.init({
            id: 'editor-container'
        });

        @if (isset($template) and $template->json)
            unlayer.loadDesign({!! $template->json !!});
        @endif

        unlayer.registerCallback('image', function(file, done) {
            var formData = new FormData();

            formData.append('file', file.attachments[0]);

            $.ajax({
                type:'POST',
                url: "{{ route('sendportal.ajax.file.store') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (data) => {
                    done({ progress: 100, url: data.file });
                },
                error: function(res) {
                    alert('Upload failed!');
                }
            });
        });

        $(document).ready(function () {
            $('#template-form').on('submit', function(e) {
                if ($(document.activeElement).attr('name') === 'builder') {
                    e.preventDefault();

                    var $this = $(this);

                    unlayer.exportHtml(function(data) {
                        var json = data.design;
                        var html = data.html;

                        $this.append($("<input type='hidden'>").attr({ name: 'html', value: html }));
                        $this.append($("<input type='hidden'>").attr({ name: 'json', value: JSON.stringify(json) }));

                        $this.off('submit').submit();
                    });
                }
            });
        });
    </script>

@endpush