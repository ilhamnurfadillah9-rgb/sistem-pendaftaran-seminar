document.addEventListener('DOMContentLoaded', () => {
    const originalSubmitContent = new WeakMap();

    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented || !(event.target instanceof HTMLFormElement)) {
            return;
        }

        const form = event.target;

        if (form.method.toUpperCase() === 'GET' || form.dataset.submitting === 'true') {
            return;
        }

        const submitButton = event.submitter instanceof HTMLButtonElement
            ? event.submitter
            : form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');

        if (!(submitButton instanceof HTMLButtonElement) || submitButton.disabled) {
            return;
        }

        form.dataset.submitting = 'true';
        originalSubmitContent.set(submitButton, submitButton.innerHTML);
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitButton.innerHTML = '<span class="form-submit-spinner" aria-hidden="true"></span><span>Memproses...</span>';
    });

    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) {
            return;
        }

        document.querySelectorAll('form[data-submitting="true"]').forEach((form) => {
            form.removeAttribute('data-submitting');
            const submitButton = form.querySelector('button[aria-busy="true"]');

            if (submitButton instanceof HTMLButtonElement) {
                submitButton.disabled = false;
                submitButton.removeAttribute('aria-busy');
                submitButton.innerHTML = originalSubmitContent.get(submitButton) || submitButton.innerHTML;
            }
        });
    });

    const flyerInput = document.querySelector('[data-flyer-input]');
    const flyerPreview = document.querySelector('[data-flyer-preview]');
    const flyerPreviewLabel = document.querySelector('[data-flyer-preview-label]');

    if (flyerInput && flyerPreview) {
        let previewUrl = null;

        flyerInput.addEventListener('change', () => {
            const [file] = flyerInput.files;

            if (!file) {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }

                return;
            }

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }

            previewUrl = URL.createObjectURL(file);
            flyerPreview.src = previewUrl;
            flyerPreviewLabel.textContent = file.name;
        });
    }

    const deleteModal = document.querySelector('#delete-seminar-modal');

    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;

            if (!(trigger instanceof HTMLElement)) {
                return;
            }

            deleteModal.querySelector('[data-delete-seminar-name]').textContent = trigger.dataset.seminarTitle || '';
            deleteModal.querySelector('[data-delete-seminar-form]').action = trigger.dataset.deleteUrl || '';
        });
    }

    const deleteParticipantModal = document.querySelector('#delete-participant-modal');

    if (deleteParticipantModal) {
        deleteParticipantModal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;

            if (!(trigger instanceof HTMLElement)) {
                return;
            }

            deleteParticipantModal.querySelector('[data-delete-participant-name]').textContent = trigger.dataset.participantName || '';
            deleteParticipantModal.querySelector('[data-delete-participant-form]').action = trigger.dataset.deleteUrl || '';
        });
    }

    const participantQrModal = document.querySelector('#participant-qr-modal');

    if (participantQrModal) {
        participantQrModal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;

            if (!(trigger instanceof HTMLElement)) {
                return;
            }

            const qrImage = participantQrModal.querySelector('[data-participant-qr]');
            qrImage.src = trigger.dataset.qrUrl || '';
            qrImage.alt = `QR peserta ${trigger.dataset.participantName || ''}`;
            participantQrModal.querySelector('[data-participant-confirmation]').href =
                trigger.dataset.confirmationUrl || '';
        });

        participantQrModal.addEventListener('hidden.bs.modal', () => {
            participantQrModal.querySelector('[data-participant-qr]').removeAttribute('src');
        });
    }

    const attendanceScanner = document.querySelector('[data-attendance-scanner]');

    if (attendanceScanner) {
        const scanForm = attendanceScanner.querySelector('[data-attendance-scan-form]');
        const seminarSelect = attendanceScanner.querySelector('[data-scan-seminar]');
        const tokenInput = attendanceScanner.querySelector('[data-scan-token]');
        const resultCard = attendanceScanner.querySelector('[data-scan-result]');
        const resultIcon = attendanceScanner.querySelector('[data-result-icon]');
        const resultMessage = attendanceScanner.querySelector('[data-result-message]');
        const resultName = attendanceScanner.querySelector('[data-result-name]');
        const resultSeminar = attendanceScanner.querySelector('[data-result-seminar]');
        const cameraButton = attendanceScanner.querySelector('[data-start-camera]');
        const cameraMessage = attendanceScanner.querySelector('[data-camera-message]');
        const reader = attendanceScanner.querySelector('[data-qr-reader]');
        const attendanceListLink = document.querySelector('[data-attendance-list-link]');
        let isProcessing = false;
        let lastScannedToken = '';
        let lastScanAt = 0;

        if (attendanceListLink) {
            seminarSelect.addEventListener('change', () => {
                attendanceListLink.href = attendanceListLink.dataset.attendanceListUrl
                    .replace('__seminar__', encodeURIComponent(seminarSelect.value));
            });
        }

        const showResult = (message, participant = null) => {
            const isSuccess = participant !== null;

            resultCard.hidden = false;
            resultCard.classList.toggle('attendance-scan-result--success', isSuccess);
            resultCard.classList.toggle('attendance-scan-result--error', !isSuccess);
            resultIcon.innerHTML = isSuccess
                ? '<i class="bi bi-check-circle-fill"></i>'
                : '<i class="bi bi-exclamation-circle-fill"></i>';
            resultMessage.textContent = message;
            resultName.hidden = !isSuccess;
            resultSeminar.hidden = !isSuccess;

            if (isSuccess) {
                resultName.textContent = participant.nama;
                resultSeminar.textContent = participant.seminar;
            }
        };

        const submitToken = async (token) => {
            const normalizedToken = token.trim();
            const currentTime = Date.now();

            if (
                normalizedToken === ''
                || isProcessing
                || (normalizedToken === lastScannedToken && currentTime - lastScanAt < 2000)
            ) {
                return;
            }

            isProcessing = true;
            lastScannedToken = normalizedToken;
            lastScanAt = currentTime;

            const submitButton = scanForm.querySelector('[type="submit"]');
            const originalSubmitContent = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            submitButton.innerHTML = '<span class="form-submit-spinner" aria-hidden="true"></span><span>Memproses...</span>';

            try {
                const response = await fetch(scanForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        token: normalizedToken,
                        seminar_id: seminarSelect.value,
                    }),
                });
                const result = await response.json();

                if (!response.ok || !result.success) {
                    const validationMessage = Object.values(result.errors || {}).flat()[0];
                    showResult(result.message || validationMessage || 'Kode QR tidak dapat diproses.');

                    return;
                }

                showResult(result.message, result.participant);

                if (tokenInput.value.trim() === normalizedToken) {
                    tokenInput.value = '';
                }
            } catch {
                showResult('Tidak dapat menghubungi server. Periksa koneksi lalu coba lagi.');
            } finally {
                window.setTimeout(() => {
                    isProcessing = false;
                    submitButton.disabled = false;
                    submitButton.removeAttribute('aria-busy');
                    submitButton.innerHTML = originalSubmitContent;
                }, 900);
            }
        };

        scanForm.addEventListener('submit', (event) => {
            event.preventDefault();
            submitToken(tokenInput.value);
        });

        cameraButton.addEventListener('click', async () => {
            const isLocalhost = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname);

            if (!window.isSecureContext && !isLocalhost) {
                cameraMessage.textContent = 'Kamera membutuhkan HTTPS. Masukkan token secara manual.';
                return;
            }

            if (!navigator.mediaDevices?.getUserMedia || typeof Html5Qrcode === 'undefined') {
                cameraMessage.textContent = 'Pemindai kamera tidak tersedia. Masukkan token secara manual.';
                return;
            }

            cameraButton.disabled = true;
            cameraMessage.textContent = 'Meminta akses kamera...';

            try {
                const scanner = new Html5Qrcode(reader.id);

                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 230, height: 230 } },
                    (decodedToken) => submitToken(decodedToken),
                    () => {},
                );

                cameraMessage.textContent = 'Kamera aktif. Arahkan QR ke bingkai.';
                cameraButton.hidden = true;
            } catch {
                cameraButton.disabled = false;
                cameraMessage.textContent = 'Kamera tidak dapat diakses. Periksa izin kamera atau masukkan token manual.';
            }
        });
    }
});
