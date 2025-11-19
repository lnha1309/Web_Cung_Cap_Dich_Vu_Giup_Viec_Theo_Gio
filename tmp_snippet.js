
                if (bookerInfo) {
                    bookerInfo.style.display = 'flex';
                }
                if (workloadInfo) {
                    workloadInfo.style.display = 'block';
                }
                if (priceCard) {
                    priceCard.style.display = 'none';
                }
                if (voucherCard) {
                    voucherCard.classList.add('show');
                }

                const totalHours = (window.selectedDuration || 0) + (Array.isArray(window.selectedExtraTasks) ? window.selectedExtraTasks.length : 0);
                const timeInput = document.getElementById('startTime');
                const time = timeInput ? (timeInput.value || '07:00') : '07:00';
                const workloadValue = document.getElementById('workloadValue');
                if (workloadValue) {
                    workloadValue.textContent = `${totalHours} giờ @ ${time}`;
                }

                if (chosen) {
                    const profile = document.querySelector('.worker-profile-section');
                    if (profile) {
