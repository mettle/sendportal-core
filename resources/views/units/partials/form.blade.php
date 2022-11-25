

@include('sendportal::layouts.partials.summernote')

@push('js')
    <script>

        $(function () {
            const smtp = {{
                $emailServices->filter(function ($service) {
                    return $service->type_id === \Sendportal\Base\Models\EmailServiceType::SMTP;
                })
                ->pluck('id')
            }};

            let service_id = $('select[name="email_service_id"]').val();

            toggleTracking(smtp.includes(parseInt(service_id, 10)));

            $('select[name="email_service_id"]').on('change', function () {
              toggleTracking(smtp.includes(parseInt(this.value, 10)));
            });
        });

        function toggleTracking(disable) {
            let $open = $('input[name="is_open_tracking"]');
            let $click = $('input[name="is_click_tracking"]');

            if (disable) {
                $open.attr('disabled', 'disabled');
                $click.attr('disabled', 'disabled');
            } else {
                $open.removeAttr('disabled');
                $click.removeAttr('disabled');
            }
        }

    </script>
@endpush
