<div class="template-panel">
    <a href="{{ route('sendportal.templates.edit', $template->id) }}" style="display:block">
        <div class="template-preview">
            <iframe width="100%" height="350px" scrolling="yes" frameborder="0"
                    srcdoc="{{ $template->content }}"></iframe>
        </div>
    </a>
</div>
