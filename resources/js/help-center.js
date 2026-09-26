/**
 * Pusat Bantuan — buka topik accordion sesuai hash URL (#cara-memesan, dst)
 * saat halaman dimuat, tanpa reload, memakai Bootstrap Collapse API.
 */

import { Collapse } from 'bootstrap';

function openTopicFromHash() {
    let hash = window.location.hash;

    if (!hash || hash === '#') {
        return;
    }

    try {
        hash = decodeURIComponent(hash);
    } catch (error) {
        return;
    }

    let topic;

    try {
        topic = document.querySelector(hash);
    } catch (error) {
        return;
    }

    if (!topic || !topic.classList.contains('accordion-item')) {
        return;
    }

    const collapse = topic.querySelector('.accordion-collapse');

    if (collapse && !collapse.classList.contains('show')) {
        new Collapse(collapse, { show: true });
    }

    topic.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', openTopicFromHash);
} else {
    openTopicFromHash();
}

window.addEventListener('hashchange', openTopicFromHash);
