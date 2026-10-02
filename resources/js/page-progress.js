import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

// Force side effect
window.__PAGE_PROGRESS_INIT = true;

document.addEventListener('click', function (e) {
    const link = e.target.closest('a');

    if (e.defaultPrevented || link?.hasAttribute('data-no-rt')) {
        return;
    }

    if (link && link.href && link.target !== '_blank' && !link.href.startsWith('javascript:')) {
        NProgress.start();
    }
});

window.addEventListener('load', function () {
    NProgress.done();
});

window.addEventListener('beforeunload', function () {
    NProgress.start();
});

// Export to prevent tree-shaking
export const pageProgress = 'initialized';
