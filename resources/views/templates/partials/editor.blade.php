@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/theme/monokai.min.css">
    <style>
        .CodeMirror {
            height: 600px;
        }

        .template-preview {
            height: 600px;
        }
    </style>
@endpush

<div class="form-group row form-group-content template-content">
    <label for="id-field-content" class="control-label col-sm-2">{{ __('Content') }}</label>
    <div class="col-sm-10">
        <textarea id="id-field-content" class="form-control" name="content" cols="50"
                  rows="20">{{ old('content', $template->content ?? null) }}</textarea>
    </div>
</div>

<div class="form-group row template-preview d-none">
    <div class="offset-sm-2 col-sm-10">
        <div class="border border-light h-100">
            <iframe width="100%" height="100%" scrolling="yes" frameborder="0"
                    srcdoc="{!! old('content', $template->content ?? null)  !!} "></iframe>
        </div>
    </div>
</div>

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/mode/xml/xml.min.js"></script>

    <script>
        $(document).ready(function () {
            var codeMirror = CodeMirror.fromTextArea(document.getElementById('id-field-content'), {
                lineNumbers: true,
                lineWrapping: true,
                mode: 'xml',
                theme: 'monokai',
            });

            $('.btn-preview').click(function (e) {
                e.preventDefault();

                var elContent = $('.template-preview');
                var elPreview = $('.template-content');
                var elButton = $('.btn-preview');

                if (elContent.hasClass('d-none')) {
                    $('.template-preview iframe').attr('srcdoc', codeMirror.getValue());
                    elContent.removeClass('d-none');
                    elPreview.addClass('d-none');
                    elButton.text('Show Design');
                } else {
                    elContent.addClass('d-none');
                    elPreview.removeClass('d-none');
                    elButton.text('Show Preview');
                }
            });
        });
    </script>
@endpush
