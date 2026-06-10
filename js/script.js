/* ── Modal helpers ── */
window.openModal = function (id) {
    var m = document.getElementById(id);
    if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
};
window.closeModal = function (id) {
    var m = document.getElementById(id);
    if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
};

document.addEventListener('DOMContentLoaded', function () {

    // ========== MODAL CONFIRMARE PERSONALIZAT (pentru ștergere antrenament și ștergere cont) ==========
    const confirmModal = document.getElementById('confirmModal');
    const confirmTitle = document.getElementById('confirmModalTitle');
    const confirmMessage = document.getElementById('confirmModalMessage');
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');
    const confirmOkBtn = document.getElementById('confirmOkBtn');

    function showConfirmModal(title, message, confirmButtonText, onConfirm, onCancel) {
        confirmTitle.textContent = title;
        confirmMessage.textContent = message;
        confirmOkBtn.textContent = confirmButtonText;
        confirmModal.style.display = 'flex';

        function handleConfirm() {
            cleanup();
            if (onConfirm) onConfirm();
        }
        function handleCancel() {
            cleanup();
            if (onCancel) onCancel();
        }
        function cleanup() {
            confirmOkBtn.removeEventListener('click', handleConfirm);
            confirmCancelBtn.removeEventListener('click', handleCancel);
            confirmModal.removeEventListener('click', outsideClick);
            document.removeEventListener('keydown', escapeHandler);
            // Resetăm textul butonului la valoarea implicită pentru următoarea utilizare
            confirmOkBtn.textContent = 'Șterge';
        }
        function outsideClick(e) {
            if (e.target === confirmModal) handleCancel();
        }
        function escapeHandler(e) {
            if (e.key === 'Escape') handleCancel();
        }

        confirmOkBtn.addEventListener('click', handleConfirm);
        confirmCancelBtn.addEventListener('click', handleCancel);
        confirmModal.addEventListener('click', outsideClick);
        document.addEventListener('keydown', escapeHandler);
    }

    // 1. Ștergere antrenament
    document.querySelectorAll('[data-confirm-action="delete_workout"]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const form = this.closest('form');
            if (!form) return;
            const title = this.getAttribute('data-confirm-title') || 'Ștergi antrenamentul?';
            const workoutName = this.getAttribute('data-workout-name') || 'acest antrenament';
            showConfirmModal(
                title,
                workoutName,
                'Șterge',
                () => { form.submit(); },
                () => { }
            );
        });
    });

    // 2. Ștergere cont
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const title = this.getAttribute('data-confirm-title') || 'Ștergere cont';
            const message = this.getAttribute('data-confirm-message') || 'Ești sigur? Contul și toate antrenamentele tale vor fi șterse permanent.';
            showConfirmModal(
                title,
                message,
                'Șterge cont',
                () => {
                    document.getElementById('deleteAccountForm').submit();
                },
                () => { }
            );
        });
    }

    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.classList.add('fade-out');
            setTimeout(function () { el.remove(); }, 400);
        }, 4000);
    });

    // Form validation
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var valid = true;
            form.querySelectorAll('[required]').forEach(function (field) {
                if (!field.value.trim()) { field.classList.add('invalid'); valid = false; }
                else field.classList.remove('invalid');
            });
            if (!valid) e.preventDefault();
        });
    });
    document.querySelectorAll('input[required], textarea[required]').forEach(function (el) {
        el.addEventListener('input', function () {
            if (el.value.trim()) el.classList.remove('invalid');
        });
    });

    // Modal open/close events
    document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
        btn.addEventListener('click', function () { openModal(btn.getAttribute('data-modal-open')); });
    });
    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', function () { closeModal(btn.getAttribute('data-modal-close')); });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.open').forEach(function (m) {
                m.classList.remove('open'); document.body.style.overflow = '';
            });
        }
    });

    // ========== GESTIONARE EXERCIȚII + SETURI ==========
    var exercisesContainer = document.getElementById('exercisesContainer');
    var addExerciseBtn = document.getElementById('addExerciseBtn');

    function createSetRow(exerciseIndex, setNumber) {
        var row = document.createElement('div');
        row.className = 'set-row';
        row.setAttribute('data-set-index', setNumber);
        row.innerHTML = `
            <span class="set-label">Set ${setNumber + 1}</span>
            <input type="number" name="exercises[${exerciseIndex}][sets][${setNumber}][reps]" placeholder="10" min="1" value="10" required>
            <input type="number" name="exercises[${exerciseIndex}][sets][${setNumber}][weight]" placeholder="0" min="0" step="0.5" value="0">
            <button type="button" class="btn-remove-set" title="Șterge set">✕</button>
        `;
        return row;
    }

    function createExerciseBlock(exerciseIndex) {
        var block = document.createElement('div');
        block.className = 'exercise-block';
        block.setAttribute('data-ex-index', exerciseIndex);

        var header = document.createElement('div');
        header.className = 'exercise-header';
        header.innerHTML = `
            <input type="text" name="exercises[${exerciseIndex}][name]" class="exercise-name-input" placeholder="Ex: Bench Press" required>
            <button type="button" class="btn-remove-exercise" title="Șterge exercițiu">✕</button>
        `;

        var setsContainer = document.createElement('div');
        setsContainer.className = 'sets-container';
        var setsHeader = document.createElement('div');
        setsHeader.className = 'sets-header';
        setsHeader.innerHTML = '<span></span><span>Repetări</span><span>Greutate (kg)</span><span></span>';
        setsContainer.appendChild(setsHeader);
        setsContainer.appendChild(createSetRow(exerciseIndex, 0));

        var addSetBtn = document.createElement('button');
        addSetBtn.type = 'button';
        addSetBtn.className = 'btn-add-set';
        addSetBtn.textContent = '+ Adaugă set';
        addSetBtn.addEventListener('click', function () {
            var currentExIdx = parseInt(block.getAttribute('data-ex-index'));
            var currentSetCount = setsContainer.querySelectorAll('.set-row').length;
            var newRow = createSetRow(currentExIdx, currentSetCount);
            setsContainer.appendChild(newRow);
            attachRemoveSetEvent(newRow);
            reindexAllExercises();
        });

        block.appendChild(header);
        block.appendChild(setsContainer);
        block.appendChild(addSetBtn);
        return block;
    }

    function attachRemoveSetEvent(setRow) {
        var removeBtn = setRow.querySelector('.btn-remove-set');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                var container = setRow.closest('.sets-container');
                if (container.querySelectorAll('.set-row').length <= 1) return;
                setRow.remove();
                reindexAllExercises();
            });
        }
    }

    function attachRemoveExerciseEvent(block) {
        var removeBtn = block.querySelector('.btn-remove-exercise');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                if (exercisesContainer.children.length <= 1) return;
                block.remove();
                reindexAllExercises();
            });
        }
    }

    function reindexAllExercises() {
        var blocks = exercisesContainer.querySelectorAll('.exercise-block');
        blocks.forEach(function (block, exIdx) {
            block.setAttribute('data-ex-index', exIdx);
            var nameInput = block.querySelector('.exercise-name-input');
            if (nameInput) nameInput.name = `exercises[${exIdx}][name]`;

            var setRows = block.querySelectorAll('.set-row');
            setRows.forEach(function (row, setIdx) {
                row.setAttribute('data-set-index', setIdx);
                var label = row.querySelector('.set-label');
                if (label) label.textContent = `Set ${setIdx + 1}`;
                var inputs = row.querySelectorAll('input');
                if (inputs[0]) inputs[0].name = `exercises[${exIdx}][sets][${setIdx}][reps]`;
                if (inputs[1]) inputs[1].name = `exercises[${exIdx}][sets][${setIdx}][weight]`;
            });
        });
    }

    function initExercisesContainer() {
        exercisesContainer.innerHTML = '';
        var firstBlock = createExerciseBlock(0);
        exercisesContainer.appendChild(firstBlock);
        attachRemoveExerciseEvent(firstBlock);
        var firstSetRow = firstBlock.querySelector('.set-row');
        if (firstSetRow) attachRemoveSetEvent(firstSetRow);
    }

    if (exercisesContainer && addExerciseBtn) {
        initExercisesContainer();
        addExerciseBtn.addEventListener('click', function () {
            var newIdx = exercisesContainer.children.length;
            var newBlock = createExerciseBlock(newIdx);
            exercisesContainer.appendChild(newBlock);
            attachRemoveExerciseEvent(newBlock);
            newBlock.querySelectorAll('.set-row').forEach(function (row) {
                attachRemoveSetEvent(row);
            });
            reindexAllExercises();
        });
    }
});

// ========== SMOOTH NAVIGATION & LOADING ==========
(function () {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loadingOverlay);

    function smoothNavigate(url) {
        if (!url || url.startsWith('javascript:') || url.startsWith('#') || url.startsWith('mailto:')) {
            return true;
        }
        if (url === window.location.pathname || url === window.location.href) {
            return false;
        }
        event.preventDefault();
        document.body.classList.add('page-fade-out');
        loadingOverlay.classList.add('active');
        setTimeout(() => {
            window.location.href = url;
        }, 180);
        return false;
    }

    document.querySelectorAll('a[href]').forEach(link => {
        const href = link.getAttribute('href');
        if (!href) return;
        if (href.startsWith('/') || href.startsWith('./') || href.startsWith('../') ||
            (href.startsWith(window.location.origin)) ||
            (!href.startsWith('http') && !href.startsWith('//') && !href.startsWith('#'))) {
            link.addEventListener('click', function (e) {
                let targetUrl = href;
                if (targetUrl.startsWith('./')) targetUrl = targetUrl.substring(1);
                if (!targetUrl.startsWith('/') && !targetUrl.includes('://')) {
                    targetUrl = '/' + targetUrl;
                }
                e.preventDefault();
                smoothNavigate(targetUrl);
            });
        }
    });

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function () {
            loadingOverlay.classList.add('active');
            document.body.classList.add('page-fade-out');
        });
    });

    window.addEventListener('pageshow', function () {
        document.body.classList.remove('page-fade-out');
        loadingOverlay.classList.remove('active');
    });
})();

// ========== LANDING PAGE ANIMATIONS ==========
(function () {
    const animatedElements = document.querySelectorAll('.lp-hero-left, .lp-hero-right, .lp-feature-card, .lp-stat-item, .lp-cta');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2, rootMargin: '0px 0px -20px 0px' });
    animatedElements.forEach(el => observer.observe(el));

    const statNumbers = document.querySelectorAll('.lp-stat-num');
    const animateNumber = (el, target) => {
        let current = 0;
        const step = Math.ceil(target / 40);
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                el.textContent = target.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (el.textContent.includes('+') ? '+' : '');
                clearInterval(timer);
            } else {
                el.textContent = current + (el.textContent.includes('+') ? '+' : '');
            }
        }, 25);
    };
    const statObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const raw = el.getAttribute('data-target') || el.textContent.replace(/[^0-9]/g, '');
                const target = parseInt(raw, 10);
                if (!isNaN(target)) {
                    animateNumber(el, target);
                }
                statObserver.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    statNumbers.forEach(el => {
        const raw = el.textContent.replace(/[^0-9]/g, '');
        el.setAttribute('data-target', raw);
        el.textContent = '0';
        statObserver.observe(el);
    });
})();