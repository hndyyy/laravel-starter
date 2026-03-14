@if (setting("google_analytics") !== "")
    @php
        $google_analytics = setting("google_analytics");
        $nonce = request()->attributes->get('csp_nonce');
    @endphp

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $google_analytics }}" nonce="{{ $nonce }}"></script>
    <script nonce="{{ $nonce }}">
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', '{{ $google_analytics }}');
    </script>
@endif
