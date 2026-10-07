<div class="ku-wrap">

    <div class="ku-head">
        <div>
            <div class="ku-kicker">ADMINISTRASI</div>
            <h2>Kelola User</h2>
            <p>Kelola akun pengguna Telkom Fallout System.</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="ku-add-btn">
            + Tambah User
        </a>
    </div>


    {{-- =========================================================
         UI ALERT / CONFIRM
         Sama konsepnya dengan Upload Data
         ========================================================= --}}
    <div
        class="ku-ui-alert-overlay"
        id="kuUiAlertOverlay"
        aria-hidden="true"
    >
        <div
            class="ku-ui-alert-card"
            role="dialog"
            aria-modal="true"
            aria-labelledby="kuUiAlertTitle"
        >

            <div
                class="ku-ui-alert-icon"
                id="kuUiAlertIcon"
            ></div>

            <h3 id="kuUiAlertTitle">
                Informasi
            </h3>

            <div
                class="ku-ui-alert-message"
                id="kuUiAlertMessage"
            ></div>

            <div
                class="ku-ui-alert-actions"
                id="kuUiAlertActions"
            >

                <button
                    type="button"
                    class="ku-ui-alert-btn ku-ui-alert-cancel"
                    id="kuUiAlertCancel"
                >
                    Batal
                </button>

                <button
                    type="button"
                    class="ku-ui-alert-btn ku-ui-alert-confirm"
                    id="kuUiAlertConfirm"
                >
                    OK
                </button>

            </div>

        </div>
    </div>


    <div class="ku-table-card">
        <table class="ku-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="ku-action-head">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td class="ku-name">{{ $user->name }}</td>

                        <td>{{ $user->username }}</td>

                        <td class="ku-nowrap">{{ $user->email }}</td>

                        <td>
                            <span class="ku-role {{ $user->role === 'admin' ? 'ku-role-admin' : 'ku-role-user' }}">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>

                        <td class="ku-action-cell">
                            <div class="ku-actions">

                                {{-- EDIT --}}
                                <a
                                    href="{{ route('admin.users.edit', $user->id) }}"
                                    class="ku-action ku-edit"
                                >
                                    Edit
                                </a>

                                {{-- HAPUS --}}
                                @if($user->id !== auth()->id())
                                    <form
                                        action="{{ route('admin.users.destroy', $user->id) }}"
                                        method="POST"
                                        class="ku-delete-form"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="ku-action ku-delete"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="ku-empty">Belum ada user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .ku-wrap{
        width:100%;
        contain:layout style;
    }

    .ku-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:16px;
        flex-wrap:wrap;
        margin-bottom:24px;
    }

    .ku-kicker{
        font-size:10px;
        font-weight:700;
        letter-spacing:.08em;
        color:rgba(200,16,46,.72);
        margin-bottom:7px;
    }

    .ku-head h2{
        margin:0;
        font-family:'Space Grotesk',sans-serif;
        font-size:24px;
        font-weight:700;
        color:var(--ink);
    }

    .ku-head p{
        margin-top:5px;
        font-size:12.5px;
        color:var(--ink-lo);
    }

    .ku-add-btn{
        display:inline-flex;
        align-items:center;
        gap:7px;
        padding:10px 16px;
        border-radius:10px;
        background:var(--red);
        color:#fff;
        text-decoration:none;
        font-size:12px;
        font-weight:700;
    }

    .ku-table-card{
        background:rgba(255,255,255,.74);
        border:1px solid rgba(255,255,255,.8);
        border-radius:16px;
        overflow:auto;
        contain:layout paint style;
        -webkit-overflow-scrolling:touch;
    }

    .ku-table{
        width:100%;
        min-width:760px;
        border-collapse:collapse;
        table-layout:fixed;
        font-size:12px;
    }

    .ku-table th{
        background:#5C0A1B;
        color:#fff;
        text-align:left;
        padding:12px 14px;
        font-weight:700;
        white-space:nowrap;
    }

    .ku-table td{
        padding:13px 14px;
        border-bottom:1px solid #F1EAE9;
        color:var(--ink);
        vertical-align:middle;
    }

    .ku-table tbody tr:last-child td{
        border-bottom:0;
    }

    .ku-name{
        font-weight:600;
    }

    .ku-nowrap{
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }

    .ku-role{
        display:inline-block;
        padding:4px 9px;
        border-radius:999px;
        font-size:10px;
        font-weight:700;
        white-space:nowrap;
    }

    .ku-role-admin{
        background:rgba(200,16,46,.10);
        color:#C8102E;
    }

    .ku-role-user{
        background:#F0ECEB;
        color:#706669;
    }

    /* =========================================================
       AKSI
       Pastikan Edit dan Hapus selalu terlihat
       ========================================================= */

    .ku-action-head{
        width:150px;
    }

    .ku-action-cell{
        width:150px;
        min-width:150px;
    }

    .ku-actions{
        display:flex;
        gap:7px;
        align-items:center;
        justify-content:flex-start;
        white-space:nowrap;
        min-width:120px;
    }

    .ku-delete-form{
        margin:0;
        padding:0;
        display:flex;
    }

    .ku-action{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:48px;
        padding:7px 10px;
        border-radius:8px;
        font-size:11px;
        font-weight:700;
        text-decoration:none;
        line-height:1;
        flex:none;
    }

    .ku-edit{
        background:#F4E5E8;
        color:#8A0F26;
    }

    .ku-edit:hover{
        background:#ECD9DD;
    }

    .ku-delete{
        border:0;
        background:#FDECEC;
        color:#A61B1B;
        cursor:pointer;
        font-family:'Inter',sans-serif;
    }

    .ku-delete:hover{
        background:#F9DEDE;
    }

    .ku-empty{
        text-align:center;
        padding:35px !important;
        color:var(--ink-lo);
    }


    /* =========================================================
       UI ALERT / CONFIRM
       Sama pola dengan Upload Data
       ========================================================= */

    html.ku-ui-lock,
    body.ku-ui-lock{
        overflow:hidden !important;
    }

    .ku-ui-alert-overlay{
        display:none;
        position:fixed !important;
        inset:0 !important;
        width:100vw !important;
        height:100vh !important;
        z-index:2147483000 !important;
        background:rgba(20,16,18,.56);
        backdrop-filter:blur(7px);
        -webkit-backdrop-filter:blur(7px);
        align-items:center;
        justify-content:center;
        padding:16px;
        overflow:hidden !important;
        box-sizing:border-box;
    }

    .ku-ui-alert-overlay.open{
        display:flex;
        animation:kuUiAlertFadeIn .18s ease;
    }

    .ku-ui-alert-card{
        width:min(440px, calc(100vw - 32px));
        max-width:440px;
        max-height:calc(100vh - 32px);
        overflow:hidden;
        background:#fff;
        border-radius:22px;
        padding:24px 22px 20px;
        box-shadow:0 34px 90px rgba(0,0,0,.30);
        text-align:center;
        animation:kuUiAlertPop .18s ease;
        box-sizing:border-box;
    }

    .ku-ui-alert-icon{
        width:54px;
        height:54px;
        margin:0 auto 14px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        background:rgba(200,16,46,.08);
        color:#C8102E;
    }

    .ku-ui-alert-icon.success{
        background:rgba(30,122,70,.08);
        color:#16A34A;
    }

    .ku-ui-alert-icon.error{
        background:rgba(200,16,46,.08);
        color:#C8102E;
    }

    .ku-ui-alert-icon.warning{
        background:rgba(216,150,35,.10);
        color:#B86E00;
    }

    .ku-ui-alert-icon svg{
        width:27px;
        height:27px;
        display:block;
        stroke:currentColor;
    }

    .ku-ui-alert-card h3{
        margin:0 0 8px;
        font-family:'Space Grotesk',sans-serif;
        font-size:18px;
        font-weight:700;
        color:#252331;
    }

    .ku-ui-alert-message{
        min-height:42px;
        margin:0 auto 20px;
        max-width:380px;
        font-family:'Inter',sans-serif;
        font-size:12.5px;
        line-height:1.65;
        color:#7A808B;
        overflow-wrap:anywhere;
        word-break:break-word;
    }

    .ku-ui-alert-message strong{
        color:#2C3442;
        font-weight:800;
    }

    .ku-ui-alert-actions{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:10px;
    }

    .ku-ui-alert-actions.single{
        grid-template-columns:1fr;
    }

    .ku-ui-alert-btn{
        height:42px;
        border-radius:11px;
        font-family:'Inter',sans-serif;
        font-size:13px;
        font-weight:700;
        cursor:pointer;
        transition:.18s ease;
    }

    .ku-ui-alert-cancel{
        border:1px solid #E5E0E1;
        background:#fff;
        color:#6F7177;
    }

    .ku-ui-alert-cancel:hover{
        background:#F8F6F6;
    }

    .ku-ui-alert-confirm{
        border:1px solid #B40000;
        background:#B40000;
        color:#fff;
        box-shadow:0 10px 22px -14px rgba(180,0,0,.8);
    }

    .ku-ui-alert-confirm:hover{
        background:#980000;
        border-color:#980000;
    }

    .ku-ui-alert-confirm.success-btn{
        background:#650014;
        border-color:#650014;
    }

    .ku-ui-alert-confirm.success-btn:hover{
        background:#4F0010;
        border-color:#4F0010;
    }

    @keyframes kuUiAlertFadeIn{
        from{
            opacity:0;
        }
        to{
            opacity:1;
        }
    }

    @keyframes kuUiAlertPop{
        from{
            opacity:0;
            transform:translateY(10px) scale(.98);
        }
        to{
            opacity:1;
            transform:translateY(0) scale(1);
        }
    }

    @media(max-width:520px){
        .ku-ui-alert-card{
            max-width:calc(100vw - 32px);
            padding:20px 18px 18px;
        }
    }
</style>

<script>
(function () {

    'use strict';


    const overlay =
        document.getElementById('kuUiAlertOverlay');

    const icon =
        document.getElementById('kuUiAlertIcon');

    const title =
        document.getElementById('kuUiAlertTitle');

    const message =
        document.getElementById('kuUiAlertMessage');

    const actions =
        document.getElementById('kuUiAlertActions');

    const cancelBtn =
        document.getElementById('kuUiAlertCancel');

    const confirmBtn =
        document.getElementById('kuUiAlertConfirm');


    if (
        !overlay ||
        !icon ||
        !title ||
        !message ||
        !actions ||
        !cancelBtn ||
        !confirmBtn
    ) {
        return;
    }


    /*
     * Pindahkan overlay ke BODY supaya tidak terkurung
     * oleh container dashboard/content.
     * Dengan ini alert benar-benar menutup seluruh viewport,
     * termasuk sidebar, header, dan seluruh halaman.
     */
    if (overlay.parentElement !== document.body) {
        document.body.appendChild(overlay);
    }


    let resolver = null;


    /* =========================================================
       ICON
       ========================================================= */

    function kuIcon(type){

        if(type === 'success'){

            return `
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.4"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M5 12.5 9.2 17 19 7"/>
                </svg>
            `;

        }


        if(type === 'warning'){

            return `
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M12 3 2.8 20h18.4L12 3Z"/>
                    <path d="M12 9v5"/>
                    <path d="M12 17.2h.01"/>
                </svg>
            `;

        }


        return `
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 8v5"/>
                <path d="M12 16.5h.01"/>
            </svg>
        `;

    }


    /* =========================================================
       OPEN ALERT
       ========================================================= */

    function openAlert(options){

        return new Promise(function(resolve){

            resolver = resolve;


            const type =
                options.type || 'error';


            icon.className =
                'ku-ui-alert-icon ' + type;


            icon.innerHTML =
                kuIcon(type);


            title.textContent =
                options.title || 'Informasi';


            message.innerHTML =
                options.message || '';


            cancelBtn.textContent =
                options.cancelText || 'Batal';


            confirmBtn.textContent =
                options.confirmText || 'OK';


            const showCancel =
                options.showCancel === true;


            cancelBtn.style.display =
                showCancel ? '' : 'none';


            actions.classList.toggle(
                'single',
                !showCancel
            );


            confirmBtn.classList.toggle(
                'success-btn',
                type === 'success'
            );


            document.documentElement.classList.add(
                'ku-ui-lock'
            );


            document.body.classList.add(
                'ku-ui-lock'
            );


            overlay.classList.add(
                'open'
            );


            overlay.setAttribute(
                'aria-hidden',
                'false'
            );


            setTimeout(function(){

                confirmBtn.focus();

            },20);

        });

    }


    /* =========================================================
       CLOSE ALERT
       ========================================================= */

    function closeAlert(value){

        overlay.classList.remove(
            'open'
        );


        overlay.setAttribute(
            'aria-hidden',
            'true'
        );


        document.documentElement.classList.remove(
            'ku-ui-lock'
        );


        document.body.classList.remove(
            'ku-ui-lock'
        );


        if(resolver){

            const resolve =
                resolver;

            resolver =
                null;

            resolve(value);

        }

    }


    /* =========================================================
       BUTTON
       ========================================================= */

    confirmBtn.addEventListener(
        'click',
        function(){

            closeAlert(true);

        }
    );


    cancelBtn.addEventListener(
        'click',
        function(){

            closeAlert(false);

        }
    );


    overlay.addEventListener(
        'click',
        function(event){

            if(
                event.target === overlay
            ){

                closeAlert(false);

            }

        }
    );


    document.addEventListener(
        'keydown',
        function(event){

            if(
                event.key === 'Escape' &&
                overlay.classList.contains('open')
            ){

                closeAlert(false);

            }

        }
    );


    /* =========================================================
       DELETE CONFIRM
       ========================================================= */

    document
        .querySelectorAll('.ku-delete-form')
        .forEach(function(form){

            form.addEventListener(
                'submit',
                async function(event){

                    event.preventDefault();


                    const userName =
                        form
                            .closest('tr')
                            ?.querySelector('.ku-name')
                            ?.textContent
                            ?.trim() || 'user ini';


                    const confirmed =
                        await openAlert({

                            type:'warning',

                            title:'Hapus User?',

                            message:
                                `User <strong>${escapeHtml(userName)}</strong> akan dihapus dari sistem. Tindakan ini tidak dapat dibatalkan.`,

                            confirmText:'Hapus',

                            cancelText:'Batal',

                            showCancel:true

                        });


                    if(!confirmed){
                        return;
                    }


                    HTMLFormElement
                        .prototype
                        .submit
                        .call(form);

                }
            );

        });


    /* =========================================================
       ESCAPE HTML
       ========================================================= */

    function escapeHtml(value){

        return String(value ?? '')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#039;');

    }


    /* =========================================================
       SERVER MESSAGE
       ========================================================= */

    const successMessage =
        @json(session('success'));


    const errorMessage =
        @json(session('error'));


    const serverErrors =
        @json($errors->all());


    if(
        successMessage ||
        errorMessage ||
        (
            Array.isArray(serverErrors) &&
            serverErrors.length > 0
        )
    ){

        setTimeout(function(){

            if(successMessage){

                openAlert({

                    type:'success',

                    title:'Berhasil',

                    message:
                        escapeHtml(successMessage),

                    confirmText:'OK',

                    showCancel:false

                });

                return;
            }


            if(errorMessage){

                openAlert({

                    type:'error',

                    title:'Gagal',

                    message:
                        escapeHtml(errorMessage),

                    confirmText:'OK',

                    showCancel:false

                });

                return;
            }


            openAlert({

                type:'error',

                title:'Terjadi Kesalahan',

                message:
                    serverErrors
                        .map(function(error){
                            return `
                                <div>
                                    ${escapeHtml(error)}
                                </div>
                            `;
                        })
                        .join(''),

                confirmText:'OK',

                showCancel:false

            });

        },120);

    }

})();
</script>
