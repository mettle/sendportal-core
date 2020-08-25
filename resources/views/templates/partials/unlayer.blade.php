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