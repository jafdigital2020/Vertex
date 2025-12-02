<!-- resources/views/layout/partials/request-a-demo.blade.php -->

<button
    type="button"
    id="vertexRequestDemoBtn"
    class="vertex-request-demo-btn"
    aria-label="Request a demo"
    {{-- Optional: uncomment if you have a named route 'demo.request' --}}
    {{-- data-route="{{ route('demo.request') }}" --}}
>
    <span>Request a Demo</span>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        class="vertex-request-demo-arrow"
        viewBox="0 0 20 20"
        fill="currentColor"
        role="img"
        aria-hidden="true"
    >
        <title>Arrow</title>
        <path
            fill-rule="evenodd"
            d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
            clip-rule="evenodd"
        />
    </svg>
</button>

<style>
.vertex-request-demo-btn {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    background-color: #008080;
    color: #fff;
    padding: 0.65rem 1.25rem;
    border-radius: 9999px;
    box-shadow: 0 10px 15px rgba(0,0,0,0.08);
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    z-index: 9999;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    font-size: 1rem;
    font-weight: 500;
    outline: none;
}
.vertex-request-demo-btn:hover {
    background-color: #006666;
    transform: scale(1.04);
    box-shadow: 0 20px 30px rgba(0,0,0,0.12);
}
.vertex-request-demo-btn:focus {
    box-shadow: 0 0 0 4px rgba(0,128,128,0.18);
}
.vertex-request-demo-btn:active {
    transform: scale(0.99);
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
    .vertex-request-demo-btn {
        transition: none;
        transform: none;
    }
}

.vertex-request-demo-arrow {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}
</style>

<script>
(function () {
    if (window.__vertexRequestDemoInitialized) return;
    window.__vertexRequestDemoInitialized = true;

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('vertexRequestDemoBtn');
        if (!btn) return;

        btn.addEventListener('click', function (e) {
            e.preventDefault();

            // Redirect to the contact page in a new tab
            var url = 'https://timora.ph/contact-us/';
            var newWin = window.open(url, '_blank', 'noopener,noreferrer');
            if (newWin) newWin.opener = null;
        }, { passive: false });
    });
})();
</script>
