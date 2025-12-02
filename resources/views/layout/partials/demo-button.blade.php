{{-- Request a Demo Floating Button --}}
<a href="https://timora.ph/contact-us/"
   target="_blank"
   rel="noopener noreferrer"
   class="request-demo-btn"
   title="Request a Demo">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
    </svg>
    <span class="demo-btn-text">Request a Demo</span>
</a>

<style>
    .request-demo-btn {
        position: fixed;
        bottom: 80px;
        right: 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 50px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
        z-index: 9999;
        white-space: nowrap;
    }

    .request-demo-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        color: white;
        text-decoration: none;
    }

    .request-demo-btn:active {
        transform: translateY(0);
    }

    .request-demo-btn svg {
        flex-shrink: 0;
    }

    /* Responsive design */
    @media (max-width: 991.98px) {
        .request-demo-btn {
            bottom: 70px;
            right: 20px;
            padding: 10px 16px;
            font-size: 13px;
        }

        .demo-btn-text {
            display: none;
        }

        .request-demo-btn {
            width: 44px;
            height: 44px;
            padding: 12px;
            justify-content: center;
            border-radius: 50%;
        }
    }

    /* Accessibility */
    .request-demo-btn:focus {
        outline: 3px solid rgba(102, 126, 234, 0.5);
        outline-offset: 2px;
    }

    /* Animation on page load */
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .request-demo-btn {
        animation: slideInRight 0.6s ease-out 0.5s both;
    }
</style>
