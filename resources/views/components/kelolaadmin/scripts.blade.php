<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const htmlEl = document.documentElement;
    const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function handleThemeSync(theme) {
        htmlEl.setAttribute('data-theme', theme);
        if (theme === 'dark') {
            htmlEl.classList.add('dark');
        } else {
            htmlEl.classList.remove('dark');
        }
    }
    window.addEventListener('themeChanged', (e) => handleThemeSync(e.detail.theme));
    handleThemeSync(localStorage.getItem('theme') || 'dark');

    let localAdminsStore = [];
    let currentActiveAdminUsername = ''; 
    let originalSelectedData = null; 

    // STATE PAGINASI 
    let currentPage = 1;
    const rowsPerPage = 10;

    function toggleGlobalLoader(show) {
        const loader = document.getElementById('global-loader');
        if (loader) {
            if (show) {
                loader.classList.remove('hidden');
                loader.classList.add('flex');
            } else {
                loader.classList.remove('flex');
                loader.classList.add('hidden');
            }
        }
    }

    async function fetchAdminData() {
        const tbody = document.getElementById('admin-table-body');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-8 py-10 text-center text-slate-400 dark:text-slate-500 italic">Syncing Team Data...</td></tr>';
        }
            
        try {
            const response = await fetch("{{ route('admin.manage.list') }}"); 
            const data = await response.json();
                
            if (data && data.admins) {
                localAdminsStore = data.admins;
                currentActiveAdminUsername = data.current_username || ''; 
                    
                animateValue('total-admins', parseInt(document.getElementById('total-admins').innerText) || 0, localAdminsStore.length, 800);
                    
                const currentMonth = new Date().getMonth();
                const currentYear = new Date().getFullYear();
                const filterNewAdmins = localAdminsStore.filter(admin => {
                    const adminDate = new Date(admin.created_at);
                    return adminDate.getMonth() === currentMonth && adminDate.getFullYear() === currentYear;
                }).length;
                animateValue('new-admins', parseInt(document.getElementById('new-admins').innerText) || 0, filterNewAdmins, 800);
                    
                animateValue('active-admins', parseInt(document.getElementById('active-admins').innerText) || 0, 1, 800);

                currentPage = 1;
                renderTable(localAdminsStore);
            }
        } catch (err) {
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-8 py-10 text-center text-rose-500 font-semibold uppercase text-xs tracking-wider">Gagal sinkronisasi data admin.</td></tr>';
            }
            AppAlert.fire('error', 'SISTEM TIMEOUT', 'Gagal memuat atau menyinkronkan data tim admin.');
        }
    }

    function renderTable(admins) {
        const tbody = document.getElementById('admin-table-body');
        const cardsContainer = document.getElementById('admin-cards-container');
        if (!tbody) return;

        if (admins.length === 0) {
            const emptyState = '<tr><td colspan="5" class="px-8 py-16 text-center text-slate-400 dark:text-slate-500 italic">Tidak ada data admin tambahan.</td></tr>';
            tbody.innerHTML = emptyState;
            if (cardsContainer) cardsContainer.innerHTML = '<div class="text-center text-slate-400 dark:text-slate-500 italic py-8">Tidak ada data admin tambahan.</div>';
            setupPaginationControls(0);
            return;
        }

        const reversedAdmins = admins.slice().reverse();

        const indexOfLastRow = currentPage * rowsPerPage;
        const indexOfFirstRow = indexOfLastRow - rowsPerPage;
        const currentRows = reversedAdmins.slice(indexOfFirstRow, indexOfLastRow);

        let tableHtml = '';
        let cardsHtml = '';

        currentRows.forEach((admin, index) => {
            const rowDelay = index * 40;
            const formattedDate = new Date(admin.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
            const originalIndex = admins.findIndex(a => a.id_admin === admin.id_admin);
            const displayIndex = indexOfFirstRow + index + 1;

            let actionButtons = '';
            if (admin.username === currentActiveAdminUsername) {
                actionButtons = `
                    <button onclick="openEditModalByIndex(${originalIndex})" class="text-slate-400 hover:text-indigo-400 transition-colors cursor-pointer" title="Edit Akun Saya">
                        <i class="fa-solid fa-user-pen text-sm md:text-xs"></i>
                    </button>
                `;
            } else {
                actionButtons = `
                    <button onclick="openEditModalByIndex(${originalIndex})" class="text-slate-400 hover:text-indigo-400 transition-colors cursor-pointer">
                        <i class="fa-solid fa-user-pen text-sm md:text-xs"></i>
                    </button>
                    <button onclick="deleteAdmin(${admin.id_admin}, '${admin.username}')" class="text-slate-400 hover:text-rose-500 transition-colors cursor-pointer">
                        <i class="fa-solid fa-trash-can text-sm md:text-xs"></i>
                    </button>
                `;
            }

            tableHtml += `
                <tr class="hover:bg-white/[0.03] border-b border-slate-200 dark:border-white/5 transition-colors animate__animated animate__fadeInUp text-slate-700 dark:text-slate-200" style="animation-delay: ${rowDelay}ms">
                    <td class="px-6 py-5 text-center">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full  text-slate-400 font-bold text-xs">
                            ${displayIndex}
                        </span>
                    </td>
                    <td class="px-6 py-5 text-center">
                        <span class="bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-3 py-1.5 rounded-lg font-bold text-[11px] border border-indigo-500/10">${admin.username}</span>
                    </td>
                    <td class="px-8 py-5 text-center font-mono text-xs text-slate-600 dark:text-slate-300">
                        ${admin.email}
                    </td>
                    <td class="px-8 py-5 text-center font-semibold text-xs text-slate-400">
                        ${formattedDate}
                    </td>
                    <td class="px-8 py-5 text-right space-x-2">
                        <div class="space-x-2">
                            ${actionButtons}
                        </div>
                    </td>
                </tr>`;

            cardsHtml += `
                <div class="glass glass-card-mobile p-5 mb-4 rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 shadow-sm inline-block w-full text-left animate__animated animate__fadeInUp" style="animation-delay: ${rowDelay}ms;">
        <div class="flex justify-between items-center border-b border-slate-100 dark:border-white/5 pb-3 mb-3">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 dark:bg-white/5 text-slate-400 font-bold text-xs">
                ${displayIndex}
            </span>
            <div class="flex space-x-3">
                ${actionButtons}
            </div>
        </div>
        <div class="space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Username</span>
                <span class="bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded-md font-bold text-[11px]">${admin.username}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Email</span>
                <span class="font-mono text-slate-600 dark:text-slate-300 break-all pl-4 text-right">${admin.email}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Join Date</span>
                <span class="text-slate-500 font-medium">${formattedDate}</span>
            </div>
        </div>
    </div>`;
        });

        tbody.innerHTML = tableHtml;
        if (cardsContainer) {
            cardsContainer.innerHTML = cardsHtml;
        }

        setupPaginationControls(admins.length);
    }

    function setupPaginationControls(totalItems) {
        const startEl = document.getElementById('pagination-start');
        const endEl = document.getElementById('pagination-end');
        const totalEl = document.getElementById('pagination-total');
        const pagesContainer = document.getElementById('pagination-pages');
        const prevBtn = document.getElementById('pagination-prev');
        const nextBtn = document.getElementById('pagination-next');

        if (startEl && endEl && totalEl) {
            const startRange = totalItems === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
            const endRange = Math.min(currentPage * rowsPerPage, totalItems);
            
            startEl.innerText = startRange;
            endEl.innerText = endRange;
            totalEl.innerText = totalItems;
        }

        const totalPages = Math.ceil(totalItems / rowsPerPage);

        if (prevBtn) {
            if (currentPage === 1 || totalPages <= 1) {
                prevBtn.setAttribute('disabled', 'true');
                prevBtn.classList.add('opacity-40', 'cursor-not-allowed');
            } else {
                prevBtn.removeAttribute('disabled');
                prevBtn.classList.remove('opacity-40', 'cursor-not-allowed');
            }
            prevBtn.onclick = () => changePage(currentPage - 1);
        }

        if (nextBtn) {
            if (currentPage === totalPages || totalPages <= 1) {
                nextBtn.setAttribute('disabled', 'true');
                nextBtn.classList.add('opacity-40', 'cursor-not-allowed');
            } else {
                nextBtn.removeAttribute('disabled');
                nextBtn.classList.remove('opacity-40', 'cursor-not-allowed');
            }
            nextBtn.onclick = () => changePage(currentPage + 1);
        }

        if (pagesContainer) {
            pagesContainer.innerHTML = '';
            if (totalPages <= 1) return;

            let buttonsHtml = '';
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    buttonsHtml += `
                        <button type="button" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white shadow-md shadow-indigo-600/20 transition-all">
                            ${i}
                        </button>
                    `;
                } else {
                    buttonsHtml += `
                        <button type="button" onclick="changePage(${i})" 
                            class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/5 text-xs font-semibold bg-white dark:bg-white/5 text-slate-600 dark:text-slate-300 hover:border-indigo-500/30 dark:hover:border-indigo-500/30 hover:text-indigo-500 dark:hover:text-indigo-400 transition-all cursor-pointer">
                            ${i}
                        </button>
                    `;
                }
            }
            pagesContainer.innerHTML = buttonsHtml;
        }
    }

    function changePage(page) {
        const totalPages = Math.ceil(localAdminsStore.length / rowsPerPage);
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderTable(localAdminsStore);

        if (window.innerWidth < 768) {
            const mainCardContainer = document.querySelector('table')?.closest('.rounded-2xl') || document.getElementById('admin-table-body');
            if (mainCardContainer) {
                mainCardContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    function animateValue(id, start, end, duration, suffix = '') {
        const obj = document.getElementById(id);
        if (!obj || start === end) { if(obj) obj.innerHTML = end.toLocaleString() + suffix; return; }
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.innerHTML = Math.floor(progress * (end - start) + start).toLocaleString() + suffix;
            if (progress < 1) window.requestAnimationFrame(step);
        };
        window.requestAnimationFrame(step);
    }

    function validateField(fieldId) {
        const el = document.getElementById(fieldId);
        const errEl = document.getElementById(`error-${fieldId}`);
        if (!el || !errEl) return true;

        let isValid = true;
        const value = el.value.trim();
        const id = document.getElementById('admin-id').value;
        const isEdit = id !== '';

        if (fieldId === 'username') {
            isValid = value !== '';
        } else if (fieldId === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            isValid = emailRegex.test(value);
        } else if (fieldId === 'password') {
            if (!isEdit) {
                isValid = value.length >= 6;
            }
        } else if (isEdit && (fieldId === 'old_password' || fieldId === 'new_password' || fieldId === 'confirm_password')) {
            const oldVal = document.getElementById('old_password').value;
            const newVal = document.getElementById('new_password').value;
            const confVal = document.getElementById('confirm_password').value;
                
            if (oldVal || newVal || confVal) {
                if (fieldId === 'old_password') {
                    isValid = oldVal.length > 0;
                } else if (fieldId === 'new_password') {
                    isValid = newVal.length >= 6;
                } else if (fieldId === 'confirm_password') {
                    isValid = confVal === newVal && confVal.length >= 6;
                }
            } else {
                ['old_password', 'new_password', 'confirm_password'].forEach(i => {
                    const targetEl = document.getElementById(i);
                    const targetErr = document.getElementById(`error-${i}`);
                    if (targetErr) {
                        targetErr.classList.add('hidden');
                        targetErr.classList.remove('flex');
                    }
                    if (targetEl) {
                        targetEl.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500/20');
                    }
                });
                return true;
            }
        }

        if (!isValid) {
            errEl.classList.remove('hidden');
            errEl.classList.add('flex');
            el.classList.add('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500/20');
        } else {
            errEl.classList.add('hidden');
            errEl.classList.remove('flex');
            el.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500/20');
        }

        return isValid;
    }

    function clearValidationState() {
        ['username', 'email', 'password', 'old_password', 'new_password', 'confirm_password'].forEach(id => {
            const el = document.getElementById(id);
            const errEl = document.getElementById(`error-${id}`);
            if (errEl) {
                errEl.classList.add('hidden');
                errEl.classList.remove('flex');
            }
            if (el) {
                el.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500/20');
            }
        });
    }

    function togglePasswordVisibility(inputId, buttonEl) {
        const input = document.getElementById(inputId);
        const icon = buttonEl.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function resetPasswordInputsType() {
        ['password', 'old_password', 'new_password', 'confirm_password'].forEach(id => {
            const input = document.getElementById(id);
            if (input) input.type = 'password';
        });
        document.querySelectorAll('#admin-form button[type="button"] i').forEach(icon => {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        });
    }

    // Modal Operations
    const modal = document.getElementById('admin-modal');
    const modalCard = document.getElementById('modal-card');

    function openAddModal() {
        document.getElementById('admin-form').reset();
        document.getElementById('admin-id').value = '';
        document.getElementById('modal-title').innerText = 'TAMBAH ADMIN BARU';
            
        document.getElementById('create-password-wrapper').classList.remove('hidden');
        document.getElementById('edit-password-group').classList.add('hidden');
            
        originalSelectedData = null; 
        clearValidationState();
        resetPasswordInputsType();

        modal.classList.remove('hidden', 'animate__fadeOut');
        modal.classList.add('flex', 'animate__fadeIn', 'animate__faster');
            
        cardModal = document.getElementById('modal-card');
        cardModal.classList.remove('animate__zoomOut');
        cardModal.classList.add('animate__zoomIn', 'animate__faster');
    }

    function openEditModalByIndex(index) {
        const admin = localAdminsStore[index];
        if (!admin) return;

        document.getElementById('admin-form').reset();
        document.getElementById('admin-id').value = admin.id_admin;
        document.getElementById('username').value = admin.username;
        document.getElementById('email').value = admin.email;
        document.getElementById('modal-title').innerText = 'EDIT DATA ADMINISTRATOR';
            
        document.getElementById('create-password-wrapper').classList.add('hidden');
        document.getElementById('edit-password-group').classList.remove('hidden');
            
        originalSelectedData = {
            username: admin.username,
            email: admin.email
        };

        clearValidationState();
        resetPasswordInputsType();

        modal.classList.remove('hidden', 'animate__fadeOut');
        modal.classList.add('flex', 'animate__fadeIn', 'animate__faster');
            
        modalCard.classList.remove('animate__zoomOut');
        modalCard.classList.add('animate__zoomIn', 'animate__faster');
    }

    function closeModal() {
        if (!modal || !modalCard) return;
        modal.classList.remove('animate__fadeIn');
        modal.classList.add('animate__fadeOut');
            
        modalCard.classList.remove('animate__zoomIn');
        modalCard.classList.add('animate__zoomOut');

        const animationHandler = () => {
            modal.classList.remove('flex', 'animate__fadeOut');
            modal.classList.add('hidden');
            document.getElementById('password').value = '';
            document.getElementById('old_password').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            modal.removeEventListener('animationend', animationHandler);
        };

        modal.addEventListener('animationend', animationHandler);
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
            
        const id = document.getElementById('admin-id').value;
        const isEdit = id !== '';
            
        const currentUsername = document.getElementById('username').value.trim();
        const currentEmail = document.getElementById('email').value.trim();
            
        const oldPass = document.getElementById('old_password').value;
        const newPass = document.getElementById('new_password').value;
        const confPass = document.getElementById('confirm_password').value;

        if (isEdit && originalSelectedData) {
            if (currentUsername === originalSelectedData.username && 
                currentEmail === originalSelectedData.email && 
                !oldPass && !newPass && !confPass) {
                    
                closeModal();
                AppAlert.fire('info', 'TIDAK ADA PERUBAHAN', 'Tidak ada data admin yang diubah atau diperbarui.');
                return;
            }
        }

        // Validasi Input
        const isUsernameValid = validateField('username');
        const isEmailValid = validateField('email');
            
        let isPasswordValid = true;
        if (!isEdit) {
            isPasswordValid = validateField('password');
        } else {
            isPasswordValid = validateField('old_password') && validateField('new_password') && validateField('confirm_password');
        }

        if (!isUsernameValid || !isEmailValid || !isPasswordValid) {
            AppAlert.fire('error', 'VALIDASI GAGAL', 'Harap periksa kembali isian form Anda sebelum menyimpan data.');
            return;
        }

        let confirmationTitle = 'KONFIRMASI SIMPAN';
        let confirmationHtml = `Apakah Anda yakin ingin mendaftarkan administrator baru ke dalam sistem?`;

        if (isEdit) {
            confirmationTitle = 'PERBARUI PROFIL';
            let passwordWarning = '';

            if (oldPass || newPass || confPass) {
                passwordWarning = `
                    <div class="mt-3 p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-left">
                        <span class="text-rose-500 font-bold block text-xs uppercase mb-1">⚠️ PERINGATAN KEAMANAN:</span>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Anda mendeteksi adanya pembaruan kata sandi. Pastikan Anda mengingat password baru ini. Sesi login pada perangkat lain mungkin akan diminta untuk melakukan re-autentikasi demi keamanan.
                        </p>
                    </div>
                `;
            }

            confirmationHtml = `
                <div class="text-slate-300 text-sm">
                    Sistem akan memperbarui informasi profil akun admin. Pastikan data yang dimasukkan sudah valid dan sesuai prosedur operasional.
                    ${passwordWarning}
                </div>
            `;
        }

        AppAlert.fire('warning', confirmationTitle, `
            ${confirmationHtml}
            <div class="flex flex-row justify-center gap-3 mt-6 w-full">
                <button id="btn-confirm-save" class="flex-1 px-4 py-2.5 rounded-xl font-black text-[11px] uppercase tracking-widest bg-indigo-600 text-white active:scale-95 transition-all cursor-pointer whitespace-nowrap">
                    YA, SIMPAN DATA
                </button>
                <button id="btn-cancel-save" class="flex-1 px-4 py-2.5 rounded-xl font-black text-[11px] uppercase tracking-widest bg-slate-700 text-slate-200 active:scale-95 transition-all cursor-pointer whitespace-nowrap">
                    BATAL
                </button>
            </div>
        `);

        Swal.stopTimer();
        if (Swal.getTimerProgressBar()) Swal.getTimerProgressBar().style.display = 'none';
        if (Swal.getConfirmButton()) Swal.getConfirmButton().style.display = 'none';

        document.getElementById('btn-cancel-save').addEventListener('click', () => Swal.close());
        document.getElementById('btn-confirm-save').addEventListener('click', async () => {
            Swal.close(); 
            toggleGlobalLoader(true);

            const payload = {
                username: currentUsername,
                email: currentEmail,
            };
                
            if (!isEdit) {
                payload.password = document.getElementById('password').value;
            } else {
                if (newPass) {
                    payload.old_password = oldPass;
                    payload.password = newPass;
                    payload.password_confirmation = confPass;
                }
            }

            const url = isEdit 
                ? "{{ route('admin.manage.update', ':id') }}".replace(':id', id) 
                : "{{ route('admin.manage.store') }}";

            try {
                const response = await fetch(url, {
                    method: 'POST', 
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const res = await response.json();
                toggleGlobalLoader(false);

                if (res.status === 'success') {
                    closeModal();
                    AppAlert.fire('success', 'BERHASIL', isEdit ? 'Data administrator berhasil diperbarui.' : 'Akun admin baru berhasil didaftarkan.');
                    fetchAdminData();
                } else {
                    AppAlert.fire('error', 'PROSES GAGAL', res.message || 'Terjadi kesalahan sistem internal.');
                }
            } catch (err) {
                toggleGlobalLoader(false);
                AppAlert.fire('error', 'SISTEM ERROR', 'Gagal memproses pengiriman data ke server.');
            }
        });
    }

    // Delete Action
    function deleteAdmin(id_admin, username) {
        AppAlert.fire('warning', 'REVOKE ACCESS', `
            Akses akun resmi untuk admin <span class="text-red-400 font-bold">"${username}"</span> akan dicabut dari sistem.
            <div class="flex flex-row justify-center gap-3 mt-6 w-full">
                <button id="btn-confirm-delete" class="flex-1 px-4 py-2.5 rounded-xl font-black text-[11px] uppercase tracking-widest bg-red-500 text-white active:scale-95 transition-all cursor-pointer whitespace-nowrap">
                    YA, CABUT AKSES
                </button>
                <button id="btn-cancel-delete" class="flex-1 px-4 py-2.5 rounded-xl font-black text-[11px] uppercase tracking-widest bg-slate-700 text-slate-200 active:scale-95 transition-all cursor-pointer whitespace-nowrap">
                    BATAL
                </button>
            </div>
        `);

        Swal.stopTimer(); 
        if (Swal.getTimerProgressBar()) Swal.getTimerProgressBar().style.display = 'none';
        if (Swal.getConfirmButton()) Swal.getConfirmButton().style.display = 'none';

        document.getElementById('btn-confirm-delete').addEventListener('click', function() {
            Swal.close();
            toggleGlobalLoader(true);

            fetch(`/admin/kelola-admin/delete/${id_admin}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                toggleGlobalLoader(false);

                if (data.status === 'success') {
                    AppAlert.fire('success', 'BERHASIL', data.message);
                    fetchAdminData(); 
                } else {
                    AppAlert.fire('error', 'GAGAL', data.message);
                }
            })
            .catch(error => {
                toggleGlobalLoader(false);
                console.error('Error:', error);
                AppAlert.fire('error', 'SYSTEM ERROR', 'Gagal terhubung ke server.');
            });
        });

        document.getElementById('btn-cancel-delete').addEventListener('click', function() {
            Swal.close();
        });
    }

    window.onload = fetchAdminData;
</script>