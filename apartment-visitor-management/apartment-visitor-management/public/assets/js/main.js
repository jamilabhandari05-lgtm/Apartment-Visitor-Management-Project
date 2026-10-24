(function () {
    'use strict';

    document.querySelectorAll('.alert').forEach(function (alertEl) {
        setTimeout(function () {
            alertEl.style.display = 'none';
        }, 6000);
    });

    var codeInput = document.getElementById('code');
    if (codeInput) {
        codeInput.addEventListener('input', function () {
            codeInput.value = codeInput.value.toUpperCase();
        });
    }

    var tabLinks = document.querySelectorAll('[data-tab-target]');
    var tabPanels = document.querySelectorAll('[data-tab-panel]');
    tabLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var target = link.getAttribute('data-tab-target');

            tabLinks.forEach(function (l) { l.classList.remove('active'); });
            link.classList.add('active');

            tabPanels.forEach(function (panel) {
                panel.classList.toggle('hidden', panel.getAttribute('data-tab-panel') !== target);
            });
        });
    });
})();
