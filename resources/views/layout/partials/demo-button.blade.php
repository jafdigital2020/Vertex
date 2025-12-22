{{-- Request a Demo Floating Button --}}
<div id="demo-button-container" class="demo-button-wrapper">
    <a href="https://timora.ph/contact-us/"
       target="_blank"
       rel="noopener noreferrer"
       class="request-demo-btn"
       id="demoButton"
       title="Request a Demo">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span class="demo-btn-text">Request a Demo</span>
        <!-- Start of LiveChat code -->
<script>
  window.__lc = window.__lc || {};
  window.__lc.license = 18469914;
  window.__lc.integration_name = "manual_channels";
  window.__lc.product_name = "livechat";
 
  // ROUTING LOGIC
  (function () {
    var path = window.location.pathname;
 
    if (path.includes('timora')) {
      // Specific page → Group 1
      window.__lc.group = 1;
    } else {
      // All other pages → Group 2
      window.__lc.group = 2;
    }
  })();
</script>
 
<script>
  (function(n,t,c){
    function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}
    var e={_q:[],_h:null,_v:"2.0",
      on:function(){i(["on",c.call(arguments)])},
      once:function(){i(["once",c.call(arguments)])},
      off:function(){i(["off",c.call(arguments)])},
      get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},
      call:function(){i(["call",c.call(arguments)])},
      init:function(){
        var n=t.createElement("script");
        n.async=!0;
        n.type="text/javascript";
        n.src="https://cdn.livechatinc.com/tracking.js";
        t.head.appendChild(n);
      }
    };
    !n.__lc.asyncInit && e.init();
    n.LiveChatWidget=n.LiveChatWidget||e;
  })(window,document,[].slice);
</script>
 
<noscript>
  <a href="https://www.livechat.com/chat-with/18469914/" rel="nofollow">
    Chat with us
  </a>
</noscript>
<!-- End of LiveChat code -->
    </a>

    <button type="button"
            class="demo-close-btn"
            id="closeDemoBtn"
            title="Hide demo button"
            aria-label="Hide demo button">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
</div>

{{-- Minimized Show Button --}}
<button type="button"
        class="demo-show-btn"
        id="showDemoBtn"
        title="Show Request a Demo button"
        aria-label="Show Request a Demo button"
        style="display: none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
    </svg>
</button>

<style>
    .demo-button-wrapper {
        position: fixed;
        bottom: 30px;
        right: 30px;
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 9999;
        transition: all 0.3s ease;
    }

    .demo-button-wrapper.hidden {
        display: none;
    }

    .request-demo-btn {
        background: linear-gradient(135deg, #008080 0%, #006666 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 50px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(0, 128, 128, 0.25);
        transition: all 0.3s ease;
        white-space: nowrap;
        opacity: 0.9;
    }

    .request-demo-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 128, 128, 0.35);
        color: white;
        text-decoration: none;
        opacity: 1;
    }

    .request-demo-btn:active {
        transform: translateY(0);
    }

    .request-demo-btn svg {
        flex-shrink: 0;
    }

    .demo-close-btn {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 0;
        color: #666;
    }

    .demo-close-btn:hover {
        background: white;
        color: #dc3545;
        transform: scale(1.1);
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
    }

    .demo-close-btn:active {
        transform: scale(0.95);
    }

    .demo-show-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: linear-gradient(135deg, #008080 0%, #006666 100%);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 128, 128, 0.25);
        transition: all 0.3s ease;
        z-index: 9998;
        padding: 0;
        opacity: 0.9;
    }

    .demo-show-btn:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 128, 128, 0.35);
        opacity: 1;
    }

    .demo-show-btn:active {
        transform: translateY(0) scale(1);
    }

    /* Responsive design */
    @media (max-width: 991.98px) {
        .demo-button-wrapper {
            bottom: 20px;
            right: 20px;
        }

        .request-demo-btn {
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

        .demo-close-btn {
            width: 20px;
            height: 20px;
        }

        .demo-close-btn svg {
            width: 12px;
            height: 12px;
        }

        .demo-show-btn {
            bottom: 20px;
            right: 20px;
            width: 36px;
            height: 36px;
        }

        .demo-show-btn svg {
            width: 16px;
            height: 16px;
        }
    }

    /* Accessibility */
    .request-demo-btn:focus,
    .demo-close-btn:focus,
    .demo-show-btn:focus {
        outline: 3px solid rgba(0, 128, 128, 0.5);
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

    .demo-button-wrapper {
        animation: slideInRight 0.6s ease-out 0.5s both;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .demo-show-btn {
        animation: fadeIn 0.3s ease-out;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const demoContainer = document.getElementById('demo-button-container');
        const closeBtn = document.getElementById('closeDemoBtn');
        const showBtn = document.getElementById('showDemoBtn');
        const STORAGE_KEY = 'demoBtnHidden';

        // Check localStorage on page load
        const isHidden = localStorage.getItem(STORAGE_KEY) === 'true';
        if (isHidden) {
            demoContainer.classList.add('hidden');
            showBtn.style.display = 'flex';
        }

        // Close button click handler
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            demoContainer.classList.add('hidden');
            localStorage.setItem(STORAGE_KEY, 'true');

            // Show the minimized button after animation
            setTimeout(() => {
                showBtn.style.display = 'flex';
            }, 300);
        });

        // Show button click handler
        showBtn.addEventListener('click', function() {
            showBtn.style.display = 'none';
            demoContainer.classList.remove('hidden');
            localStorage.setItem(STORAGE_KEY, 'false');
        });
    });
</script>
